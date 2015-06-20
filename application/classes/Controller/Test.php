<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Kohana_Controller {

    public function action_index() {
        $companies = DB::select('id', 'company_id')->from('users')->where('company_id', '>', 0)->execute()->as_array('id', 'company_id');
        $financial = DB::select(DB::expr("CONCAT('data.', `id`) as id"))->from('job_columns')->where('financial', '>', 0)->execute()->as_array(NULL, 'id');

        $result = Database_Mongo::collection('submissions')->find(array('active' => -1, 'key' => array('$in' => $financial)));

        $jobs = array();
        $submissions = array();
        foreach ($result as $submission) {
            if (!isset($submission['financial_time'])) {
                $jobs[$submission['job_key']] = 1;
                $submissions[] = $submission;
            }
        }

        $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($jobs))), array('companies' => 1));
        $jobs = array();
        foreach ($result as $job)
            $jobs[$job['_id']] = $job['companies'];

        foreach ($submissions as $submission) {
            $company = Arr::get($companies, $submission['user_id']);

            if (!in_array($company, $jobs[$submission['job_key']])) {
                Database_Mongo::collection('submissions')->update(array('_id' => $submission['_id']), array('$set' => array('financial_time' => 0)));
                print_r($submission);
            }
        }

        die();
    }

}
