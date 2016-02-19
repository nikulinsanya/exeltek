<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Tools extends Controller
{

    public function before()
    {
        ini_set('memory_limit', -1);
    }

    public function action_discrepancies() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');

        $list = Database_Mongo::collection('jobs')->find(array('discrepancies' => array('$nin' => array(NULL, '', 0))));
        $ids = array();
        foreach ($list as $job) {
            $discr = Database_Mongo::collection('discrepancies')->find(array('job_key' => $job['_id']))->sort(array('update_time' => -1))->getNext();
            $fl = true;
            foreach ($discr['data'] as $key => $value) {
                if ($key == 44) {
                    $status = preg_replace('/[^a-z]/i', '', strtolower($value['old_value']));
                    $status2 = preg_replace('/[^a-z]/i', '', strtolower($value['new_value']));
                    $status0 = preg_replace('/[^a-z]/i', '', strtolower(Arr::get($job['data'], $key, '')));

                    if ($status == $status0) continue;

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

        header('Content-type: application/json');
        die(json_encode(array('success' => true)));
    }

    public function action_underbore() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');

        $list = Database_Mongo::collection('submissions')->find(array('key' => 'data.205', 'active' => -1));
        $jobs = array();
        foreach ($list as $submission) {
            if (!isset($jobs[$submission['job_key']]) || $jobs[$submission['job_key']]['time'] <= $submission['process_time'])
                $jobs[$submission['job_key']] = array(
                    'time' => $submission['process_time'],
                    'value' => $submission['value'],
                );
        }
        foreach ($jobs as $key => $job) {
            Database_Mongo::collection('jobs')->update(array('_id' => $key), array('$set' => array('data.268' => $job['value'])));
        }

        header('Content-type: application/json');
        die(json_encode(array('success' => true)));
    }

    public function action_financial() {
        if (!Group::current('is_admin') && !(Group::current('allow_finance') && Group::current('show_all_jobs')))
            throw new HTTP_Exception_403('Forbidden');

        $jobs = Database_Mongo::collection('jobs')->find();
        foreach ($jobs as $job) {
            set_time_limit(30);
            Utils::calculate_financial($job);
        }

        header('Content-type: application/json');
        die(json_encode(array('success' => true)));
    }
}