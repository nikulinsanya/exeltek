<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Form extends Controller {

    public function action_index() {
        $id = $this->request->param('id');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($id)));

        if (!$job) throw new HTTP_Exception_404('Not found');

        if (Arr::get($job, 'locked')) throw new HTTP_Exception_403('Forbidden');

        $job['region'] = DB::select('name')->from('regions')->where('id', '=', $job['region'])->execute()->get('name');

        $types = array();
        foreach (Arr::get($job, 'assigned', array()) as $type => $company)
            if ($company == User::current('company_id'))
                $types[] = $type;

        if (!$types) throw new HTTP_Exception_403('Forbidden');

        $columns_sets = Form::$columns;

        if (Arr::get($_POST, 'signature') && Arr::get($_POST, 'signed')) {

            $completed = Arr::get($_POST, 'completed');
            $data = Arr::get($_POST, 'data-' . $completed);

            $submissions = array();
            foreach ($columns_sets as $title => $columns) if ($completed == crc32($title)) {
                foreach ($columns as $map => $value) {
                    $value = Arr::get($data, $map, '');
                    if ($value === '') continue;

                    $map = explode(',', $map);
                    foreach ($map as $key) if (is_numeric($key)) {
                        $val = Columns::parse($value, Columns::get_type($key));
                        $submissions[$key] = $val;
                    }
                }

                if (Arr::get($data, 'ot1') || Arr::get($data, 'ot2')) {
                    $submissions['43'] = '1310-' . Arr::get($data, 'ot1', '') . ' 1625-' . Arr::get($data, 'ot2', '');
                }

                if ($submissions)
                    $submissions['245'] = strpos($title, 'Yes') === 0 ? 'Yes' : 'No';

                break;
            }

            if (Arr::get($_POST, 'upload-count'))
                Messages::save(intval($_POST['upload-count']) . ' file(s) were successfully uploaded', 'success');

            if ($submissions) {
                $data = array(
                    'filename' => 'Submission-' . date('dmY-His') . '-signature.png',
                    'mime' => 'image/png',
                    'uploaded' => time(),
                    'user_id' => User::current('id'),
                    'job_id' => $id,
                    'folder' => 'Others',
                    'fda_id' => Arr::path($job, 'data.14'),
                    'address' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-'),
                    'title' => '',
                );
                Database::instance()->begin();
                $result = DB::insert('attachments', array_keys($data))->values(array_values($data))->execute();
                $image_id = Arr::get($result, 0);
                $content = explode(',', Arr::get($_POST, 'signature'));
                if ($image_id && file_put_contents(DOCROOT . 'storage/' . $image_id, base64_decode(Arr::get($content, 1, '')))) {
                    unset($data['mime']);
                    $data = array(
                        'filename' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', 'other / ' . Arr::path($job, 'data.14') . ' / ' . Arr::path($job, 'data.8') . ' / Submission-' . date('dmY-His') . '-signature.png')), '-'),
                        'uploaded' => time(),
                        'user_id' => User::current('id'),
                        'job_id' => $id,
                        'action' => 1,
                    );
                    DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                    Database::instance()->commit();

                    $submission = array(
                        'job_key' => $id,
                        'user_id' => User::current('id'),
                        'update_time' => time(),
                    );
                    if (Arr::get($_POST, 'location'))
                        $submission['location'] = $_POST['location'];

                    $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);

                    $update = array();
                    $approval = false;
                    $archive = array();

                    foreach ($submissions as $key => $value) if (Columns::get_direct($key)) {
                        if (Arr::path($job, 'data.' . $key) != $value) {
                            if ($value)
                                $update['$set']['data.' . $key] = $value;
                            else
                                $update['$unset']['data.' . $key] = 1;

                            $archive['data'][$key] = array(
                                'old_value' => Arr::path($job, 'data.' . $key),
                                'new_value' => $value,
                            );
                            $job['data'][$key] = $value;
                        }

                        $submission['key'] = 'data.' . $key;
                        $submission['value'] = $value;
                        $submission['active'] = -1;
                        $submission['process_time'] = $submission['update_time'];
                        Database_Mongo::collection('submissions')->insert($submission);
                        unset($submission['process_time']);
                        unset($submission['_id']);
                    } else {
                        $approval = true;

                        if ($status != Enums::STATUS_PENDING)
                            $status = $update['$set']['status'] = Enums::STATUS_PENDING;

                        $submission['key'] = 'data.' . $key;
                        $submission['value'] = $value;
                        $submission['active'] = 1;
                        Database_Mongo::collection('submissions')->insert($submission);
                        unset($submission['_id']);
                    }

                    if ($update) {
                        $update['$set']['last_update'] = time();
                        if ($approval)
                            $update['$set']['last_submit'] = time();
                        Database_Mongo::collection('jobs')->update(
                            array('_id' => $id),
                            $update
                        );

                        if ($archive) {
                            foreach (Columns::get_static() as $key => $value)
                                $archive['static'][$key] = Arr::path($job, 'data.' . $key);
                            $archive['fields'] = array_keys($archive['data']);
                            $archive['job_key'] = $id;
                            $archive['user_id'] = User::current('id');
                            $archive['update_time'] = time();
                            $archive['update_type'] = 2;
                            $archive['filename'] = 'MANUAL';
                            Database_Mongo::collection('archive')->insert($archive);
                        }
                    } elseif ($approval)
                        Database_Mongo::collection('jobs')->update(
                            array('_id' => $id),
                            array('$set' => array('last_submit' => time()))
                        );
                    Messages::save("Changes were succesfully submitted. " . ($approval ? 'Manager will review changes and confirm them.' : ''), 'success');
                } else {
                    Database::instance()->rollback();
                    Messages::save("Unable to save signature image! Please, try again in few minutes", 'danger');
                }
            } else Messages::save("No changes were submitted", 'warning');
            $this->redirect('search');
        }

        $last = Database_Mongo::collection('submissions')->find(array(
            'job_key' => $id,
            'active' => array('$exists' => 1),
            'user_id' => array('$in' => DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id')),
        ))->sort(array('update_time' => 1));

        $values = array();
        foreach ($last as $submission)
            $values[str_replace('.', '', $submission['key'])] = array('status' => Arr::get($submission, 'active', 0), 'value' => $submission['value']);

        $view = View::factory('Jobs/Form')
            ->bind('job', $job)
            ->bind('job_values', $values)
            ->bind('columns', $columns_sets);

        $this->response->body($view);
    }
}