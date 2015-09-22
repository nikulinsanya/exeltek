<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Form extends Controller {

    public function action_build()
    {
        $view = View::factory('Form');

        $this->response->body($view);
    }
    public function action_generate()
    {
        Database_Mongo::collection('api')->insert(array('data' => file_get_contents('php://input'), 'post' => $_POST));
    }
}
