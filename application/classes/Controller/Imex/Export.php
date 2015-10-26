<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imex_Export extends Controller {

    public function action_index()
    {
        if (!Group::current('allow_reports'))
            throw new HTTP_Exception_403('Forbidden');

        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');
        $groups = DB::select('id', 'name')->from('groups')->execute()->as_array('id', 'name');
        
        $form = new Form();
        $form->add('region', 'Region', Form::SELECT, array('' => 'Please, select region') + $regions, array('not_empty'));

        if (isset($_POST['group']) && isset($_POST['csv'])) {
            if (isset($_POST['job'])) {
                $query = array('_id' => array('$in' => array_keys($_POST['job'])));
                $region = array('name' => 'PARTIAL');
            } else {
                if (!Arr::get($_POST, 'region'))
                    throw new HTTP_Exception_404('Not found');
                $region = DB::select('id', 'name')->from('regions')->where('id', '=', $_POST['region'])->execute()->current();

                $query = array('region' => $region['id']);
            }

            $jobs = Database_Mongo::collection('jobs')->find($query);

            $columns = array();
            $csv = $_POST['csv'];
            if ($_POST['group']) {
                if (Group::get($_POST['group'], 'is_admin'))
                    $result = DB::select('id')->from('job_columns')->execute()->as_array(NULL, 'id');
                else
                    $result = DB::select('column_id')->from('group_columns')->where('group_id', '=', $_POST['group'])->and_where('permissions', '>', 0)->execute()->as_array(NULL, 'column_id');

                if ($csv != 'none') foreach ($result as $column)
                    if ($csv == 'all' || !(($csv == 'csv') xor Columns::get_csv($column)))
                        $columns[$column] = Columns::get_name($column);
            } else {
                foreach (Arr::get($_POST, 'columns', array()) as $column => $value)
                    $columns[$column] = Columns::get_name($column);
            }

            header("Content-type: text/csv");
            header('Content-disposition: filename="' . date('Ymd') . '_EXEL_' . $region['name'] . '_EOD.csv"');

            $file = tmpfile();

            fputcsv($file, array(0 => 'Ticket Of Work') + $columns);

            while ($job = $jobs->next()) {
                $data[0] = $job['_id'];
                $i = 1;
                foreach ($columns as $key => $value)
                    $data[$i++] = iconv("CP1251", 'CP1251//ignore', Columns::output(Arr::get($job['data'], $key, ''), Columns::get_type($key), true));

                fputcsv($file, $data);
            }
            rewind($file);
            fpassthru($file);
            fclose($file);
            die();
        }

        $view = View::factory('Jobs/Export')
            ->bind('regions', $regions)
            ->bind('groups', $groups);
        $this->response->body($view);
    }
}
