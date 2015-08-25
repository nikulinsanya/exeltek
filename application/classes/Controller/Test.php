<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Controller {

    public function before() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        header('Content-type: text/plain');
        $jobs = Database_Mongo::collection('jobs')->find();
        foreach ($jobs as $job) {
            $address = Arr::path($job, 'data.8');
            $address = MapQuest::parse($address);
            Database_Mongo::collection('jobs')->update(array('_id' => $job['_id']), array('$set' => array('address' => $address)));
        }

        die('Done');

        header('Content-type: application/json');
        $result = Database_Mongo::collection('jobs')->find(array(), array('data.8' => 1));
        $jobs = array();
        foreach ($result as $job)
            $jobs[$job['_id']] = $job['data'][8];
        die(json_encode($jobs));

        $start = microtime(true);

        $total = 0;
        $jobs = Database_Mongo::collection('jobs')->find();
        foreach ($jobs as $job) {
            $new = array();
            if (isset($job['assigned']))
                foreach ($job['assigned'] as $key => $company) if ($company !== intval($company))
                    $new['$set']['assigned.' . $key] = intval($company);

            if (isset($job['ex']))
                foreach ($job['ex'] as $key => $company) if ($company !== intval($company))
                    $new['$set']['ex.' . $key] = intval($company);

            if ($new) {
                Database_Mongo::collection('jobs')->update(array('_id' => $job['_id']), $new);
                $total++;
            }
        }

        echo $total;
        die();

        header("Content-type: text/plain");
        $jobs = Database_Mongo::collection('jobs')->find(array(), array('data.245' => 1, 'data.43' => 1, 'data.190' => 1, 'data.191' => 1, 'data.192' => 1, 'created' => 1));

        $archive = Database_Mongo::collection('archive');

        $result = DB::select('id', 'company_id')->from('users')->where('company_id', '>', 0)->execute()->as_array('id', 'company_id');
        $companies = array();
        foreach ($result as $key => $value)
            $companies[intval($key)] = $value;

        $ids = array();
        foreach ($jobs as $job) if (Arr::path($job, 'data.245') == 'Yes') {
            $result = $archive->find(array(
                'job_key' => $job['_id'],
                'fields' => array('$in' => array(245, 43, 190, 191, 192)),
                //'user_id' => array('$in' => array_keys($companies))
            ))->sort(array('update_time' => 1));
            $indexes = array();

            if (Arr::get($job['data'], 43)) {
                $indexes['t'] = 43;
                $types['t'][0] = $job['created'];
            }

            if (Arr::get($job['data'], 190)) {
                $indexes['a'] = 190;
                $types['a'][0] = $job['created'];
            }

            if (Arr::get($job['data'], 191)) {
                $indexes['b'] = 191;
                $types['b'][0] = $job['created'];
            }

            if (Arr::get($job['data'], 192)) {
                $indexes['c'] = 192;
                $types['c'][0] = $job['created'];
            }

            $finished = 0;
            $types = array();
            foreach ($result as $item) if (isset($companies[$item['user_id']])) {
                if (isset($item['data']['245']))
                    $finished = $item['update_time'];
                foreach ($indexes as $key => $value)
                    if (isset($item['data'][$value]))
                        $types[$key][$companies[$item['user_id']]] = $item['update_time'];

            }
            if ($finished && $types) {
                $types['f'] = $finished;
                print_r($types);
                die();
            }
        }

        die('Done in ' . round(microtime(true) - $start, 3) . 's.');
    }

}
