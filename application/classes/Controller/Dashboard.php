<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller {

    private function group_jobs($key, $query) {
        $result = Database_Mongo::collection('jobs')->find($query, array($key => 1, 'data.44' => 1));

        $query = array(
            'data.44' => array('$exists' => 1),
        );

        if (Arr::get($_GET, 'start')) $query['update_time']['$gt'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $query['update_time']['$lt'] = strtotime($_GET['end']) + 86399;

        $total = array();
        $jobs = array();
        $statuses = array();
        foreach ($result as $job) {
            $fsam = Arr::path($job, $key);
            $jobs[$job['_id']] = $fsam;
            $status = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44')));
            if (!isset($query['update_time'])) $statuses[$job['_id']] = $status;
            if (!in_array($status, array('dirty', 'deferred', 'heldnbn')))
                $total[$fsam] = Arr::get($total, $fsam, 0) + 1;
        }

        $query['job_key'] = array('$in' => array_keys($jobs));

        $result = Database_Mongo::collection('archive')->find($query, array('_id' => 0, 'job_key' => 1, 'data.44' => 1, 'update_time' => 1))->sort(array('update_time' => 1));

        foreach ($result as $job)
            $statuses[$job['job_key']] = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44.new_value')));

        $built = array();
        $tested = array();
        foreach ($jobs as $id => $fsam) {
            switch (Arr::get($statuses, $id)) {
                case 'built':
                    $built[$fsam] = Arr::get($built, $fsam) + 1;
                    break;
                case 'tested':
                    $tested[$fsam] = Arr::get($tested, $fsam) + 1;
                    break;
            }
        }

        $fsam = array();
        foreach ($total as $key => $value)
            $fsam[$key ? : 'Unknown'] = array(
                'total' => $value,
                'built' => Arr::get($built, $key, 0),
                'tested' => Arr::get($tested, $key, 0),
            );

        ksort($fsam);

        return $fsam;
    }

    public function action_fda() {
        $query = array();

        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        if (!Group::current('show_all_jobs')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && isset($companies[$_GET['company']]))
                $query['$or'] = array(
                    array('companies' => intval($_GET['company'])),
                    array('ex' => intval($_GET['company'])),
                );

        }

        $fsas = Database_Mongo::collection('jobs')->distinct('data.12', $query ? : NULL);
        $fsas = array_combine($fsas, $fsas);

        if (Arr::get($_GET, 'fsa') && (isset($fsas[$_GET['fsa']]) || $_GET['fsa'] == 'Unknown'))
            $query['data.12'] = $_GET['fsa'] == 'Unknown' ? array('$exists' => false) : strval($_GET['fsa']);

        $fsam = Database_Mongo::collection('jobs')->distinct('data.13', $query ? : NULL);
        $fsam = array_combine($fsam, $fsam);

        if (Arr::get($_GET, 'fsam') && isset($fsam[$_GET['fsam']]))
            $query['data.13'] = strval($_GET['fsam']);

        $fdas = $this->group_jobs('data.14', $query);

        $view = View::factory('Dashboard/Fda')
            ->bind('fsam', $fsam)
            ->bind('fsas', $fsas)
            ->bind('fdas', $fdas)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_fsam() {
        $query = array();

        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && isset($companies[$_GET['company']]))
                $query['$or'] = array(
                    array('companies' => intval($_GET['company'])),
                    array('ex' => intval($_GET['company'])),
                );
        }

        $fsas = Database_Mongo::collection('jobs')->distinct('data.12', $query ? : NULL);
        $fsas = array_combine($fsas, $fsas);

        if (Arr::get($_GET, 'fsa') && (isset($fsas[$_GET['fsa']]) || $_GET['fsa'] == 'Unknown'))
            $query['data.12'] = $_GET['fsa'] == 'Unknown' ? array('$exists' => false) : strval($_GET['fsa']);

        $fsam = $this->group_jobs('data.13', $query);

        $view = View::factory('Dashboard/Fsam')
            ->bind('fsam', $fsam)
            ->bind('fsas', $fsas)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_fsa() {
        $query = array();

        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && isset($companies[$_GET['company']]))
                $query['$or'] = array(
                    array('companies' => intval($_GET['company'])),
                    array('ex' => intval($_GET['company'])),
                );
            if (Arr::get($_GET, 'region') && isset($regions[$_GET['region']]))
                $query['region'] = strval($_GET['region']);

        }

        $fsa = $this->group_jobs('data.12', $query);

        $view = View::factory('Dashboard/Fsa')
            ->bind('fsa', $fsa)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_reports() {
        $query = array();

        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && isset($companies[$_GET['company']]))
                $query['$or'] = array(
                    array('companies' => intval($_GET['company'])),
                    array('ex' => intval($_GET['company'])),
                );
            if (Arr::get($_GET, 'region') && isset($regions[$_GET['region']]))
                $query['region'] = strval($_GET['region']);

        }

        $fsa = $this->group_jobs('data.12', $query);

        $view = View::factory('Dashboard/Reports')
            ->bind('fsa', $fsa)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_api() {
        header('Content-type: application/json');

        $type = Arr::get($_GET, 'type');
        $range = array();
        if (Arr::get($_GET, 'start')) $range['$gte'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $range['$lte'] = strtotime($_GET['end']) + 86399;

        $separate = strtolower(Arr::get($_GET, 'sep'));

        $filter = array();

        if (!Group::current('show_all_jobs')) {
            $filter = array('$or' => array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            ));
        }

        switch ($type) {
            case 'companies':
                $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'companies' => 1, 'ex' => 1));
                $list = array();
                $total = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';

                    $total[$key] = Arr::get($total, $key, 0) + 1;

                    foreach (Arr::get($job, 'companies', array()) as $company) if (Group::current('show_all_jobs') || $company == User::current('company_id'))
                        $list[$company][$key] = Arr::path($list, array($company, $key)) + 1;

                    foreach (Arr::get($job, 'ex', array()) as $company) if (Group::current('show_all_jobs') || $company == User::current('company_id'))
                        $list[$company][$key] = Arr::path($list, array($company, $key)) + 1;
                }
                $result = array();
                foreach ($list as $key => $values)
                    $result[Arr::get($companies, $key, 'Unknown')] = $values;
                $list = array(
                    'total' => $total,
                    'companies' => $result,
                );
                break;
            case 'fsa':
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'data.12' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.12', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            case 'fsam':
                $fsa = strval(Arr::get($_GET, 'fsa', ''));
                $filter['data.12'] = $fsa;
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'data.13' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.13', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            default:
                if ($range || $separate == 'd' || $separate == 'w' || $separate == 'm') {
                    $jobs = array();
                    $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'created' => 1));
                    $ids = array();
                    $start = Arr::get($range, '$gte');
                    $end = Arr::get($range, '$lte');

                    foreach ($result as $job) {
                        if ($filter) $ids[] = $job['_id'];
                        if ((!$start || $start <= $job['created']) && (!$end || $end >= $job['created']))
                            $jobs[$job['_id']] = array(
                                's' => strtolower(Arr::path($job, 'data.44')),
                                't' => $job['created'],
                            );
                    }

                    $filter = array('$match' => array(
                        'fields' => 44,
                    ));
                    if ($range) $filter['$match']['update_time'] = $range;

                    if ($ids) $filter['$match']['job_key'] = array('$in' => $ids);

                    $result = Database_Mongo::collection('archive')->aggregate(array(
                        $filter,
                        array('$sort' => array('update_time' => 1)),
                        array('$group' => array(
                            '_id' => '$job_key',
                            'date' => array('$last' => '$update_time'),
                            'status' => array('$last' => array('$toLower' => '$data.44.new_value')),
                        )),
                    ));

                    foreach ($result['result'] as $item)
                        $jobs[$item['_id']] = array(
                            's' => $item['status'],
                            't' => $item['date'],
                        );

                    $list = array();
                    $date = false;
                    foreach ($jobs as $item) {
                        $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', $item['s']))) ? : 'Unknown';

                        switch ($separate) {
                            case 'm':
                                $date = date('Y-m', $item['t']);
                                break;
                            case 'w':
                                $date = date('Y-W', $item['t']);
                                break;
                            case 'd':
                                $date = date('Y-m-d', $item['t']);
                                break;
                            default:
                                $date = false;
                                break;
                        }
                        if ($date)
                            $list[$date][$key] = Arr::path($list, array($date, $key), 0) + 1;
                        else
                            $list[$key] = Arr::get($list, $key, 0) + 1;
                    }
                    if ($date) ksort($list);
                } else {
                    $query = array();
                    if ($filter) $query[] = array('$match' => $filter);
                    $query[] = array('$group' => array(
                            '_id' => array('$toLower' => '$data.44'),
                            'count' => array('$sum' => 1),
                        ));
                    $result = Database_Mongo::collection('jobs')->aggregate($query);
                    $list = array();
                    foreach ($result['result'] as $item) {
                        $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', $item['_id']))) ? : 'Unknown';
                        $count = $item['count'];
                        $list[$key] = Arr::get($list, $key, 0) + $count;
                    }
                }
                break;
        }
        die(json_encode($list));
    }
}
