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

        if ($attachments) {
            $attachments = DB::select('id', 'filename')->from('attachments')->where('id', 'IN', array_keys($attachments))->execute()->as_array('id', 'filename');
            foreach ($reports as $id => $report)
                $reports[$id]['attachment'] = Arr::get($attachments, $report['attachment_id'], 'Unknown file');
        }

        $columns = DB::select('id', 'name')->from('report_columns')->where('report_id', '=', $query['report_id'])->execute()->as_array('id', 'name');

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'columns' => $columns, 'data' => $reports)));
    }

}