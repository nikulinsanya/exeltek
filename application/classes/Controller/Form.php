<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Form extends Controller {

    public function action_index() {
        $result = Database_Mongo::collection('forms')->find(array(), array('type' => 1, 'name' => 1))->sort(array('type' => 1, 'name' => 1));
        $forms = array();
        foreach ($result as $form)
            $forms[$form['type']][strval($form['_id'])] = $form['name'];

        $view = View::factory('Forms/Builder')
            ->bind('forms', $forms);

        $this->response->body($view);
    }

    public function action_build()
    {
        $info = Database_Mongo::collection('api')->findOne(array('_id' => new MongoId('5601c4b7c92cb670368b4690')));

        $view = View::factory('Form')
            ->set('json', json_encode($info['data']));

        $this->response->body($view);
    }

    public function action_fill() {
        $id = Arr::get($_GET, 'id');
        if ($id) {
            $form = Database_Mongo::collection('forms-data')->findOne(array('_id' => new MongoId($id)));
            $form_id = false;
        } else {
            $form_id = Arr::get($_GET, 'form');
            $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($form_id)));
        }

        if (!$form) throw new HTTP_Exception_404('Not found');

        if ($this->request->is_ajax()) {
            header('Content-type: application/json');

            if ($_POST) {
                foreach ($form['data'] as $key => $values) if (is_array($values))
                    foreach ($values as $v => $input)
                        if (Arr::get($input, 'name')) {
                            $form['data'][$key][$v]['value'] = Columns::parse(Arr::get($_POST, $input['name']), Arr::get($input, 'type'));
                        }
                unset($form['_id']);

                if ($id)
                    Database_Mongo::collection('forms-data')->update(array('_id' => $id), $form);
                else
                    Database_Mongo::collection('forms-data')->insert($form);
            }
            die(json_encode($form['data']));
        }

        $view = View::factory('Forms/Form')
            ->set('form_id', $form_id)
            ->set('id', $id)
            ->set('name', $form['name']);

        $this->response->body($view);
    }

    public function action_save() {

        $id = Arr::get($_GET, 'id');
        $type = intval(Arr::get($_GET, 'type'));
        $name = strval(Arr::get($_GET, 'name'));
        $data = json_decode(file_get_contents('php://input'), true);

        $form = array(
            'type' => $type,
            'name' => $name,
            'data' => $data,
        );

        if ($id)
            Database_Mongo::collection('forms')->update(array('_id' => new MongoId($id)), $form);
        else
            Database_Mongo::collection('forms')->insert($form);

        die(json_encode(array('success' => true)));
    }

    public function action_load() {
        $id = Arr::get($_GET, 'id');
        if (!$id) throw new HTTP_Exception_404('Not found');

        $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($id)));
        if (!$form) throw new HTTP_Exception_404('Not found');

        header('Content-type: application/json');
        die(json_encode(array(
            'success' => true,
            'type' => $form['type'],
            'name' => $form['name'],
            'data' => $form['data'],
        )));
    }

    public function action_generate()
    {
        Database_Mongo::collection('api')->insert(array('data' => json_decode(file_get_contents('php://input'), true)));
    }
}
