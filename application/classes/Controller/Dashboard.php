<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller {

    private function group_jobs($key, $list) {
        $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => $list)), array($key => 1, 'data.44' => 1));

        $total = array();
        $jobs = array();
        $statuses = array();
        foreach ($result as $job) {
            $fsam = Arr::path($job, $key);
            $jobs[$job['_id']] = $fsam;
            $status = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44')));
            $statuses[$job['_id']] = $status;
            if (!in_array($status, array('dirty', 'deferred', 'heldnbn')))
                $total[$fsam] = Arr::get($total, $fsam, 0) + 1;
        }

        $query = array(
            'job_key' => array('$in' => $list),
            'data.44' => array('$exists' => 1),
        );

        if (Arr::get($_GET, 'start')) $query['update_time']['$gt'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $query['update_time']['$lt'] = strtotime($_GET['end']) + 86399;

        $result = Database_Mongo::collection('archive')->find($query, array('_id' => 0, 'job_key' => 1, 'data.44' => 1, 'update_time' => 1))->sort(array('update_time' => 1));

        foreach ($result as $job)
            $statuses[$job['job_key']] = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44.new_value')));

        $built = array();
        $tested = array();
        foreach ($list as $id) {
            $fsam = $jobs[$id];
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

        $list = Database_Mongo::collection('jobs')->distinct('_id', $query ? : NULL);

        $fdas = $this->group_jobs('data.14', $list);

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

        $list = Database_Mongo::collection('jobs')->distinct('_id', $query ? : NULL);

        $fsam = $this->group_jobs('data.13', $list);

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

        $list = Database_Mongo::collection('jobs')->distinct('_id', $query ? : NULL);

        $fsa = $this->group_jobs('data.12', $list);

        $view = View::factory('Dashboard/Fsa')
            ->bind('fsa', $fsa)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }
}
