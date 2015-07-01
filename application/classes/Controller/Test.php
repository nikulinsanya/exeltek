<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Controller {

    public function before() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');
        phpinfo();
        die();
    }

    public function action_index() {
        $jobs = Database_Mongo::collection('jobs');

        $archive = Database_Mongo::collection('archive');

        $region = 2;

        $list = $jobs->find(array('last_update' => array('$exists' => 0)));

        foreach ($list as $item) {
            $jobs->update(array('_id' => $item['_id']), array('$set' => array('last_update' => $item['created'])));
        }

        $file = fopen(DOCROOT . '/tmp/jeopardy.csv', 'r');

        fgetcsv($file);

        $count = 0;

        while ($row = fgetcsv($file)) {
            $job = $jobs->findOne(array('_id' => $row[0]));
            //if ($job) $jobs->remove(array('_id' => $row[0]));
            if (!$job) {
                $count++;
                $data = array(
                    '1' => $row[1],
                    '8' => $row[2],
                    '9' => $row[3],
                    '10' => $row[4],
                    '13' => $row[5],
                    '14' => $row[6],
                    '17' => strtotime($row[7]),
                    '18' => strtotime($row[8]),
                    '19' => $row[9],
                    '20' => $row[10],
                );

                $job = array(
                    '_id' => $row[0],
                    'region' => $region,
                    'created' => time(),
                    'last_update' => time(),
                    'data' => $data,
                );
                $jobs->insert($job);
                $archive->insert(array(
                    'job_key' => $row[0],
                    'update_time' => time(),
                    'update_type' => 1,
                    'user_id' => User::current('id'),
                    'filename' => 'JEOPARDY',
                    'static' => array(),
                    'data' => array(),
                    'fields' => array(),
                ));
            }
        }

        die('Done: ' . $count);
    }

}
