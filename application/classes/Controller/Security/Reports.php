<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Reports extends Controller
{

    public function before() {
        parent::before();

        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $reports = DB::select('id', 'name')->from('reports')->execute()->as_array('id', 'name');

        $view = View::factory('Security/Reports')
            ->bind('reports', $reports);

        $this->response->body($view);
    }

    public function action_load() {
        $id = Arr::get($_GET, 'id');

        $report = DB::select('id', 'name')->from('reports')->where('id', '=', $id)->execute()->current();

        if (!$report) die(json_encode(array('success' => false)));

        $report['success'] = true;

        $report['data'] = DB::select('id', 'name', 'type', 'visible')->from('report_columns')->where('report_id', '=', $id)->order_by('auto_id', 'ASC')->execute()->as_array('id');

        die(json_encode($report));
    }

    public function action_update() {
        $id = Arr::get($_REQUEST, 'id');

        $name = Arr::get($_REQUEST, 'name');

        $data = Arr::get($_REQUEST, 'data');

        Database::instance()->begin();
        if ($id) {
            $report = DB::select()->from('reports')->where('id', '=', $id)->execute()->current();
            if (!$report) throw new HTTP_Exception_404('Not found');

            DB::update('reports')->set(array('name' => $name))->where('id', '=', $id)->execute();

            DB::delete('report_columns')->where('report_id', '=', $id)->execute();
        } else {
            $id = Arr::get(DB::insert('reports', array('name'))->values(array($name))->execute(), 0);
        }

        if ($data) {
            $query = DB::insert('report_columns', array('report_id', 'id', 'name', 'type', 'visible'));

            Database_Mongo::collection('api')->insert($data);
            unset($data['_id']);
            foreach ($data as $key => $value)
                $query->values(array($id, $key, Arr::get($value, 'name', ''), Arr::get($value, 'type', ''), Arr::get($value, 'visible', 'read')));

            $query->execute();
        }

        Database::instance()->commit();

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'id' => $id)));
    }

}