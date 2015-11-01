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

        $reports = array();
        $columns = DB::select('id', 'name', 'type')->from('report_columns')->where('report_id', '=', $query['report_id'])->execute()->as_array('id');
        foreach ($result as $report) {
            $id = strval($report['_id']);
            $data = array(
                'attachment' => Arr::get($report, 'attachment', 'Unknown file'),
                'attachment_id' => Arr::get($report, 'attachment_id', 0),
            );
            foreach ($columns as $key => $column)
                $data[$key] = Arr::get($report, $key) ? Columns::output($report[$key], $column['type']) : '';

            $reports[$id] = $data;
        }


        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'columns' => $columns, 'data' => $reports)));
    }

}