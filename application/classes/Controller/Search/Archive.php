<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Archive extends Controller {

    public function action_index() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            Messages::save('Please, select at least one job!');
        else {
            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            foreach ($result as $job) if (Arr::get($job, 'status') != Enums::STATUS_PENDING && Arr::get($job, 'status') != Enums::STATUS_ARCHIVE) {
                $jobs->update(array('_id' => $job['_id']), array('$set' => array('last_update' => time(), 'status' => Enums::STATUS_ARCHIVE)));
                $count++;
            }

            Messages::save($count . ' jobs were succesfully archived', 'success');
        }

        $this->redirect('/search');
    }

}