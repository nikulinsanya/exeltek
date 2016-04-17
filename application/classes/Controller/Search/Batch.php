<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Batch extends Controller
{
    public function action_index()
    {
        if (!Group::current('allow_assign')) throw new HTTP_Exception_403('Forbidden');

        header('Content-type: application/json');

        $action = $this->request->param('id');

        $result = array();

        $columns = DB::select('id')->from('job_columns')->where('editable', '=', 1)->execute()->as_array('id', 'id');

        switch ($action) {
            case 'get':
                $data = array();

                $result['columns'] = array();

                foreach ($columns as $column) {
                    $value = array(
                        'id' => intval($column),
                        'name' => Columns::get_name($column),
                    );
                    $type = Columns::get_type($column);

                    if (substr($type, 0, 4) == 'enum') {
                        $id = substr($type, 5);
                        $type = Enums::is_multi($id) ? 'multi' : 'list';
                        $value['values'] = array_values(Enums::get_values($id));
                    }
                    $value['type'] = $type ? : 'string';

                    $result['columns'][] = $value;

                    $data[] = 'data.' . $column;
                }

                $ids = explode(',', strval(Arr::get($_GET, 'id', '')));
                $values = array();
                if ($ids) {
                    $query = array('_id' => array('$in' => $ids));
                    if (!Group::current('show_all_jobs'))
                        $query['companies'] = intval(User::current('company_id'));

                    $jobs = Database_Mongo::collection('jobs')->find($query, $data);
                    foreach ($jobs as $job) {
                        foreach ($job['data'] as $key => $value)
                            $job['data'][$key] = Columns::output($value, Columns::get_type($key), true);
                        $job['id'] = $job['_id'];
                        unset($job['_id']);
                        $values[] = $job;
                    }
                }
                $result['jobs'] = $values;
                break;
            case 'set':
                if (User::current('login') !== Arr::get($_POST, 'username'))
                    die('Wrong username! Please, check it and submit data again.');

                $data = Arr::get($_POST, 'jobs');
                $values = array();
                foreach ($data as $job) {
                    $id = strval(Arr::get($job, 'id', ''));
                    foreach (Arr::get($job, 'data') as $key => $value) if (isset($columns[$key]))
                        $values[$id][$key] = strval($value);
                }

                $query = array('_id' => array('$in' => array_keys($values)));
                if (!Group::current('show_all_jobs'))
                    $query['companies'] = intval(User::current('company_id'));

                $data = array();
                foreach ($columns as $column) {
                    $data['data.' . $column] = 1;
                }

                $count = 0;

                $jobs = Database_Mongo::collection('jobs')->find($query, $data);
                foreach ($jobs as $job) if (isset($values[$job['_id']])) {
                    $id = $job['_id'];
                    $new = array();
                    $archive = array();
                    foreach ($values[$id] as $key => $value) {
                        $value = $value ? Columns::parse($value, Columns::get_type($key)) : '';
                        $old = Arr::path($job, 'data.' . $key);
                        if (($value || $old) && $value != $old) {
                            if ($value)
                                $new['$set']['data.' . $key] = $value;
                            else
                                $new['$unset']['data.' . $key] = 1;

                            $archive['data'][$key] = array(
                                'old_value' => $old,
                                'new_value' => $value,
                            );
                        }
                    }
                    if ($new) {
                        $new['$set']['last_update'] = time();
                        Database_Mongo::collection('jobs')->update(array('_id' => $id), $new);
                        $archive['fields'] = array_keys($archive['data']);
                        $archive['job_key'] = $id;
                        $archive['user_id'] = User::current('id');
                        $archive['update_time'] = time();
                        $archive['update_type'] = 2;
                        $archive['filename'] = 'MANUAL';
                        Database_Mongo::collection('archive')->insert($archive);
                        $count++;
                    }
                }

                $result = array('success' => true, 'count' => $count);
                break;
        }

        die(json_encode($result));
    }

}