<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Controller {

    public function before() {
        parent::before();

        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_indexes() {
        $columns = DB::select('column_id')->distinct(true)->from('group_columns')->where('search', '=', 2)->execute()->as_array(NULL, 'column_id');

        foreach ($columns as $column) {
            $index = array();
            $type = Columns::get_type($column);
            $name = 'data.' . $column;
            switch ($type) {
                case 'text':
                    //$index[$name] = 'text';
                    break;
                default:
                    $index[$name] = true;
            }

            if ($index)
                Database_Mongo::collection('jobs')->ensureIndex($index, array('sparse' => true));
        }

        die('Done');
    }

    public function action_dates() {
        $tested = array();
        $built = array();

        $jobs = Database_Mongo::collection('archive')->find(array('data.44.new_value' => new MongoRegex('/.*built.*/i')), array('job_key' => 1, 'update_time' => 1));
        foreach ($jobs as $job)
            if (!isset($built[$job['job_key']]) || $built[$job['job_key']] > $job['update_time']) $built[$job['job_key']] = $job['update_time'];

        $jobs = Database_Mongo::collection('archive')->find(array('data.44.new_value' => new MongoRegex('/.*tested.*/i')), array('job_key' => 1, 'update_time' => 1));
        foreach ($jobs as $job)
            if (!isset($tested[$job['job_key']]) || $tested[$job['job_key']] > $job['update_time']) $tested[$job['job_key']] = $job['update_time'];

        $jobs = Database_Mongo::collection('jobs')->find(array('data.44' => new MongoRegex('/.*built.*/i')), array('created' => 1));
        foreach ($jobs as $job)
            if (!isset($built[$job['_id']]))
                $built[$job['_id']] = $job['created'];

        $jobs = Database_Mongo::collection('jobs')->find(array('data.44' => new MongoRegex('/.*tested.*/i')), array('created' => 1));
        foreach ($jobs as $job) {
            if (!isset($built[$job['_id']]))
                $built[$job['_id']] = $job['created'];
            if (!isset($tested[$job['_id']]))
                $tested[$job['_id']] = $job['created'];
        }

        $update = array();

        foreach ($built as $key => $value)
            $update[$key]['$set']['data.264'] = $value;

        foreach ($tested as $key => $value)
            $update[$key]['$set']['data.265'] = $value;

        foreach ($update as $key => $value)
            Database_Mongo::collection('jobs')->update(array('_id' => $key), $value);
        die('Done');
    }

    public function action_discrepancies() {
        $list = Database_Mongo::collection('jobs')->find(array('discrepancies' => array('$nin' => array(NULL, '', 0))));
        $ids = array();
        foreach ($list as $job) {
            $discr = Database_Mongo::collection('discrepancies')->find(array('job_key' => $job['_id']))->sort(array('update_time' => -1))->getNext();
            $fl = true;
            foreach ($discr['data'] as $key => $value) {
                if ($key == 44) {
                    $status = preg_replace('/[^a-z]/i', '', strtolower($value['old_value']));
                    $status2 = preg_replace('/[^a-z]/i', '', strtolower($value['new_value']));
                    if ((($status == 'tested' && $status2 != 'tested') || ($status == 'built' && ($status2 != 'built' && $status2 != 'tested')) ||
                            ($status != $status2 && in_array($status2, array('deferred', 'dirty', 'heldnbn'), true)))
                    ) {
                        $fl = false;
                        continue;
                    }
                } elseif ($value['old_value'] != Arr::get($job['data'], $key, '')) {
                    $fl = false;
                    continue;
                }
            }
            if ($fl) $ids[] = $job['_id'];
        }
        Database_Mongo::collection('jobs')->update(array('_id' => array('$in' => $ids)), array('$unset' => array('discrepancies' => 1)), array('multiple' => 1));
        die('Done. Total ' . count($ids) . ' job(s)');

    }

    public function action_index() {
        header('Content-type: text/plain');

        /*$list = Database_Mongo::collection('archive')->distinct('job_key', array('update_time' => array('$gt' => strtotime('2015-09-01')), 'data.44.new_value' => array('$in' => array('TESTED', '[3] Tested'))));
        $list = Database_Mongo::collection('jobs')->distinct('_id', array('_id' => array('$in' => $list), 'data.12' => '4AAR'));
        echo implode("\n", $list);
        die();*/

        set_time_limit(0);
        $jobs = Database_Mongo::collection('jobs')->find();
        foreach ($jobs as $job) {
            Utils::calculate_financial($job);
        }
        die('Done');

        $list = Database_Mongo::collection('jobs')->find(array('discrepancies' => array('$nin' => array(NULL, '', 0))));
        $ids = array();
        foreach ($list as $job) {
            $discr = Database_Mongo::collection('discrepancies')->find(array('job_key' => $job['_id']))->sort(array('update_time' => -1))->getNext();
            $fl = true;
            foreach ($discr['data'] as $key => $value)
                if ($value['old_value'] != Arr::get($job['data'], $key, '')) {
                    $fl = false;
                    continue;
                }
            if ($fl) $ids[] = $job['_id'];
        }
        Database_Mongo::collection('jobs')->update(array('_id' => array('$in' => $ids)), array('$unset' => array('discrepancies' => 1)), array('multiple' => 1));
        die('Done. Total ' . count($ids) . ' job(s)');

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
