<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Form extends Controller {

    public function action_index() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');

        $result = Database_Mongo::collection('forms')->find(array(), array('type' => 1, 'name' => 1))->sort(array('type' => 1, 'name' => 1));
        $forms = array();
        foreach ($result as $form)
            $forms[$form['type']][strval($form['_id'])] = $form['name'];

        $reports = DB::select('id', 'name')->from('reports')->execute()->as_array('id', 'name');

        $view = View::factory('Forms/Builder')
            ->bind('forms', $forms)
            ->bind('reports', $reports);

        $this->response->body($view);
    }

    public function action_print() {
        $id = Arr::get($_GET, 'id');
        $form_data = Database_Mongo::collection('forms-data')->findOne(array('_id' => new MongoId($id)));

        if (!$form_data) throw new HTTP_Exception_404('Not found');

        $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($form_data['form_id'])));

        if (!$form) throw new HTTP_Exception_404('Not found');

        foreach ($form['data'] as $key => $table) if (is_array($table) && Arr::get($table, 'type') == 'table')
            foreach ($table['data'] as $row => $cells)
                foreach ($cells as $cell => $input)
                    if (Arr::get($input, 'name'))
                        $form['data'][$key]['data'][$row][$cell]['value'] = Arr::get($form_data['data'], $input['name']);



        $view = View::factory('Forms/PDF')
            ->bind('name', $form['name'])
            ->bind('form', $form['data']);

        if (isset($_GET['raw'])) {
            echo $view;
            die();
        }

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
            $form_data = Database_Mongo::collection('forms-data')->findOne(array('_id' => new MongoId($id)));
            $form_id = $form_data['form_id'];
        } else {
            $data = explode('/', Arr::get($_GET, 'form', ''));
            $form_id = $data[0];
            $form_data = array();
        }

        $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($form_id)));
        $form['company'] = User::current('company_id');

        if (!$form) throw new HTTP_Exception_404('Not found');

        if (!$form_data) {
            $form_data['form_id'] = $form_id;
            $form_data['data'] = array();
            foreach ($form['data'] as $key => $table) if (is_array($table) && Arr::get($table, 'type') == 'table')
                foreach ($table['data'] as $row => $cells)
                    foreach ($cells as $cell => $input)
                        if (Arr::get($input, 'name'))
                            $form_data['data'][$input['name']] = '';
        }

        switch ($form['type']) {
            case Form::FORM_TYPE_COMMON:
                break;

            case Form::FORM_TYPE_TICKET:
                if (isset($form_data['job']))
                    $job_id = $form_data['job'];
                else {
                    $job_id = $data[1];
                    $form_data['job'] = $job_id;
                }
                $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($job_id)));
                if (!$job) throw new HTTP_Exception_404('Not found');
                if (!Group::current('show_all_jobs') && !in_array(User::current('company_id'), array_merge(Arr::get($job, 'companies', array()), Arr::get($job, 'ex', array()))))
                    throw new HTTP_Exception_404('Not found');

                foreach ($form['data'] as $key => $table) if (is_array($table) && Arr::get($table, 'type') == 'table')
                    foreach ($table['data'] as $row => $cells)
                        foreach ($cells as $cell => $input)
                            switch (Arr::get($input, 'type')) {
                                case 'ticket':
                                    $form['data'][$key]['data'][$row][$cell] = array(
                                        'type' => 'label',
                                        'placeholder' => Arr::get($input, 'value') ? Columns::output(Arr::path($job, 'data.' . $input['value']), Columns::get_type($input['value'])) : $job['_id'],
                                        'destination' => Arr::get($input, 'destination'),
                                    );
                                    break;
                                case 'timestamp':
                                    $form['data'][$key]['data'][$row][$cell] = array(
                                        'type' => 'timestamp',
                                        'placeholder' => Arr::get($form_data, 'last_update') ? date('d-m-Y H:i', $form_data['last_update']) : '',
                                        'destination' => Arr::get($input, 'destination'),
                                    );
                                    break;
                                case 'revision':
                                    $form['data'][$key]['data'][$row][$cell] = array(
                                        'type' => 'revision',
                                        'placeholder' => Arr::get($form_data, 'revision', 1),
                                        'destination' => Arr::get($input, 'destination'),
                                    );
                                    break;
                            }
                break;
        }

        if (isset($_GET['load']) || $_POST) {
            header('Content-type: application/json');


            if (isset($form_data['job']))
                $job = Database_Mongo::collection('jobs')->findOne(array('_id' => is_array($form_data['job']) ? array('$in' => $form_data['job']) : $form_data['job']));

            if ($_POST) {
                $fl = false;
                foreach ($form_data['data'] as $key => $value) if (Arr::get($_POST, $key) != $value) {
                    $form_data['data'][$key] = Arr::get($_POST, $key);
                    $fl = true;
                }

                if ($fl) {
                    $form_data['last_update'] = time();

                    if (Arr::get($form, 'geo'))
                        $form_data['geo'] = Arr::get($_POST, 'geo');
                }

                if ($id) {
                    if ($fl)
                        $form_data['revision']++;
                    Database_Mongo::collection('forms-data')->update(array('_id' => new MongoId($id)), $form_data);
                } else {
                    $form_data['created'] = time();
                    $form_data['user_id'] = User::current('id');
                    $form_data['revision'] = 1;
                    Database_Mongo::collection('forms-data')->insert($form_data);
                    $id = strval($form['_id']);
                }

                if (isset($_POST['print'])) {
                    $columns = DB::select('id')->from('report_columns')->where('report_id', '=', Arr::get($form, 'report'))->execute()->as_array('id', 'id');
                    $report = array();

                    foreach ($form['data'] as $key => $table) if (is_array($table) && Arr::get($table, 'type') == 'table')
                        foreach ($table['data'] as $row => $cells)
                            foreach ($cells as $cell => $input) {
                                switch (Arr::get($input, 'type')) {
                                    case 'revision':
                                        $form['data'][$key]['data'][$row][$cell]['placeholder'] = $input['placeholder'] = Arr::get($form_data, 'revision', 1);
                                        break;
                                    case 'timestamp':
                                        $form['data'][$key]['data'][$row][$cell]['placeholder'] = $input['placeholder'] = Arr::get($form_data, 'last_update') ? date('d-m-Y H:i', $form_data['last_update']) : '';
                                        break;
                                }
                                if (Arr::get($input, 'name'))
                                    $form['data'][$key]['data'][$row][$cell]['value'] = $input['value'] = Arr::path($form_data, array('data', $input['name']), '');

                                if (Arr::get($input, 'destination') && isset($columns[$input['destination']]))
                                    $report[$input['destination']] = Arr::get($input, in_array(Arr::get($input, 'type', ''), array('text', 'number', 'float', 'date')) ? 'value' : 'placeholder');
                            }

                    $view = View::factory('Forms/PDF')
                        ->bind('name', $form['name'])
                        ->bind('form', $form['data']);

                    require_once(APPPATH . 'mpdf/mpdf.php');
                    $pdf = new mPDF();
                    $pdf->ignore_invalid_utf8 = true;

                    $pdf->WriteHTML($view);
                    $content = $pdf->Output('', 'S');

                    $name = trim(preg_replace('/-{2,}/', '-', preg_replace('/[^a-z0-9]/i', '-', $form['name'])), '-');

                    switch ($form['type']) {
                        case Form::FORM_TYPE_COMMON:
                            $jobs = array(0);
                            break;
                        case Form::FORM_TYPE_TICKET:
                            $jobs = array($job['_id']);
                            break;
                    }

                    $company = DB::select('name')->from('companies')->where('id', '=', User::get($form_data['user_id'], 'company_id'))->execute()->get('name');

                    foreach ($jobs as $job) {
                        if ($job)
                            $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $job), array('data.8' => 1, 'data.14' => 1));

                        $filename = $name . ' (' . $company . ') -' . date('dmY-His') . '.pdf';
                        $uploaded = time();

                        $data = array(
                            'filename' => $filename,
                            'mime' => 'application/pdf',
                            'uploaded' => $uploaded,
                            'user_id' => $form_data['user_id'],
                            'job_id' => $job ? $job['_id'] : 0,
                            'folder' => 'Reports',
                            'fda_id' => $job ? Arr::path($job, 'data.14') : 'Unattached',
                            'address' => $job ? trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-') : 'Unattached',
                            'title' => '',
                        );
                        Database::instance()->begin();
                        $result = DB::insert('attachments', array_keys($data))->values(array_values($data))->execute();
                        $image_id = Arr::get($result, 0);

                        if ($image_id && file_put_contents(DOCROOT . 'storage/' . $image_id, $content)) {
                            unset($data['mime']);
                            $data = array(
                                'filename' => 'Reports / ' . ($job ? Arr::path($job, 'data.14') : 'Unattached') . ' / ' . ($job ? $data['address'] : 'Unattached') . ' / ' . $data['filename'],
                                'uploaded' => $uploaded,
                                'user_id' => User::current('id'),
                                'job_id' => $job ? $job['_id'] : 0,
                                'action' => 1,
                            );
                            DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                            Database::instance()->commit();
                            Database_Mongo::collection('forms-data')->remove(array('_id' => new MongoId($id)));

                            if ($report) {
                                $data = $report;
                                $report = array(
                                    'report_id' => intval(Arr::get($form, 'report')),
                                    'attachment_id' => $image_id,
                                    'attachment' => $filename,
                                    'uploaded' => $uploaded,
                                );

                                if (isset($form_data['geo']))
                                    $report['geo'] = $form_data['geo'];

                                $columns = DB::select('id', 'type')->from('report_columns')->where('report_id', '=', $report['report_id'])->execute()->as_array('id', 'type');
                                foreach ($columns as $key => $value)
                                    $report[$key] = Arr::get($data, $key) ? Columns::parse($data[$key], $value) : '';

                                Database_Mongo::collection('reports')->insert($report);
                            }
                        } else Messages::save('Error occurred during report processing... Please try again later');
                    }

                    $target = 'attachments';
                } else $target = 'forms';
                header('Content-type: application/json');
                switch ($form['type']) {
                    case Form::FORM_TYPE_TICKET:
                        $url = URL::base() . 'search/view/' . $form_data['job'] . '#' . $target;
                        break;

                    case Form::FORM_TYPE_COMMON:
                        $url = URL::base() . 'form/unattached';
                        break;
                }
                die(json_encode(array('success' => true, 'url' => $url)));
            }

            foreach ($form['data'] as $key => $table) if (is_array($table) && Arr::get($table, 'type') == 'table')
                foreach ($table['data'] as $row => $cells)
                    foreach ($cells as $cell => $input)
                        if (Arr::get($input, 'name'))
                            $form['data'][$key]['data'][$row][$cell]['value'] = Arr::get($form_data['data'], $input['name']);

            die(json_encode($form['data']));
        }

        $view = View::factory('Forms/Form')
            ->set('form_id', $form_id)
            ->set('id', $id)
            ->set('allow_geo', Arr::get($form, 'geo'))
            ->set('name', $form['name']);

        $this->response->body($view);
    }

    public function action_save() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');

        $id = Arr::get($_GET, 'id');
        $type = intval(Arr::get($_GET, 'type'));
        $name = strval(Arr::get($_GET, 'name'));
        $data = json_decode(file_get_contents('php://input'), true);

        $form = array(
            'type' => $type,
            'name' => $name,
            'geo' => Arr::get($_GET, 'geo') ? true : false,
            'report' => intval(Arr::get($_GET, 'report')),
            'data' => $data,
        );

        if ($id)
            Database_Mongo::collection('forms')->update(array('_id' => new MongoId($id)), $form);
        else
            Database_Mongo::collection('forms')->insert($form);

        die(json_encode(array('success' => true)));
    }

    public function action_load() {
        if (!Group::current('is_admin')) throw new HTTP_Exception_403('Forbidden');

        $id = Arr::get($_GET, 'id');
        if (!$id) throw new HTTP_Exception_404('Not found');

        $form = Database_Mongo::collection('forms')->findOne(array('_id' => new MongoId($id)));
        if (!$form) throw new HTTP_Exception_404('Not found');

        header('Content-type: application/json');
        die(json_encode(array(
            'success' => true,
            'type' => $form['type'],
            'name' => $form['name'],
            'geo' => Arr::get($form, 'geo') ? true : false,
            'report' => Arr::get($form, 'report'),
            'data' => $form['data'],
        )));
    }

    public function action_unattached() {
        $query = array('type' => Form::FORM_TYPE_COMMON);

        $forms = array();
        $result = Database_Mongo::collection('forms')->find($query, array('name' => 1));
        foreach ($result as $form)
            $forms[strval($form['_id'])] = $form['name'];

        if (Group::current('show_all_jobs')) {
            if (isset($_GET['company'])) {
                $company = is_array($_GET['company']) ? $_GET['company'] : explode(',', $_GET['company']);
                $query['company'] = array('$in' => array_map('intval', $company));
            }
        } else $query['company'] = array('$in' => array(User::current('company_id')));
        $result = Database_Mongo::collection('forms-data')->find($query, array('data' => 0))->sort(array('last_update' => -1));

        $companies = array();
        $list = array();
        foreach ($result as $form) {
            $companies[$form['company']] = 1;
            $list[] = $form;
        }

        if ($companies)
            $companies = DB::select('id', 'name')->from('companies')->where('id', 'IN', array_keys($companies))->execute()->as_array('id', 'name');

        $files = DB::select()->from('attachments')->where('fda_id', '=', 'Unattached')->order_by('uploaded', 'DESC');

        if (isset($query['company']))
            $files->and_where('user_id', 'IN', DB::select('id')->from('users')->where('company_id', 'IN', $query['company']['$in']));

        $files = $files->execute()->as_array();

        $view = View::factory('Forms/Unattached')
            ->bind('forms', $forms)
            ->bind('companies', $companies)
            ->bind('list', $list)
            ->bind('files', $files);

        $this->response->body($view);
    }

}
