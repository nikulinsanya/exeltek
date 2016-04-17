<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Complete extends Controller
{
    public function action_index()
    {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            Messages::save('Please, select at least one job!');
        else {
            $jobs = Database_Mongo::collection('jobs');
            $submissions = Database_Mongo::collection('submissions')->distinct('job_key', array('job_key' => array('$in' => $ids), 'active' => 1));

            $ids = array_values(array_diff($ids, $submissions));


            $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => $ids)));
            print_r($result->explain());

            $count = 0;
            foreach ($result as $job) if (!in_array(Arr::get($job, 'status'), array(Enums::STATUS_ARCHIVE, Enums::STATUS_COMPLETE))) {
                $jobs->update(array('_id' => $job['_id']), array('$set' => array('last_update' => time(), 'status' => Enums::STATUS_COMPLETE)));
                $count++;
            }

            Messages::save($count . ' jobs were succesfully completed', 'success');
        }

        $this->redirect('/search');
    }

}