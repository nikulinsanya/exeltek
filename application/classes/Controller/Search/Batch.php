<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Batch extends Controller
{
    public function action_index()
    {
        header('Content-type: application/json');

        $action = $this->request->param('id');

        $result = array();

        switch ($action) {
            case 'get':
                $columns = DB::select('id')->from('job_columns')->where('editable', '=', 1)->execute()->as_array(NULL, 'id');
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
                        $job['id'] = $job['_id'];
                        unset($job['_id']);
                        $values[] = $job;
                    }
                }
                $result['jobs'] = $values;
                break;
            case 'save':
                break;
        }

        die(json_encode($result));
    }

}