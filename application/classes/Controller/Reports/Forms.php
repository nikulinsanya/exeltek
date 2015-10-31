<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Reports_Forms extends Controller
{

    public function action_index() {
        $reports = DB::select()->from('reports')->execute()->as_array('id', 'name');

        $view = View::factory('Reports/Forms')
            ->bind('reports', $reports);

        $this->response->body($view);
    }

}