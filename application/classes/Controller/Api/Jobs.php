<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Jobs extends Kohana_Controller {

    public function action_index() {
        $token = Arr::get($_REQUEST, 'token');

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array(NULL, 'region_id');

        $query = array(
            'companies' => intval(User::current('company_id'))
        );

        if ($regs) $query['region'] = array('$in' => $regs);

        $result = Database_Mongo::collection('jobs')->find($query, array('created' => '1', 'last_update' => 1));

        $jobs = array();

        Form::$static_title[] = 245;

        $columns = array_flip(array_merge(array_keys(Columns::get_static()), Form::$static_title));

        $columns[8] = 1;
        $columns[12] = 1;
        $columns[13] = 1;
        $columns[14] = 1;

        foreach (Form::$columns as $type => $list) foreach ($list as $keys => $name) foreach (explode(',', $keys) as $key)
            if (is_numeric($key)) $columns[$key] = 1;

        foreach ($result as $job) {
            $jobs[$job['_id']] = Arr::get($job, 'last_update') ?: Arr::get($job, 'created');
        }

        $list = array();
        foreach ($columns as $column => $value) {
            $value = array('n' => Columns::get_name($column));
            $type = Columns::get_type($column);
            if ($type)
                if (substr($type, 0, 4) == 'enum') {
                    $value['t'] = 'enum';
                    $value['m'] = Enums::is_multi(substr($type, 5));
                    $value['v'] = array_values(Enums::get_values(substr($type, 5)));
                } else $value['t'] = Columns::get_type($column);
            $list[$column] = $value;
        }
        $columns = $list;
        ksort($columns);

        $form = array();
        foreach (Form::$columns as $id => $list)
            foreach ($list as $key => $value)
                $form[$id][] = array(
                    'k' => $key,
                    'v' => $value,
                );

        if (isset($_GET['gzip']))
            die(gzcompress(json_encode(array(
                'success' => true,
                'columns' => $columns,
                'static' => Form::$static_title,
                'form' => $form,
                'jobs' => $jobs,
            )), 9));
        else {
            header('Content-type: application/json');
            die(json_encode(array(
                'success' => true,
                'columns' => $columns,
                'static' => Form::$static_title,
                'form' => $form,
                'jobs' => $jobs,
            )));
        }
    }

    public function action_get() {
        $token = Arr::get($_REQUEST, 'token');

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $id = Arr::get($_REQUEST, 'id');

        Form::$static_title[] = 245;

        $columns = array_flip(array_merge(array_keys(Columns::get_static()), Form::$static_title));

        $columns[8] = 1;
        $columns[12] = 1;
        $columns[13] = 1;
        $columns[14] = 1;

        foreach (Form::$columns as $type => $list) foreach ($list as $key => $name)
            if (is_numeric($key)) $columns[$key] = 1;

        if (!$id)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (!is_array($id)) $id = array($id);

        $query = array(
            'companies' => intval(User::current('company_id')),
            '_id' => array('$in' => $id),
        );

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array(NULL, 'region_id');

        if ($regs) $query['region'] = array('$in' => $regs);

        $result = Database_Mongo::collection('jobs')->find($query, array('assigned' => 0, 'companies' => 0, 'ex' => 0, 'address' => 0));

        if (!$result)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $jobs = array();
        foreach ($result as $job) {
            $job['data'] = array_intersect_key(Arr::get($job, 'data', array()), $columns);
            $jobs[$job['_id']] = $job;
        }

        if ($jobs) {
            $attachments = DB::select()->from('attachments')->where('uploaded', '>', 0)->and_where('job_id', 'IN', array_keys($jobs))->execute()->as_array();
            foreach ($attachments as $attachment)
                $jobs[$attachment['job_id']]['attachments'][] = array(
                    'id' => $attachment['id'],
                    'time' => $attachment['uploaded'],
                    'folder' => $attachment['folder'],
                    'name' => $attachment['filename'],
                    'mime' => $attachment['mime'],
                );

            $users = DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id');

            $result = Database_Mongo::collection('submissions')->find(array('job_key' => array('$in' => array_keys($jobs)), 'user_id' => array('$in' => $users)), array('update_time' => 1, 'job_key' => 1, '_id' => 0));
            $submissions = array();
            foreach ($result as $submission)
                $submissions[$submission['job_key']][$submission['update_time']] = 1;

            foreach ($submissions as $key => $list)
                foreach (array_keys($list) as $value)
                    $jobs[$key]['submissions'][] = $value;
        }

        if (isset($_GET['gzip']))
            die(gzcompress(json_encode(array_values($jobs)), 9));
        else {
            header('Content-type: application/json');
            die(json_encode(array_values($jobs)));
        }
    }

    public function action_submit() {
        $token = Arr::get($_REQUEST, 'token');

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $id = strval(Arr::get($_REQUEST, 'id'));

        if (!$id)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $id));

        if (!$job)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');

        if ($regs) $query['region'] = array('$in' => $regs);

        if ($regs && !isset($regs[$job['region']]))
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (!isset($job['companies']) || !in_array(intval(User::current('company_id')), $job['companies'], true))
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $time = time();
        $signature = strval(Arr::get($_REQUEST, 'signature'));
        $submissions = Arr::get($_REQUEST, 'data');
        $completed = Arr::get($_REQUEST, 'completed');
        $location = strval(Arr::get($_REQUEST, 'location'));

        if (!$signature || !$submissions || !$completed)
            die(json_encode(array('success' => false, 'error' => 'wrong data')));

        foreach (Form::$columns as $key => $columns) if ($key == $completed) {
            $data = array(
                'filename' => 'Submission-' . date('dmY-His') . '-signature.png',
                'mime' => 'image/png',
                'uploaded' => $time,
                'user_id' => User::current('id'),
                'job_id' => $id,
                'folder' => 'Signatures',
                'fda_id' => Arr::path($job, 'data.14'),
                'address' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-'),
                'title' => '',
            );
            Database::instance()->begin();
            $result = DB::insert('attachments', array_keys($data))->values(array_values($data))->execute();
            $image_id = Arr::get($result, 0);
            if ($image_id && file_put_contents(DOCROOT . 'storage/' . $image_id, base64_decode($signature))) {
                unset($data['mime']);
                $data = array(
                    'filename' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', 'Signatures / ' . Arr::path($job, 'data.14') . ' / ' . Arr::path($job, 'data.8') . ' / Submission-' . date('dmY-His') . '-signature.png')), '-'),
                    'uploaded' => $time,
                    'user_id' => User::current('id'),
                    'job_id' => $id,
                    'action' => 1,
                );
                DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                Database::instance()->commit();
                $submission = array(
                    'job_key' => $id,
                    'user_id' => User::current('id'),
                    'update_time' => $time,
                    'version' => Arr::get($_REQUEST, 'ver'),
                );
                if ($location)
                    $submission['location'] = $location;

                $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);

                $update = array();
                $approval = false;
                $archive = array();

                foreach ($submissions as $key => $value) if (is_numeric($key)) {
                    $value = Columns::parse($value, Columns::get_type($key));
                    if (Columns::get_direct($key)) {

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
                        $submission['key'] = 'data.' . $key;
                        $submission['value'] = $value;
                        $submission['active'] = 1;
                        Database_Mongo::collection('submissions')->insert($submission);
                        unset($submission['_id']);
                    }
                }

                if ($status != Enums::STATUS_PENDING)
                    $update['$set']['status'] = Enums::STATUS_PENDING;

                if ($update) {
                    $update['$set']['last_update'] = $time;
                    $update['$set']['last_submit'] = $time;
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
                        $archive['update_time'] = $time;
                        $archive['update_type'] = 2;
                        $archive['filename'] = 'MANUAL';
                        Database_Mongo::collection('archive')->insert($archive);
                    }
                }
            } else {
                Database::instance()->rollback();
                die(json_encode(array('success' => false, 'error' => 'signature problem')));
            }
            if (Kohana::$environment == Kohana::DEVELOPMENT)
                Database_Mongo::collection('api')->insert($_REQUEST);
            die(json_encode(array('success' => true, 'time' => $time)));
        }

        die(json_encode(array('success' => false, 'error' => 'wrong key')));
    }

    public function action_submission() {
        $token = Arr::get($_REQUEST, 'token');

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $id = strval(Arr::get($_REQUEST, 'id'));

        if (!$id)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $id));

        if (!$job)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $time = intval($_GET['time']);

        $users = DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id');

        $result = Database_Mongo::collection('submissions')->find(array('job_key' => $id, 'update_time' => $time, 'user_id' => array('$in' => $users)));

        $submission = array();
        foreach ($result as $item)
            $submission[] = array(
                'id' => intval(substr($item['key'], 5)),
                'value' => strval($item['value']),
            );

        if (!$submission)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        die(json_encode(array('success' => true, 'data' => $submission)));
    }
}