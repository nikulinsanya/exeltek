<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Form extends Controller {

    public function action_build()
    {
        $info = Database_Mongo::collection('api')->findOne(array('_id' => new MongoId('5601c4b7c92cb670368b4690')));

        $view = View::factory('Form')
            ->set('json', json_encode($info['data']));

        $this->response->body($view);
    }
    public function action_generate()
    {
        Database_Mongo::collection('api')->insert(array('data' => json_decode(file_get_contents('php://input'), true)));
    }
}
