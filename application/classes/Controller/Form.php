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

    public function action_print() {
        $id = Arr::get($_GET, 'id');
        $form = Database_Mongo::collection('forms-data')->findOne(array('_id' => new MongoId($id)));

        if (!$form) throw new HTTP_Exception_404('Not found');

        $view = View::factory('Forms/PDF')
            ->bind('form', $form['data']);

        $view = View::factory('Forms/PDF')
            ->bind('form', $form['data']);

        require_once(APPPATH . 'mpdf/mpdf.php');
        $pdf = new mPDF();
        $pdf->ignore_invalid_utf8 = true;

        $pdf->WriteHTML($view);
        $content = $pdf->Output('', 'S');

        header('Content-type: application/pdf');
        echo $content;
        die();
    }

    public function action_fill() {
        $id = Arr::get($_GET, 'id');
        if ($id) {
            $form = Database_Mongo::collection('forms-data')->findOne(array('_id' => new MongoId($id)));
            $form_id = false;
        } else {
            list($form_id, $job_id) = explode('/', Arr::get($_GET, 'form', ''));
            $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($job_id)));
            if (!$job) throw new HTTP_Exception_404('Not found');
            $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($form_id)));
        }

        if (!$form) throw new HTTP_Exception_404('Not found');

        if ($this->request->is_ajax()) {
            header('Content-type: application/json');

            foreach ($form['data'] as $key => $values) if (is_array($values))
                foreach ($values as $v => $input)
                    if (Arr::get($input, 'type') == 'ticket' && !isset($input['value']))
                        $form['data'][$key][$v]['value'] = Arr::get($input, 'fieldId') ? Columns::output(Arr::path($job, 'data.' . $input['fieldId']), Columns::get_type($input['fieldId'])) : $job['_id'];

            if ($_POST) {
                foreach ($form['data'] as $key => $values) if (is_array($values))
                    foreach ($values as $v => $input)
                        if (Arr::get($input, 'name')) {
                            $form['data'][$key][$v]['value'] = Arr::get($_POST, $input['name']);
                        }

                unset($form['_id']);
                $form['last_update'] = time();

                if ($id) {
                    $form['revision']++;
                    Database_Mongo::collection('forms-data')->update(array('_id' => new MongoId($id)), $form);
                } else {
                    $form['job'] = $job_id;
                    $form['created'] = time();
                    $form['user_id'] = User::current('id');
                    $form['revision'] = 1;
                    Database_Mongo::collection('forms-data')->insert($form);
                }
                die(json_encode(array('success' => true, 'url' => URL::base() . 'search/view/' . $form['job'] . '#forms')));
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
