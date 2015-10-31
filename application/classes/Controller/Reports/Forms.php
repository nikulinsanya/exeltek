<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Reports_Forms extends Controller
{

    public function action_index() {
        $reports = DB::select()->from('reports')->execute()->as_array('id', 'name');

        $view = View::factory('Reports/Forms')
            ->bind('reports', $reports);

        $this->response->body($view);
    }

    public function action_search() {
        $query = array(
            'report_id' => intval(Arr::get($_GET, 'id')),
        );
        $result = Database_Mongo::collection('reports')->find($query);

        $attachments = array();

        $reports = array();
        foreach ($result as $report) {
            $report['_id'] = strval($report['_id']);
            $reports[$report['_id']] = $report;

            $attachments[$report['attachment_id']] = 1;
        }

        if ($attachments)
            $attachments = DB::select()->from('attachments')->where('id', 'IN', array_keys($attachments))->execute()->as_array('id');

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'data' => $reports, 'attachments' => $attachments)));
    }

}