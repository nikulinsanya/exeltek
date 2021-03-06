<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Map extends Controller {

    public function action_index() {
        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            throw new HTTP_Exception_404('Not found');

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');

        $query = array();

        if ($regs)
            $query['region'] = array('$in' => array_values($regs));

        if (!Group::current('show_all_jobs')) {
            $query['$or'] = array(
                array('ex' => intval(User::current('company_id'))),
                array('companies' => intval(User::current('company_id'))),
            );
        }

        $query['_id'] = array('$in' => $ids);

        $jobs = Database_Mongo::collection('jobs')->find($query);

        $static = array_flip(explode(',', Group::current('columns')));

        $header = array(
            'Ticket ID',
        );

        $types = DB::select('id', 'name')->from('job_types')->execute()->as_array('id', 'name');
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        if ($ids)
            $attachments = DB::select('job_id', DB::expr('COUNT(*) as cnt'))
                ->from('attachments')
                ->where('job_id', 'IN', $ids)
                ->and_where('uploaded', '>', 0)
                ->group_by('job_id')
                ->execute()->as_array('job_id', 'cnt');
        else $attachments = array();

        if (Group::current('allow_assign')) {
            $result = Database_Mongo::collection('submissions')->aggregate(
                array(
                    array('$match' => array('job_key' => array('$in' => $ids), 'active' => 1)),
                    array('$group' => array('_id' => '$job_key', 'count' => array('$sum' => 1))),
                ));

            $submissions = array();
            foreach (Arr::get($result, 'result', array()) as $value)
                $submissions[$value['_id']] = $value['count'];
        }

        if (isset($static['last_update'])) $header[] = 'Last update';
        if (isset($static['last_submit'])) $header[] = 'Last submit';
        if (isset($static['status']) && Group::current('show_all_jobs')) $header[] = 'Job status';
        if (isset($static['types'])) $header[] = 'Assigned works';
        if (isset($static['companies'])) $header[] = 'Assigned companies';
        if (isset($static['pending'])) $header[] = 'Pending submissions';
        if (isset($static['attachments'])) $header[] = 'Attachments';

        foreach (Columns::get_search() as $id => $type)
            $header[] = Columns::get_name($id);

        $list = array();

        $request = array();
        foreach ($jobs as $job) if (!isset($job['lat'])) {
            $request[$job['_id']] = Arr::path($job, 'data.8');
        }

        if ($request) {
            $coords = MapQuest::locate($request);
            foreach ($coords as $key => $value)
                Database_Mongo::collection('jobs')->update(array('_id' => $key), array('$set' => array('lng' => $value['lng'], 'lat' => $value['lat'])));
        } else $coords = array();

        foreach ($jobs as $ticket) {
            if (isset($coords[$ticket['_id']])) {
                $ticket['lng'] = $coords[$ticket['_id']]['lng'];
                $ticket['lat'] = $coords[$ticket['_id']]['lat'];
            }

            if (!isset($ticket['lat'])) continue;

            $row = array($ticket['_id']);

            if (isset($static['last_update'])) $row[] = date('d-m-Y H:i', Arr::get($ticket, 'last_update', $ticket['created']));
            if (isset($static['last_submit'])) $row[] = Arr::get($ticket, 'last_submit') ? date('d-m-Y H:i', $ticket['last_submit']) : '';
            if (isset($static['status']) && Group::current('show_all_jobs')) $row[] = Arr::get(Enums::$statuses, Arr::get($ticket, 'status', 0), 'Unknown');
            if (isset($static['types']))
                if (Group::current('allow_assign'))
                    $row[] = implode(', ', array_intersect_key($types, Arr::get($ticket, 'assigned', array())));
                else
                    $row[] = implode(', ', array_intersect_key($types, array_filter(Arr::get($ticket, 'assigned', array()), function ($x) {
                        return $x == User::current('company_id');
                    })));


            if (isset($static['companies'])) $row[] = implode(', ', array_intersect_key($companies, array_flip(Arr::get($ticket, 'assigned', array()))));
            if (isset($static['pending'])) $row[] = Arr::get($submissions, $ticket['_id']);
            if (isset($static['attachments'])) $row[] = Arr::get($attachments, $ticket['_id']);

            foreach (Columns::get_search() as $id => $type)
                $row[] = Arr::path($ticket, array('data', $id)) ? Columns::output($ticket['data'][$id], Columns::get_type($id), true) : '';

            $row = array_combine($header, $row);
            $row['lat'] = $ticket['lat'];
            $row['lng'] = $ticket['lng'];
            $list[] = $row;
        }

        if (!$list) throw new HTTP_Exception_404('Not found');

        DB::delete('maps')->where('expire', '<', time())->execute();

        $ids = DB::select('map_id')->distinct(true)->from('maps')->execute()->as_array('map_id', 'map_id');

        do {
            $id = Text::random('alnum', 32);
        } while (isset($ids[$id]));

        $expire = time() + 60 * 60 * 24 * 7;

        $query = DB::insert('maps', array('map_id', 'job_key', 'lng', 'lat', 'info', 'expire'));

        foreach ($list as $values) {
            $lat = $values['lat'];
            $lng = $values['lng'];
            unset($values['lng']);
            unset($values['lat']);
            $query->values(array($id, $values['Ticket ID'], $lng, $lat, json_encode($values), $expire));
        }

        $query->execute();

        $this->redirect('/map.html#' . $id);
    }

}