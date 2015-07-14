<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Controller {

    public function before() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $start = microtime(true);

        header("Content-type: text/plain");
        $jobs = Database_Mongo::collection('jobs')->find();

        $archive = Database_Mongo::collection('archive');

        $ids = array();
        foreach ($jobs as $job) if (Arr::path($job, 'data.245') == 'Yes') {
            $result = $archive->find(array(
                'job_key' => $job['_id'],
                'fields' => array('$in' => array(245, 43, 190, 191, 192)),
            ))->sort(array('update_time' => 1));
            $indexes = array();

            if (Arr::get($job['data'], 43))
                $indexes['t'] = 43;

            if (Arr::get($job['data'], 43))
                $indexes['a'] = 190;

            if (Arr::get($job['data'], 43))
                $indexes['b'] = 191;

            if (Arr::get($job['data'], 43))
                $indexes['c'] = 192;

            $finished = 0;
            $types = array();
            foreach ($result as $item) {
                if (isset($item['data']['245']))
                    $finished = $item['update_time'];
                foreach ($indexes as $key => $value)
                    if (isset($item['data'][$value]))
                        $types[$key] = $item['update_time'];

            }
            if ($finished && $types) {
                if (isset($types['t']))
            }
        }

        die('Done in ' . round(microtime(true) - $start, 3) . 's.');
    }

}
