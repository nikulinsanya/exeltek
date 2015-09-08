<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Form extends Controller {

    public function action_build()
    {
        $view = View::factory('Form');

        $this->response->body($view);
    }
    public function action_generate()
    {
        
    }
}
