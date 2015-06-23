<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Kohana_Controller {

    public function action_index() {

        $jobs = Database_Mongo::collection('jobs')->distinct('_id');

        $ids = Database_Mongo::collection('archive')->distinct('job_key');

        $missing = array_diff($jobs, $ids);

        foreach ($missing as $id)
            if (in_array($id, $jobs))
                echo $id;
        //print_r($missing);

        die();
    }

}
