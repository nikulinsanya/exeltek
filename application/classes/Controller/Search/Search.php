<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Search extends Controller {

    public function action_index()
    {
        if (!isset($_GET['ticket']) && !isset($_GET['clear']) && Session::instance()->get('search-settings')) {
            $this->redirect(URL::query(Session::instance()->get('search-settings'), false));
        } elseif (isset($_GET['ticket']) || isset($_GET['clear'])) {
            if (isset($_GET['clear']))
                Session::instance()->delete('search-settings');
            else
                Session::instance()->set('search-settings', $_GET);
        }

        $actions = array(
            'contain',
            'does not contain',
            '=',
            '<',
            '<=',
            '>',
            '>=',
            '<>',
            'empty', 
            'not empty',
        );
        $actions_mongo = array(
            '='  => '$eq',
            '<'  => '$lt',
            '<=' => '$lte',
            '>'  => '$gt',
            '>=' => '$gte',
            '<>' => '$ne',
        );

        define('JOB_STATUS_COLUMN', 'data.44');

        $columns = Columns::get_visible();

        $reports = Columns::get_static();

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');

        $regions = DB::select('id', 'name')->from('regions');

        if ($regs)
            $regions->where('id', 'IN', $regs);

        $regions = $regions->execute()->as_array('id', 'name');

        $query = array();
        $list_query = array();

        if (Arr::get($_GET, 'submissions')) {
            $result = Database_Mongo::collection('submissions')->aggregate(array(
                array('$match' => array('active' => 1)),
                array('$group' => array('_id' => '$job_key')),
            ));
            $result = array_column($result['result'], '_id');
            $query['_id'] = array('$in' => $result);
        }

        if (Group::current('show_all_jobs')) {
            if (Arr::get($_GET, 'company')) {
                $companies = explode(',', $_GET['company']);
                if (count($companies) > 1) {
                    $list_query['companies'] = $query['companies'] = array('$in' => array_map("intval", $companies));
                } else
                    $list_query['companies'] = $query['companies'] = intval($_GET['company']);
            }
            if (Arr::get($_GET, 'ex')) {
                $companies = explode(',', $_GET['ex']);
                if (count($companies) > 1) {
                    $list_query['ex'] = $query['ex'] = array('$in' => array_map("intval", $companies));
                } else
                    $list_query['ex'] = $query['ex'] = intval($_GET['ex']);
            }
        } else {
            if (Arr::get($_GET, 'status', -1) == 0) {
                $list_query['ex'] = $query['ex'] = intval(User::current('company_id'));
            } else
                $list_query['companies'] = $query['companies'] = intval(User::current('company_id'));
        }

        if (Group::current('allow_assign')) foreach (Columns::$settings as $key => $value)
            if (isset($_GET['settings'][$key]))
                if ($_GET['settings'][$key])
                    $query[$key] = array('$exists' => 1);
                else
                    $query[$key] = array('$exists' => 0);

        if (Arr::get($_GET, 'ticket')) {
            $tickets = explode(',', $_GET['ticket']);
            $q = array();
            foreach ($tickets as $ticket) {
                $ticket = preg_replace('/[^a-z0-9]/i', '', strval($ticket));
                if ($ticket) $q[] = new MongoRegex('/.*' . $ticket . '.*/i');
            }
            if (count($q) > 1)
                $query['_id'] = array('$in' => $q);
            elseif ($q)
                $query['_id'] = $q[0];
        }

        if (Arr::get($_GET, 'start')) {
            $query['last_update']['$gt'] = Columns::parse($_GET['start'], 'date');
        }

        if (Arr::get($_GET, 'end')) {
            $query['last_update']['$lt'] = Columns::parse($_GET['end'], 'date');
        }

        if (Arr::get($_GET, 'submit-start')) {
            $query['last_submit']['$gt'] = Columns::parse($_GET['submit-start'], 'date');
        }

        if (Arr::get($_GET, 'submit-end')) {
            $query['last_submit']['$lt'] = Columns::parse($_GET['submit-end'], 'date');
        }
        $status = Arr::get($_GET, 'status', Group::current('allow_assign') ? -1 : Enums::STATUS_ALLOC);
        $_GET['status'] = intval($status);
        if ($status != -1 && (Group::current('show_all_jobs') || $status)) {
            $query['status'] = intval($status) ? : array('$exists' => 0);
        }

        if (!isset($_GET['region'])) $_GET['region'] = User::current('default_region');

        if (Arr::get($_GET, 'region') && (!$regs || isset($regs[$_GET['region']]))) {
            $list_query['region'] = $query['region'] = strval($_GET['region']);
        } elseif ($regs) {
            $list_query['region'] = $query['region'] = array('$in' => array_values($regs));
        }

        if (Arr::get($_GET, 'type')) {
            $query['assigned.' . $_GET['type']] = array('$exists' => 1);
        }

        foreach (Arr::get($_GET, 'columns', array()) as $id => $column) if ($column) {
            $op = Arr::get($actions, Arr::path($_GET, 'actions.' . $id), 0);
            $value = Arr::path($_GET, 'values.' . $id, '');

            $value = Columns::parse($value, Columns::get_type($column));

            if (Columns::get_type($column) == 'date')
                if ($op === 'contain')
                    $op = '=';
                elseif ($op === 'does not contain')
                    $op = '<>';

            if ($op === 'contain') {
                $op = '$regex';
                $value = new MongoRegex('/.*' . preg_replace('/[^a-z0-9,.+:;!? -]/i', '', $value) . '.*/i');
            } elseif ($op === 'does not contain') {
                $op = '$not';
                $value = new MongoRegex('/.*' . preg_replace('/[^a-z0-9,.+:;!? -]/i', '', $value) . '.*/i');
            } elseif ($op === 'empty') {
                $op = '$exists';
                $value = 0;
            } elseif ($op === 'not empty') {
                $op = '$exists';
                $value = 1;
            } else {
                $op = Arr::get($actions_mongo, $op, '$eq');
            }

            if (isset($query['data.' . $column])) {
                if (isset($query['data.' . $column][$op])) {
                    if ($op == '$regex') {
                        $query['data.' . $column][$op] = $value;
                    } else {
                        if (is_array($query['data.' . $column][$op]))
                            $query['data.' . $column][$op][] = $value;
                        else
                            $query['data.' . $column][$op] = array($query['data.' . $column][$op], $value);
                    }
                } elseif (isset($query['data.' . $column]['$or'][$op])) {

                }
                else
                    $query['data.' . $column][$op] = $value;
            } else
                $query['data.' . $column] = array($op => $value);
        }

        foreach ($query as $key => $ops) if (is_array($ops))
            foreach ($ops as $op => $value)
                if ($op == '$eq' && is_array($value)) {
                    $query[$key]['$in'] = $value;
                    unset($query[$key]['$eq']);
                }

        $jobs = Database_Mongo::collection('jobs');

        $list_values = array();
        foreach (Columns::get_search() as $key => $value) if ($value == 2) {
            $list_values[$key] = $jobs->distinct('data.' . $key, $query ? : NULL);
            if ($list_values[$key]) sort($list_values[$key]);
        }

        Pager::$count = $jobs->count($query);

        $result = $jobs->find($query);

        $sort = Arr::get($_GET, 'sort');
        if (is_array($sort)) {
            $save = implode('|', $sort);
            DB::update('users')->set(array('default_sort' => $save))->where('id', '=', User::current('id'))->execute();
        } else {
            $sort = explode('|', User::current('default_sort') ? : '-update');
            $_GET['sort'] = $sort;
        }

        $sorting = array();
        foreach ($sort as $order) {
            $dir = substr($order, 0, 1) == '-' ? -1 : 1;
            $order = substr($order, 1);
            switch ($order) {
                case 'id':
                    $order = '_id';
                    break;
                case 'update':
                    $order = 'last_update';
                    break;
                case 'submit':
                    $order = 'last_submit';
                    break;
                case 'status':
                    $order = 'status';
                    break;
                /*case 'data-8':
                    $sorting['data.119'] = $dir;
                    $sorting['data.118'] = $dir;
                    $order = false;
                break;*/
                default:
                    if (substr($order, 0, 5) == 'data-')
                        $order = 'data.' . intval(substr($order, 5));
                    else
                        $order = false;
            }
            if ($order) $sorting[$order] = $dir;
        }

        $result->sort($sorting)->limit(Pager::limit())->skip(Pager::offset());

        $tickets = array();
        while ($row = $result->next())
            $tickets[] = $row;


        $ids = array_column($tickets, '_id');

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

        $view = View::factory('Jobs/Search')
            ->bind('reports', $reports)
            ->bind('regions', $regions)
            ->bind('columns', $columns)
            ->bind('actions', $actions)
            ->bind('tickets', $tickets)
            ->bind('submissions', $submissions)
            ->bind('attachments', $attachments)
            ->bind('list_values', $list_values)
            ->bind('types', $types)
            ->bind('sort', $sort)
            ->bind('companies', $companies);
        $this->response->body($view);
    }
}