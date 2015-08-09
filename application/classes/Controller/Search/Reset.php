<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Reset extends Controller
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
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            foreach ($result as $job) if (Arr::get($job, 'status') == Enums::STATUS_ARCHIVE || Arr::get($job, 'status') == Enums::STATUS_COMPLETE || Arr::get($job, 'status') == Enums::STATUS_PENDING) {
                if ($job['status'] == Enums::STATUS_PENDING) {
                    $submissions = Database_Mongo::collection('submissions')->find(array('job_key' => $job['_id'], 'active' => 1))->count();

                    if ($submissions) continue;
                }

                $update = array('$set' => array('last_update' => time()));

                if (Arr::get($job, 'assigned'))
                    $update['$set']['status'] = Enums::STATUS_ALLOC;
                else
                    $update['$unset']['status'] = 1;

                $jobs->update(array('_id' => $job['_id']), $update);
                $count++;
            }

            Messages::save($count . ' jobs were succesfully reset to initial state', 'success');
        }

        $this->redirect('/search');
    }
}