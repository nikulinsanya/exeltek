<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_View extends Controller {

    public function action_index() {
        $id = $this->request->param('id');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($id)));

        if (!$job) throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') &&
            !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true) &&
            !in_array((int)User::current('company_id'), Arr::get($job, 'ex', array()), true))
            throw new HTTP_Exception_403('Forbidden');

        if (!Group::current('show_all_jobs') &&
            !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true)) {
            Columns::set_readonly();
        }


        $job['attachments'] = DB::select()
            ->from('attachments')
            ->where('job_id', '=', $id)
            ->and_where('uploaded', '>', '0')
            ->order_by('folder', 'asc')->order_by('numbering', 'asc')
            ->execute()->as_array('id');

        if (isset($_GET['delete']) && Group::current('allow_assign')) {
            $file_id = intval($_GET['delete']);
            if (isset($job['attachments'][$file_id])) {
                if (file_exists(DOCROOT . 'storage/' . $file_id)) unlink(DOCROOT . 'storage/' . $file_id);
                $attachment = $job['attachments'][$file_id];
                $data = array(
                    'filename' => $attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $attachment['filename'],
                    'uploaded' => time(),
                    'user_id' => User::current('id'),
                    'job_id' => $id,
                    'action' => 2,
                );
                Database::instance()->begin();
                DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                DB::delete('attachments')->where('id', '=', $file_id)->execute();
                Database::instance()->commit();
                die(json_encode(array('success' => true)));
            } else throw new HTTP_Exception_404('Not found');
        }

        $users = array();

        $job['region'] = DB::select('name')->from('regions')->where('id', '=', $job['region'])->execute()->get('name');

        $tabs = DB::select()->from('job_tabs')->execute()->as_array('id');

        foreach ($job['attachments'] as $attachment)
            $users[$attachment['user_id']] = 1;

        $job_types = DB::select('id', 'name')->from('job_types')->order_by('name', 'asc')->execute()->as_array('id', 'name');
        $companies = DB::select('id', 'name')->from('companies')->order_by('name', 'asc')->execute()->as_array('id', 'name');


        $submissions = array();

        if (Group::current('allow_assign'))
            $result = Database_Mongo::collection('submissions')->find(array('job_key' => $id))->sort(array('update_time' => -1, 'user_id' => 1, 'key' => 1));
        else {
            $u = DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id');
            $result = Database_Mongo::collection('submissions')->find(array('job_key' => $id, 'user_id' => array('$in' => $u)))->sort(array('update_time' => -1, 'user_id' => 1, 'key' => 1));
        }

        foreach ($result as $submission) {
            $users[$submission['user_id']] = 1;
            $key = substr($submission['key'], 5);
            $data = array(
                'id' => $submission['_id'],
                'key' => str_replace('.', '-', $submission['key']),
                'user_id' => $submission['user_id'],
                'time' => $submission['update_time'],
                'name' => Columns::get_name($key),
                'type' => Columns::get_type($key),
                'active' => Arr::get($submission, 'active'),
                'value' => $submission['value'],
            );
            if (isset($submission['version']))
                $data['version'] = $submission['version'];

            if (Group::current('allow_assign') && Arr::get($submission, 'active') == 1) {
                $tabs[Columns::get_tab($key)]['submissions'][$key] = 1;

                $submissions[$submission['key']][] = $data;
            }

            $submissions['list'][(string)$data['id']] = $data;
        }

        foreach (Columns::get_visible() as $key => $name) {
            $tabs[Columns::get_tab($key)]['columns'][$key] = $name;
        }

        User::get(array_keys($users));

        if ($_POST) {
            if (!Arr::path($_FILES, 'attachment.error', -1) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
                $data = array(
                    'filename' => $_FILES['attachment']['name'],
                    'mime' => $_FILES['attachment']['type'],
                    'uploaded' => time(),
                    'user_id' => User::current('id'),
                    'job_id' => $id,
                );
                Database::instance()->begin();
                $result = DB::insert('attachments', array_keys($data))->values(array_values($data))->execute();
                $image_id = Arr::get($result, 0);
                if ($image_id && move_uploaded_file($_FILES['attachment']['tmp_name'], DOCROOT . 'storage/' . $image_id)) {
                    Database::instance()->commit();
                    Messages::save("File " . $data['filename'] . ' was successfully uploaded!', 'success');
                } else Database::instance()->rollback();
            }
            if (Group::current('allow_assign')) {
                $update = array();
                $archive = array();
                foreach (Arr::get($_POST, 'data', array()) as $key => $value) {
                    $value = Columns::parse($value, Columns::get_type($key));

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
                }


                $list = Arr::get($submissions, 'list', array());

                $pending = array();
                $accepted = array();
                $ignored = array();
                foreach ($list as $submission) {
                    $key = 'submission-' . str_replace('-', '.', $submission['key']);
                    $value = Arr::path($_POST, $key);
                    if ($value !== NULL) {
                        $pending[] = $submission['id'];
                        if ($value)
                            $accepted[] = $submission['id'];
                        else
                            $ignored[] = $submission['id'];
                    }
                }

                foreach (Arr::get($_POST, 'submission-data', array()) as $key => $value) if ($value && isset($list[$value])) {
                    $value = Arr::path($list, $value . '.value');

                    if ($key == 177 || $key == 205) $value = $value ? 1 : 0;

                    if (Arr::path($job, 'data.' . $key) != $value) {
                        if ($value) {
                            $update['$set']['data.' . $key] = $value;
                            unset($update['$unset']['data.' . $key]);
                        } else {
                            $update['$unset']['data.' . $key] = 1;
                            unset($update['$set']['data.' . $key]);
                        }

                        $archive['data'][$key] = array(
                            'old_value' => Arr::path($job, 'data.' . $key),
                            'new_value' => $value,
                        );
                        $job['data'][$key] = $value;
                    }
                }

                if ($pending) {
                    Database_Mongo::collection('submissions')->update(
                        array('_id' => array('$in' => $ignored)),
                        array(
                            '$set' => array('admin_id' => User::current('id'), 'process_time' => time()),
                            '$unset' => array('active' => 1)
                        ),
                        array('multiple' => 1));
                    Database_Mongo::collection('submissions')->update(
                        array('_id' => array('$in' => $accepted)),
                        array(
                            '$set' => array('admin_id' => User::current('id'), 'process_time' => time(), 'active' => -1),
                        ),
                        array('multiple' => 1));
                }

                $ass = false;
                $values = array();
                foreach (Arr::get($_POST, 'assigned', array()) as $key => $value) if (Arr::path($job, 'assigned.' . $key) != $value) {
                    if ($ass < 1) $ass = $value ? 1 : -1;

                    $values[] = array(
                        'time' => time(),
                        'user_id' => User::current('id'),
                        'company_id' => max($value, 0),
                        'job_id' => $job['_id'],
                        'type' => $key,
                    );

                    $unassigned = Arr::path($job, 'assigned.' . $key);

                    if ($value) {
                        $job['assigned'][$key] = $value;
                        $users = DB::select('id')->from('users')->where('company_id', '=', $value)->execute()->as_array(NULL, 'id');

                        $message = '1 ticket was allocated on ' . date('d-m-Y H:i');
                        $insert = DB::insert('notifications', array('user_id', 'message'));

                        foreach ($users as $user)
                            $insert->values(array($user, $message));
                        $insert->execute();
                    } else unset($job['assigned'][$key]);

                    if ($unassigned) {
                        $users = DB::select('id')->from('users')->where('company_id', '=', $unassigned)->execute()->as_array(NULL, 'id');

                        $subs = Database_Mongo::collection('submissions')->findOne(array('job_key' => $job['_id'], 'active' => 1, ));

                        if ($subs) {
                            Messages::save('Unable to unassign the job - please, approve or reject all submissions first!', 'danger');
                        } else {
                            $subs = Database_Mongo::collection('submissions')->find(array(
                                'job_key' => $job['_id'],
                                'user_id' => array('$in' => $users),
                                'financial' => array('$exists' => 0),
                                'active' => -1,
                            ))->sort(array('process_time' => 1));

                            $list = array();
                            foreach ($subs as $sub) $list[$sub['key']] = $sub['_id'];

                            $financial = DB::select('id')->from('job_columns')->where('financial', '>', 0)->execute()->as_array(NULL, 'id');

                            $result = array();
                            foreach ($financial as $column)
                                if (isset($list['data.' . $column]))
                                    $result[$column] = $list['data.' . $column];

                            Database_Mongo::collection('submissions')->update(
                                array('_id' => array('$in' => array_values($result))),
                                array('$set' => array('financial_time' => 0)),
                                array('multiple' => 1)
                            );
                        }
                    }
                }
                if ($ass) {
                    $update['$set']['assigned'] = $job['assigned'];
                    $insert = DB::insert('assign_log', array_keys($values[0]));
                    foreach ($values as $value)
                        $insert->values($value);

                    $insert->execute();

                    $result = Database_Mongo::collection('submissions')->distinct('user_id', array('job_key' => $job['_id']));
                    if ($result)
                        $result = DB::select('company_id')->distinct(true)->from('users')->where('id', 'IN', $result)->and_where('company_id', '>', 0)->execute()->as_array(NULL, 'company_id');

                    $companies = array_flip($result);
                    foreach ($companies as $key => $value)
                        $companies[$key] = 1;

                    $update['$set']['ex'] = array_keys($companies);
                }

                if (!Arr::get($update, '$unset'))
                    unset($update['$unset']);

                $companies = array();
                if (isset($job['assigned'])) foreach ($job['assigned'] as $company) if ($company) $companies[$company] = 1;

                foreach (Columns::$settings as $key => $value) if (!in_array($key, Columns::$settings_read_only, true))
                    if (Arr::get($job, $key) != Arr::get($_POST, $key)) {
                        $value = Arr::get($_POST, $key) ? 1 : 0;
                        $update[$value ? '$set' : '$unset'][$key] = 1;
                        $job[$key] = $value;
                    }

                $discrepancies = Database_Mongo::collection('discrepancies')->find(array('job_key' => $job['_id']))->sort(array('update_time' => -1))->getNext();
                if (Group::current('allow_reports') && $discrepancies) {
                    $fl = 0; $set = array();
                    $ignores = Arr::get($_POST, 'ignore-discrepancy');
                    foreach ($discrepancies['data'] as $key => $values) {
                        $val = Arr::get($values, 'ignore') ? 1 : 0;
                        $new_val = Arr::get($ignores, $key) ? 1 : 0;
                        if ($val != $new_val) {
                            if ($new_val) {
                                if (!$fl) $fl = -1;
                                $set['$set']['data.' . $key . '.ignore'] = 1;
                                $discrepancies['data'][$key]['ignore'] = 1;
                            } else {
                                if ($values['old_value'] != Arr::get($job['data'], $key, ''))
                                    $fl = 1;
                                $set['$unset']['data.' . $key . '.ignore'] = 1;
                                $discrepancies['data'][$key]['ignore'] = 0;
                            }
                        }

                    }
                    if ($set)
                        Database_Mongo::collection('discrepancies')->update(array('_id' => new MongoId($discrepancies['_id'])), $set);

                    if ($fl > 0 && !Arr::get($job, 'discrepancies'))
                        $update['$set']['discrepancies'] = 1;
                    elseif($fl < 0 && Arr::get($job, 'discrepancies')) {
                        $fl = true;
                        foreach ($discrepancies['data'] as $key => $values) if (!Arr::get($values, 'ignore')) {
                            $value = $values['old_value'];
                            if ($value != Arr::get($job['data'], $key, ''))
                                $fl = false;
                        }
                        if ($fl)
                            $update['$unset']['discrepancies'] = 1;
                    }
                }

                if ($update) {
                    Utils::calculate_financial($job);

                    $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($update, array('$set', 'data.44'), '')));

                    if ($status == 'built' && !Arr::path($job, 'data.264'))
                        $update['$set']['data.264'] = time();

                    if ($status == 'tested' && !Arr::path($job, 'data.265'))
                        $update['$set']['data.265'] = time();

                    $update['$set']['companies'] = array_keys($companies);

                    $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);
                    if ($companies && $status == Enums::STATUS_UNALLOC) {
                        $update['$set']['status'] = Enums::STATUS_ALLOC;
                    } elseif (!$companies && $status == Enums::STATUS_ALLOC) {
                        $update['$unset']['status'] = 1;
                    }
                    $update['$set']['last_update'] = time();
                    if (isset($update['$set']['data.8']))
                        $update['$set']['address'] = MapQuest::parse($update['$set']['data.8']);
                    elseif (isset($update['$unset']['data.8']))
                        $update['$unset']['address'] = 1;
                    Database_Mongo::collection('jobs')->update(array('_id' => $id), $update);
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
                    Messages::save("Changes were succesfully saved!", 'success');
                } else
                    Messages::save("No changes were submitted", 'warning');

                if ($pending) {
                    $count = Database_Mongo::collection('submissions')->find(array('job_key' => $id, 'active' => 1))->count();
                    $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);

                    if ($status == Enums::STATUS_PENDING && !$count) {

                        $update = array('$set' => array(
                            'last_update' => time(),
                            'status' => Enums::STATUS_COMPLETE,
                        ));

                        Database_Mongo::collection('jobs')->update(
                            array('_id' => $id),
                            $update
                        );
                    }
                } elseif (in_array(Arr::get($job, 'status', Enums::STATUS_UNALLOC), array(Enums::STATUS_UNALLOC, Enums::STATUS_ALLOC))) {
                    if ($companies)
                        $status = Enums::STATUS_ALLOC;
                    else
                        $status = Enums::STATUS_UNALLOC;

                    if ($status != Arr::get($job, 'status', Enums::STATUS_UNALLOC)) {
                        $update = array('$set' => array('last_update' => time()));

                        if ($status == Enums::STATUS_UNALLOC)
                            $update['$unset']['status'] = 1;
                        else
                            $update['$set']['status'] = $status;

                        Database_Mongo::collection('jobs')->update(
                            array('_id' => $id),
                            $update
                        );
                    }
                } elseif ($ass == 1 && in_array(Arr::get($job, 'status', Enums::STATUS_UNALLOC), array(Enums::STATUS_COMPLETE, Enums::STATUS_ARCHIVE))) {
                    $update = array('$set' => array(
                        'last_update' => time(),
                        'status' => Enums::STATUS_ALLOC,
                    ));
                    Database_Mongo::collection('jobs')->update(
                        array('_id' => $id),
                        $update
                    );
                }
                if ($submissions) Messages::save(count($pending) . '/' . count($submissions['list']) . ' submission(s) were processed.', 'info');
            } else {
                $submissions = array();
                foreach (Arr::get($_POST, 'data', array()) as $key => $value) {
                    $value = Columns::parse($value, Columns::get_type($key));

                    if (Arr::path($job, 'data.' . $key) != $value) {
                        $submissions[$key] = $value;
                    }
                }

                if ($submissions) {
                    $submission = array(
                        'job_key' => $id,
                        'user_id' => User::current('id'),
                        'active' => 1,
                        'update_time' => time(),
                    );
                    if (Arr::get($_POST, 'location'))
                        $submission['location'] = $_POST['location'];

                    $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);

                    $update = array();
                    $approval = false;
                    $archive = array();

                    foreach ($submissions as $key => $value) if (Columns::get_direct($key)) {
                        if ($value)
                            $update['$set']['data.' . $key] = $value;
                        else
                            $update['$unset']['data.' . $key] = 1;

                        $archive['data'][$key] = array(
                            'old_value' => Arr::path($job, 'data.' . $key),
                            'new_value' => $value,
                        );
                        $job['data'][$key] = $value;

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
                    }
                    Messages::save("Changes were succesfully submitted. " . ($approval ? 'Manager will review changes and confirm them.' : ''), 'success');
                } else {
                    Messages::save("No changes were submitted", 'warning');
                }
            }

            $this->redirect('/search');
        }

        $values = array();

        if (!Group::current('allow_assign')) {
            $last = Database_Mongo::collection('submissions')->find(array(
                'job_key' => $id,
                'active' => 1,
                'user_id' => array('$in' => DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id')),
            ))->sort(array('update_time' => 1));

            foreach ($last as $submission)
                $values[str_replace('.', '', $submission['key'])] = $submission['value'];
        }

        $job['discr'] = array();
        if (Group::current('allow_reports')) {
            $job['discr'] = Database_Mongo::collection('discrepancies')->find(array('job_key' => $id))->sort(array('update_time' => -1))->getNext();
        }

        if (Group::current('time_machine')) {
            $result = Database_Mongo::collection('archive')->find(array('job_key' => $job['_id']))->sort(array('update_time' => -1));

            $archive = array();
            $ids = array();
            foreach ($result as $item) {
                $archive[] = $item;
                $ids[Arr::get($item, 'user_id', 0)] = 1;
            }
            User::get(array_keys($ids));
        }

        if (Group::current('allow_quality')) {
            $result = Database_Mongo::collection('quality')->find(array('job_key' => $job['_id']), array('prepared' => 1, 'requestdate' => 1, 'revision' => 1))->sort(array('date' => -1));

            $quality = array();
            foreach ($result as $item)
                $quality[] = $item;

        }

        if (Group::current('allow_finance')) {
            $query = DB::select('company_id', 'payment_time', 'admin_id', array('payments.amount', 'total'), array('payment_jobs.amount', 'amount'))
                ->from('payment_jobs')
                ->join('payments')->on('payment_jobs.payment_id', '=', 'payments.id')
                ->where('job_key', '=', $job['_id'])
                ->order_by('payment_time', 'desc');

            if (!Group::current('show_all_jobs'))
                $query->and_where('company_id', '=', User::current('company_id'));

            $job['payments'] = $query->execute()->as_array();
            $ids = array();

            foreach ($job['payments'] as $payment)
                $ids[$payment['admin_id']] = 1;

            if ($ids) User::get(array_keys($ids));
        }

        $forms = array();
        $result = Database_Mongo::collection('forms-data')->find(array('job' => $job['_id']), array('data' => 0));
        foreach ($result as $form)
            $forms[] = $form;


        $view = View::factory('Jobs/View')
            ->bind('job', $job)
            ->bind('tabs', $tabs)
            ->bind('job_types', $job_types)
            ->bind('companies', $companies)
            ->bind('submissions', $submissions)
            ->bind('values', $values)
            ->bind('archive', $archive)
            ->bind('forms', $forms)
            ->bind('quality', $quality);
        $this->response->body($view);
    }

}