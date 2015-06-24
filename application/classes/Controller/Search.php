<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search extends Controller {

    public function action_index()
    {
        if (!isset($_GET['ticket']) && !isset($_GET['clear']) && Session::instance()->get('search-settings')) {
            $this->redirect(URL::query(Session::instance()->get('search-settings'), false));
        } elseif (isset($_GET['ticket']) || isset($_GET['clear'])) {
            if (isset($_GET['clear']))
                Session::instance()->delete('search-settings');
            else
                Session::instance()->set('search-settings', $_GET);
        }

        $actions = array(
            'contain',  
            'does not contain',
            '=',
            '<', 
            '<=',
            '>',
            '>=',
            '<>',
            'empty',
            'not empty',
        );
        $actions_mongo = array(
            '='  => '$eq',
            '<'  => '$lt', 
            '<=' => '$lte',
            '>'  => '$gt',
            '>=' => '$gte',
            '<>' => '$ne',
        );
        
        define('JOB_STATUS_COLUMN', 'data.44');

        $columns = Columns::get_visible();

        $reports = Columns::get_static();

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');
        
        $regions = DB::select('id', 'name')->from('regions');

        if ($regs)
            $regions->where('id', 'IN', $regs);
        
        $regions = $regions->execute()->as_array('id', 'name');

        $query = array();
        $list_query = array();

        if (Arr::get($_GET, 'submissions')) {
            $result = Database_Mongo::collection('submissions')->aggregate(array(
                array('$match' => array('active' => 1)),
                array('$group' => array('_id' => '$job_key')),
            ));
            $result = array_column($result['result'], '_id');
            $query['_id'] = array('$in' => $result);
        }

        if (Group::current('show_all_jobs')) {
            if (Arr::get($_GET, 'company'))
                $list_query['companies'] = $query['companies'] = intval($_GET['company']);
            if (Arr::get($_GET, 'ex'))
                $list_query['ex'] = $query['ex'] = intval($_GET['ex']);
        } else {
            if (Arr::get($_GET, 'status', -1) == 0) {
                $list_query['ex'] = $query['ex'] = intval(User::current('company_id'));
            } else 
                $list_query['companies'] = $query['companies'] = intval(User::current('company_id'));
        }

        if (Group::current('allow_assign')) foreach (Columns::$settings as $key => $value)
            if (isset($_GET['settings'][$key]))
                if ($_GET['settings'][$key])
                    $query[$key] = array('$exists' => 1);
                else
                    $query[$key] = array('$exists' => 0);

        if (Arr::get($_GET, 'ticket')) {
            $tickets = explode(',', $_GET['ticket']);
            $q = array();
            foreach ($tickets as $ticket) {
                $ticket = preg_replace('/[^a-z0-9]/i', '', strval($ticket));
                if ($ticket) $q[] = new MongoRegex('/.*' . $ticket . '.*/i');
            }
            if (count($q) > 1)
                $query['_id'] = array('$in' => $q);
            elseif ($q)
                $query['_id'] = $q[0];
        }

        if (Arr::get($_GET, 'start')) {
            $query['last_update']['$gt'] = Columns::parse($_GET['start'], 'date');
        }

        if (Arr::get($_GET, 'end')) {
            $query['last_update']['$lt'] = Columns::parse($_GET['end'], 'date');
        }

        if (Arr::get($_GET, 'submit-start')) {
            $query['last_submit']['$gt'] = Columns::parse($_GET['submit-start'], 'date');
        }

        if (Arr::get($_GET, 'submit-end')) {
            $query['last_submit']['$lt'] = Columns::parse($_GET['submit-end'], 'date');
        }
        $status = Arr::get($_GET, 'status', Group::current('allow_assign') ? -1 : Enums::STATUS_ALLOC);
        $_GET['status'] = intval($status);
        if ($status != -1 && (Group::current('show_all_jobs') || $status)) {
            $query['status'] = intval($status) ? : array('$exists' => 0);
        }

        if (!isset($_GET['region'])) $_GET['region'] = User::current('default_region');
        
        if (Arr::get($_GET, 'region') && (!$regs || isset($regs[$_GET['region']]))) {
            $list_query['region'] = $query['region'] = strval($_GET['region']);
        } elseif ($regs) {
            $list_query['region'] = $query['region'] = array('$in' => array_values($regs));
        }

        if (Arr::get($_GET, 'type')) {
            $query['assigned.' . $_GET['type']] = array('$exists' => 1);
        }

        foreach (Arr::get($_GET, 'columns', array()) as $id => $column) if ($column) {
            $op = Arr::get($actions, Arr::path($_GET, 'actions.' . $id), 0);
            $value = Arr::path($_GET, 'values.' . $id, '');
            
            $value = Columns::parse($value, Columns::get_type($column));
            
            if (Columns::get_type($column) == 'date')
                if ($op === 'contain')
                    $op = '=';
                elseif ($op === 'does not contain')
                    $op = '<>';

            if ($op === 'contain') {
                $op = '$regex';
                $value = new MongoRegex('/.*' . preg_replace('/[^a-z0-9,.+:;!? -]/i', '', $value) . '.*/i');
            } elseif ($op === 'does not contain') {
                $op = '$not';
                $value = new MongoRegex('/.*' . preg_replace('/[^a-z0-9,.+:;!? -]/i', '', $value) . '.*/i');
            } elseif ($op === 'empty') {
                $op = '$exists';
                $value = 0;
            } elseif ($op === 'not empty') {
                $op = '$exists';
                $value = 1;
            } else {
                $op = Arr::get($actions_mongo, $op, '$eq');
            }

            if (isset($query['data.' . $column])) {
                if (isset($query['data.' . $column][$op])) {
                    if ($op == '$regex') {
                        $query['data.' . $column][$op] = $value;
                    } else {
                        if (is_array($query['data.' . $column][$op]))
                            $query['data.' . $column][$op][] = $value;
                        else
                            $query['data.' . $column][$op] = array($query['data.' . $column][$op], $value);
                    }
                } elseif (isset($query['data.' . $column]['$or'][$op])) {

                }
                else
                    $query['data.' . $column][$op] = $value;
            } else
                $query['data.' . $column] = array($op => $value);
        }                       
        
        foreach ($query as $key => $ops) if (is_array($ops))
            foreach ($ops as $op => $value)
                if ($op == '$eq' && is_array($value)) {
                    $query[$key]['$in'] = $value;
                    unset($query[$key]['$eq']);
                }

                $jobs = Database_Mongo::collection('jobs');

        $list_values = array();
        foreach (Columns::get_search() as $key => $value) if ($value == 2) {
            $list_values[$key] = $jobs->distinct('data.' . $key, $query ? : NULL);
            if ($list_values[$key]) sort($list_values[$key]);
        }
        
        Pager::$count = $jobs->count($query);

        $result = $jobs->find($query);
        
        $sort = Arr::get($_GET, 'sort');
        if (is_array($sort)) {
            $save = implode('|', $sort);
            DB::update('users')->set(array('default_sort' => $save))->where('id', '=', User::current('id'))->execute();
        } else {
            $sort = explode('|', User::current('default_sort') ? : '-update');
            $_GET['sort'] = $sort;
        }
        
        $sorting = array();
        foreach ($sort as $order) {
            $dir = substr($order, 0, 1) == '-' ? -1 : 1;
            $order = substr($order, 1);
            switch ($order) {
                case 'id':
                    $order = '_id';
                break;
                case 'update':
                    $order = 'last_update';
                break;
                case 'submit':
                    $order = 'last_submit';
                break;
                case 'status':
                    $order = 'status';
                break;
                /*case 'data-8':
                    $sorting['data.119'] = $dir;
                    $sorting['data.118'] = $dir;
                    $order = false;
                break;*/
                default:
                    if (substr($order, 0, 5) == 'data-')
                        $order = 'data.' . intval(substr($order, 5));
                    else
                        $order = false;
            }
            if ($order) $sorting[$order] = $dir;
        }
        
        $result->sort($sorting)->limit(Pager::limit())->skip(Pager::offset());
        
        $tickets = array();
        while ($row = $result->next())
            $tickets[] = $row;


        $ids = array_column($tickets, '_id');

        $types = DB::select('id', 'name')->from('job_types')->execute()->as_array('id', 'name');
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        if ($ids)
            $attachments = DB::select('job_id', DB::expr('COUNT(*) as cnt'))
            ->from('attachments')
            ->where('job_id', 'IN', $ids)
            ->and_where('uploaded', '>', 0)
            ->group_by('job_id')
            ->execute()->as_array('job_id', 'cnt');
        else $attachments = array();

        if (Group::current('allow_assign')) {
            $result = Database_Mongo::collection('submissions')->aggregate(
                array(
                    array('$match' => array('job_key' => array('$in' => $ids), 'active' => 1)),
                    array('$group' => array('_id' => '$job_key', 'count' => array('$sum' => 1))),
            ));

            $submissions = array();
            foreach (Arr::get($result, 'result', array()) as $value)
                $submissions[$value['_id']] = $value['count'];
        }

        $view = View::factory('Jobs/Search')
            ->bind('reports', $reports)
            ->bind('regions', $regions)
            ->bind('columns', $columns)
            ->bind('actions', $actions)
            ->bind('tickets', $tickets)
            ->bind('submissions', $submissions)
            ->bind('attachments', $attachments)
            ->bind('list_values', $list_values)
            ->bind('types', $types)
            ->bind('sort', $sort)
            ->bind('companies', $companies);
        $this->response->body($view);
    }

    public function action_export() {
        $ids = array_keys(Arr::get($_POST, 'job'));
        if (!$ids) throw new HTTP_Exception_404('Not found');

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');

        $query = array();

        if ($regs)
            $query['region'] = array('$in' => array_values($regs));

        if (!Group::current('show_all_jobs')) {
            $query['$or'] = array(
                array('ex' => intval(User::current('company_id'))),
                array('companies' => intval(User::current('company_id'))),
            );
        }

        $query['_id'] = array('$in' => $ids);

        $jobs = Database_Mongo::collection('jobs')->find($query);

        $static = array_flip(explode(',', Group::current('columns')));

        $header = array(
            'Ticket ID',
        );

        $types = DB::select('id', 'name')->from('job_types')->execute()->as_array('id', 'name');
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        if ($ids)
            $attachments = DB::select('job_id', DB::expr('COUNT(*) as cnt'))
                ->from('attachments')
                ->where('job_id', 'IN', $ids)
                ->and_where('uploaded', '>', 0)
                ->group_by('job_id')
                ->execute()->as_array('job_id', 'cnt');
        else $attachments = array();

        if (Group::current('allow_assign')) {
            $result = Database_Mongo::collection('submissions')->aggregate(
                array(
                    array('$match' => array('job_key' => array('$in' => $ids), 'active' => 1)),
                    array('$group' => array('_id' => '$job_key', 'count' => array('$sum' => 1))),
                ));

            $submissions = array();
            foreach (Arr::get($result, 'result', array()) as $value)
                $submissions[$value['_id']] = $value['count'];
        }

        if (isset($static['last_update'])) $header[] = 'Last update';
        if (isset($static['last_submit'])) $header[] = 'Last submit';
        if (isset($static['status']) && Group::current('show_all_jobs')) $header[] = 'Job status';
        if (isset($static['types'])) $header[] = 'Assigned works';
        if (isset($static['companies'])) $header[] = 'Assigned companies';
        if (isset($static['pending'])) $header[] = 'Pending submissions';
        if (isset($static['attachments'])) $header[] = 'Attachments';

        foreach (Columns::get_search() as $id => $type)
            $header[] = Columns::get_name($id);

        $file = tmpfile();

        fputcsv($file, $header);

        foreach ($jobs as $ticket) {
            $row = array($ticket['_id']);

            if (isset($static['last_update'])) $row[] = date('d-m-Y H:i', Arr::get($ticket, 'last_update', $ticket['created']));
            if (isset($static['last_submit'])) $row[] = Arr::get($ticket, 'last_submit') ? date('d-m-Y H:i', $ticket['last_submit']) : '';
            if (isset($static['status']) && Group::current('show_all_jobs')) $row[] = Arr::get(Enums::$statuses, Arr::get($ticket, 'status', 0), 'Unknown');
            if (isset($static['types']))
                if (Group::current('allow_assign'))
                    $row[] = implode(', ', array_intersect_key($types, Arr::get($ticket, 'assigned', array())));
                else
                    $row[] = implode(', ', array_intersect_key($types, array_filter(Arr::get($ticket, 'assigned', array()), function ($x) {
                        return $x == User::current('company_id');
                    })));


            if (isset($static['companies'])) $row[] = implode(', ', array_intersect_key($companies, array_flip(Arr::get($ticket, 'assigned', array()))));
            if (isset($static['pending'])) $row[] = Arr::get($submissions, $ticket['_id']);
            if (isset($static['attachments'])) $row[] = Arr::get($attachments, $ticket['_id']);

            foreach (Columns::get_search() as $id => $type)
                $row[] = Arr::path($ticket, array('data', $id)) ? Columns::output($ticket['data'][$id], Columns::get_type($id), true) : '';

            fputcsv($file, $row);
        }

        fseek($file, 0);

        header('Content-type: text/csv');
        header('Content-disposition: filename="SearchResults.csv"');

        fpassthru($file);

        fclose($file);

        //print_r($query);
        die();
    }

    public function action_view() {
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

        $pending_submissions = false;
        
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
                $finish = array();
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

                foreach (Columns::$settings as $key => $value)
                if (Arr::get($job, $key) != Arr::get($_POST, $key)) {
                    $value = Arr::get($_POST, $key) ? 1 : 0;
                    $update[$value ? '$set' : '$unset'][$key] = 1;
                    $job[$key] = $value;
                }

                if ($update) {
                    $update['$set']['companies'] = array_keys($companies);

                    $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);
                    if ($companies && $status == Enums::STATUS_UNALLOC) {
                        $update['$set']['status'] = Enums::STATUS_ALLOC;
                    } elseif (!$companies && $status == Enums::STATUS_ALLOC) {
                        $update['$unset']['status'] = 1;
                    }
                    $update['$set']['last_update'] = time();
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
                $submission = array(
                    'job_key' => $id,
                    'user_id' => User::current('id'),
                    'update_time' => time(),
                );

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
        
        $view = View::factory('Jobs/View')
            ->bind('job', $job)
            ->bind('tabs', $tabs)
            ->bind('job_types', $job_types)
            ->bind('companies', $companies)
            ->bind('submissions', $submissions)
            ->bind('values', $values);
        $this->response->body($view);
    }

    public function action_form() {
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

    public function action_assign() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        $type = Arr::get($_POST, 'type');
        $company = Arr::get($_POST, 'company');

        if (!$ids)
            Messages::save('Please, select at least one job!');
        elseif (!$type)
            Messages::save('Please, select works type!');
        elseif (!$company)
            Messages::save('Please, select company!');
        elseif (DB::select('id')->from('job_types')->where('id', '=', $type)->execute()->get('id') &&
        ($company == -1 || DB::select('id')->from('companies')->where('id', '=', $company)->execute()->get('id')))  {

            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            $values = array();
            $users = array();
            while ($job = $result->next()) if (Arr::path($job, 'assigned.' . $type) != $company) {
                $update = array();
                if (Arr::path($job, 'assigned.' . $type)) {
                    $users = DB::select('id')->from('users')->where('company_id', '=', $job['assigned'][$type])->execute()->as_array(NULL, 'id');

                    $subs = Database_Mongo::collection('submissions')->findOne(array('job_key' => $job['_id'], 'active' => 1, 'user_id' => array('$in' => $users)));

                    if ($subs) {
                        continue;
                    } else {
                        $subs = Database_Mongo::collection('submissions')->find(array(
                            'job_key' => $job['_id'],
                            'active' => -1,
                        ))->sort(array('process_time' => 1));

                        $list = array();
                        foreach ($subs as $sub) $list[$sub['key']] = $sub['_id'];

                        $financial = DB::select('id')->from('job_columns')->where('financial', '>', 0)->execute()->as_array(NULL, 'id');

                        $res = array();
                        foreach ($financial as $column)
                            if (isset($list['data.' . $column]))
                                $res[$column] = $list['data.' . $column];

                        Database_Mongo::collection('submissions')->update(
                            array('_id' => array('$in' => array_values($res))),
                            array('$set' => array('financial_time' => 0)),
                            array('multiple' => 1)
                        );

                        if (!in_array($job['assigned'][$type], Arr::get($job, 'ex', array()), true)) {
                            $job['ex'][] = intval($job['assigned'][$type]);
                            $update['$set']['ex'] = $job['ex'];
                        }
                    }
                }

                if ($company == -1)
                    unset($job['assigned'][$type]);
                else
                    $job['assigned'][$type] = $company;

                if (Arr::get($job, 'assigned'))
                    $update['$set']['assigned'] = $job['assigned'];
                else
                    $update['$unset']['assigned'] = 1;
                $update['$set']['last_update'] = time();

                $companies = array();
                if (Arr::get($job, 'assigned')) 
                    foreach ($job['assigned'] as $cmp) if ($cmp) $companies[$cmp] = 1;
                
                $update['$set']['companies'] = array_keys($companies);
                $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);
                if ($companies && $status == Enums::STATUS_UNALLOC) {
                    $update['$set']['status'] = Enums::STATUS_ALLOC;
                } elseif (!$companies && $status == Enums::STATUS_ALLOC) {
                    $update['$unset']['status'] = 1;
                } else $update['$set']['status'] = Enums::STATUS_COMPLETE;

                $count++;

                $jobs->update(array('_id' => $job['_id']), $update);

                $values[] = array(
                    'time' => time(),
                    'user_id' => User::current('id'),
                    'company_id' => max($company, 0),
                    'job_id' => $job['_id'],
                    'type' => $type,
                );
            }
            
            if ($values) {
                $insert = DB::insert('assign_log', array_keys($values[0]));
                foreach ($values as $value)
                    $insert->values($value);
                    
                $insert->execute();
            }

            if ($company == -1)
                Messages::save($count . ' jobs were succesfully unassigned', 'success');
            else {
                Messages::save($count . ' jobs were succesfully assigned to ' . Company::get($company, 'name'), 'success');
                
                $users = DB::select('id')->from('users')->where('company_id', '=', $company)->execute()->as_array(NULL, 'id');
                
                $message = $count . ' tickets were allocated on ' . date('d-m-Y H:i');
                $insert = DB::insert('notifications', array('user_id', 'message'));
                
                foreach ($users as $user)
                    $insert->values(array($user, $message));
                $insert->execute();
            }
        }

        $this->redirect('/search');
    }

    public function action_archive() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            Messages::save('Please, select at least one job!');
        else {
            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            foreach ($result as $job) if (Arr::get($job, 'status') != Enums::STATUS_PENDING && Arr::get($job, 'status') != Enums::STATUS_ARCHIVE) {
                $jobs->update(array('_id' => $job['_id']), array('$set' => array('last_update' => time(), 'status' => Enums::STATUS_ARCHIVE)));
                $count++;
            }

            Messages::save($count . ' jobs were succesfully archived', 'success');
        }

        $this->redirect('/search');
    }

    public function action_complete() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            Messages::save('Please, select at least one job!');
        else {
            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            foreach ($result as $job) if (!in_array(Arr::get($job, 'status'), array(Enums::STATUS_PENDING, Enums::STATUS_ARCHIVE, Enums::STATUS_COMPLETE))) {
                $jobs->update(array('_id' => $job['_id']), array('$set' => array('last_update' => time(), 'status' => Enums::STATUS_COMPLETE)));
                $count++;
            }

            Messages::save($count . ' jobs were succesfully completed', 'success');
        }

        $this->redirect('/search');
    }

    public function action_reset() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            Messages::save('Please, select at least one job!');
        else {
            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            foreach ($result as $job) if (Arr::get($job, 'status') == Enums::STATUS_ARCHIVE || Arr::get($job, 'status') == Enums::STATUS_COMPLETE) {
                $update = array('$set' => array('last_update' => time()));

                if (Arr::get($job, 'assigned'))
                    $update['$set']['status'] = Enums::STATUS_ALLOC;
                else
                    $update['$unset']['status'] = 1;

                $jobs->update(array('_id' => $job['_id']), $update);
                $count++;
            }

            Messages::save($count . ' jobs were succesfully reset to initial state', 'success');
        }

        $this->redirect('/search');
    }

    public function action_prepare() {
        $id = $this->request->param('id');
        $location = Arr::get($_GET, 'location', '');
        $type = Arr::get($_GET, 'type', 'other');
        $title = Arr::get($_GET, 'title', 'other');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($id)));

        if (!$job)
            throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            throw new HTTP_Exception_403('Forbidden');
            
        switch ($type) {
            case 'photo-before':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.before.%NUM%';
                $title = '';
            break;
            case 'photo-after':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.after.%NUM%';
                $title = '';
            break;
            case 'jsa':
                $type = 'JSA-forms';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.JSA.%NUM%';
                $title = '';
            break;
            case 'waiver':
                $type = 'Waiver';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.Waiver.%NUM%';
                $title = '';
            break;
            case 'odtr':
                $title = '';
                $type = 'otdr-traces';
                $filename = '';
            break;
            default:
                $type = 'other';
                $filename = '';
            break;
        }
            
        $number = DB::select('numbering')
            ->from('attachments')
            ->where('job_id', '=', $id)
            ->and_where('folder', '=', $type)
            ->order_by('numbering', 'desc')
            ->limit(1)
            ->execute()->get('numbering');
            
        $data = array(
            'filename' => $filename,
            'mime' => '',
            'uploaded' => 0,
            'user_id' => User::current('id'),
            'job_id' => $id,
            'folder' => $type,
            'fda_id' => Arr::path($job, 'data.14'),
            'address' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-'),
            'title' => $title,
            'numbering' => intval($number) + 1,
        );
        
        $result = Arr::get(DB::insert('attachments', array_keys($data))->values(array_values($data))->execute(), 0);

        if (file_exists(DOCROOT . 'storage/' . $result))
            unlink(DOCROOT . 'storage/' . $result);
        
        die(json_encode(array(
            'success' => true,
            'id' => $result,
        )));
    }

    public function action_upload() {
        $id = intval($this->request->param('id'));

        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        if (!$attachment)
            throw new HTTP_Exception_404('Not found');

        if (Arr::get($attachment, 'uploaded'))
            throw new HTTP_Exception_403('Forbidden');

        $job_id = Arr::get($attachment, 'job_id');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($job_id)));

        if (!$job)
            throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            throw new HTTP_Exception_403('Forbidden');

        if ($_FILES) {
            try {
                $file = Arr::get($_FILES, 'attachment', array());

                $file['name'] = trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-\.]/i', '-', Arr::get($file, 'name', ''))), '-');

                preg_match_all("/([0-9]+)/", Arr::get($_SERVER, 'HTTP_CONTENT_RANGE', ''), $matches);

                $range = Arr::get($matches, 0);

                $size = Arr::get($range, 2, filesize($file['tmp_name']));
                if (!is_uploaded_file($file['tmp_name'])) {
                    die(json_encode(array(
                        'attachment' => array(
                            'name' => $file['name'],
                            'size' => $size,
                            'error' => 'Error!',
                        ),
                    )));
                }

                $src = fopen($file['tmp_name'], 'r');
                $dest = fopen(DOCROOT . 'storage/' . $id, 'c');
                fseek($dest, Arr::get($range, 0, 0));
                $buf = fread($src, $size);
                fwrite($dest, $buf);

                fclose($dest);
                fclose($src);
                unlink($file['tmp_name']);
                
                if (!$range || ((Arr::get($range, 1) + 1) == Arr::get($range, 2))) {
                    $data = array(
                        'filename' => $file['name'],
                        'mime' => $file['type'],
                        'uploaded' => time(),
                    );
                    if ($attachment['filename']) {
                        $data['filename'] = $attachment['filename'];

                        $pos = strrpos($file['name'], '.');
                        if ($pos !== false)
                            $data['filename'] .= substr($file['name'], $pos);
                    } else {
                        $data['filename'] = ($attachment['folder'] == 'Other' ? $attachment['title'] : '') . $file['name'];
                    }
                    $data['filename'] = str_replace('%NUM%', $attachment['numbering'], $data['filename']);
                    Database::instance()->begin();
                    DB::update('attachments')->set($data)->where('id', '=', $id)->execute();
                    $data = array(
                        'user_id' => User::current('id'),
                        'job_id' => $attachment['job_id'],
                        'uploaded' => $data['uploaded'],
                        'location' => $attachment['location'],
                        'filename' => $attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $data['filename'],
                        'action' => 1,
                    );
                    DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                    Database::instance()->commit();
                    Messages::save("File " . $file['name'] . ' was successfully uploaded!', 'success');
                    die(json_encode(array(
                        'attachment' => array(
                            'name' => $file['name'],
                            'size' => $size,
                            'content' => (Group::current('allow_assign') ? '<a href="' . URL::base(). 'search/view/' . $id . '?delete=' . $id . '"
                                confirm="Do you really want to delete this attachment? This action can\'t be undone!!!"
                                class="text-danger glyphicon glyphicon-remove remove-link"></a>' : '') . 
                            '<a href="' . URL::base() . 'download/attachment/' . $id . '">
                            <img src="http://stdicon.com/' . $file['type'] . '?size=32&default=http://stdicon.com/text" />' . 
                            HTML::chars($data['filename']) . '</a>
                            - Uploaded ' . date('d-m-Y H:i', $data['uploaded']) . ' by ' . User::current('login'),
                            'message' => Messages::render(),
                        ),
                    )));
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            die(json_encode(array(
                'attachment' => array(
                    'name' => $file['name'],
                    'size' => $size,
                ),
            )));
        }               

        $view = View::factory("Jobs/UploadFile");

        $this->response->body($view);
    }
}