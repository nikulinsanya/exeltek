<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Structure extends Controller
{

    public function before()
    {
        parent::before();

        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index()
    {
        $columns = DB::select()->from('job_columns')->execute()->as_array();
        $tabs = DB::select()->from('job_tabs')->execute()->as_array('id', 'name');
        $enums = DB::select()->from('enumerations')->execute()->as_array('id', 'name');

        $view = View::factory('Security/Structure')
            ->bind('tabs', $tabs)
            ->bind('columns', $columns)
            ->bind('enums', $enums);

        $this->response->body($view);
    }

    public function action_tab() {
        $id = $this->request->param('id');
        $name = Arr::get($_POST, 'name');
        if (!$name) throw new HTTP_Exception_400('Wrong name');

        if ($id)
            if ($name == 'REMOVE')
                DB::delete('job_tabs')->where('id', '=', $id)->execute();
            else
                DB::update('job_tabs')->set(array('name' => $name))->where('id', '=', $id)->execute();
        else
            $id = Arr::get(DB::insert('job_tabs', array('name'))->values(array($name))->execute(), 0);

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'id' => $id)));
    }

    public function action_load() {
        $id = $this->request->param('id');

        $item = DB::select()->from('job_columns')->where('id', '=', $id)->execute()->current();

        $item = array(
            'id' => intval($item['id']),
            'type' => $item['type'],
            'tab_id' => intval($item['tab_id']),
            'name' => $item['name'],
            'csv' => $item['csv'] ? true : false,
            'show_reports' => $item['show_reports'] ? true : false,
            'direct' => $item['direct'] ? true : false,
            'financial' => floatval($item['financial']),
            'track' => $item['track'] ? true : false,
            'persistent' => $item['persistent'] ? true : false,
            'editable' => $item['editable'] ? true : false,
            'read_only' => $item['read_only'] ? true : false,
        );

        header('Content-type: application/json');
        die(json_encode($item));
    }
    
    public function action_save() {
        $id = $this->request->param('id');

        $item = array(
            'type' => Arr::get($_POST, 'type'),
            'tab_id' => intval(Arr::get($_POST, 'tab_id')),
            'name' => Arr::get($_POST, 'name'),
            'csv' => Arr::get($_POST, 'csv') ? true : false,
            'show_reports' => Arr::get($_POST, 'show_reports') ? true : false,
            'direct' => Arr::get($_POST, 'direct') ? true : false,
            'financial' => floatval(Arr::get($_POST, 'financial')),
            'track' => Arr::get($_POST, 'track') ? true : false,
            'persistent' => Arr::get($_POST, 'persistent') ? true : false,
            'editable' => Arr::get($_POST, 'editable') ? true : false,
            'read_only' => Arr::get($_POST, 'read_only') ? true : false,
        );

        if ($id)
            DB::update('job_columns')->set($item)->where('id', '=', $id)->execute();
        else
            $id = Arr::get(DB::insert('job_columns', array_keys($item))->values(array_values($item))->execute(), 0);

        header('Content-type: application/json');
        die(json_encode(array('success' => true, 'id' => $id)));
    }
}