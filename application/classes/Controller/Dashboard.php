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
        $type = Arr::get($_GET, 'type');
        $range = array();
        if (Arr::get($_GET, 'start')) $range['$gte'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $range['$lte'] = strtotime($_GET['end']) + 86399;

        switch ($type) {
            case 'companies':
                $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
                $result = Database_Mongo::collection('jobs')->find(array(), array('data.44' => 1, 'companies' => 1, 'ex' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    foreach (Arr::get($job, 'companies', array()) as $company)
                        $list[Arr::get($companies, $company, 'Unknown')][$key] = Arr::path($list, array($company, $key)) + 1;

                    foreach (Arr::get($job, 'ex', array()) as $company)
                        $list[Arr::get($companies, $company, 'Unknown')][$key] = Arr::path($list, array($company, $key)) + 1;
                }
                break;
            case 'fsa':
                $result = Database_Mongo::collection('jobs')->find(array(), array('data.44' => 1, 'data.12' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.12', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            case 'fsam':
                $fsa = strval(Arr::get($_GET, 'fsa', ''));
                $result = Database_Mongo::collection('jobs')->find(array('data.12' => $fsa), array('data.44' => 1, 'data.13' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.13', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            default:
                if ($range) {
                    $result = Database_Mongo::collection('archive')->aggregate(array(
                        array('$match' => array(
                            'update_time' => $range,
                            'fields' => 44,
                        )),
                        array('$group' => array(
                            '_id' => '$job_key',
                            'status' => array('$last' => array('$toLower' => '$data.44.new_value')),
                        )),
                    ));
                    $jobs = array();
                    foreach ($result['result'] as $item)
                        $jobs[$item['_id']] = $item['status'];

                    $result = Database_Mongo::collection('jobs')->find(array('created' => $range), array('data.44' => 1));
                    foreach ($result as $job)
                        if (!isset($jobs[$job['_id']]))
                            $jobs[$job['_id']] = strtolower(Arr::path($job, 'data.44'));

                    $list = array();
                    foreach ($jobs as $item) {
                        $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', $item))) ? : 'Unknown';
                        $list[$key] = Arr::get($list, $key, 0) + 1;
                    }
                } else {
                    $result = Database_Mongo::collection('jobs')->aggregate(array(
                        '$group' => array(
                            '_id' => array('$toLower' => '$data.44'),
                            'count' => array('$sum' => 1),
                        )
                    ));
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
