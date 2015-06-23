<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imex_Export extends Controller {

    public function action_index()
    {
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');
        
        $form = new Form();
        $form->add('region', 'Region', Form::SELECT, array('' => 'Please, select region') + $regions, array('not_empty'));
        
        if (isset($_POST['job'])) {
            header("Content-type: text/csv");
            header('Content-disposition: filename="' . date('Ymd') . '_EXEL_PARTIAL_EOD.csv"');
            
            $file = tmpfile();
            
            $columns = Columns::get_csv();
            
            fputcsv($file, array(0 => 'Ticket Of Work') + $columns);
            
            $jobs = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($_POST['job']))));
            
            while ($job = $jobs->next()) {
                $data = array_fill(0, count($columns) + 1, '');
                $data[0] = $job['_id'];
                foreach ($columns as $key => $value)
                    $data[$key] = iconv("UTF-8", 'CP1251//ignore', Columns::output(Arr::get($job['data'], $key, ''), Columns::get_type($key), true));
                     
                fputcsv($file, $data);
            }
            rewind($file);
            fpassthru($file);
            fclose($file);
            die();
        } elseif (isset($regions[Arr::get($_POST, 'region')])) {
            $region = $_POST['region'];
            
            header("Content-type: text/csv");
            header('Content-disposition: filename="' . date('Ymd') . '_EXEL_' . $regions[$region] . '_EOD.csv"');
            
            $file = tmpfile();
            
            $columns = Columns::get_csv();
            
            fputcsv($file, array(0 => 'Ticket Of Work') + $columns);
            
            $jobs = Database_Mongo::collection('jobs')->find(array('region' => $region));
            
            while ($job = $jobs->next()) {
                $data = array_fill(0, count($columns) + 1, '');
                $data[0] = $job['_id'];
                foreach ($columns as $key => $value)
                    $data[$key] = iconv("UTF-8", 'CP1251//ignore', Columns::output(Arr::get($job['data'], $key, ''), Columns::get_type($key), true));
                     
                fputcsv($file, $data);
            }
            rewind($file);
            fpassthru($file);
            fclose($file);
            die();
        }

        $this->response->body($form->render());
    }
}
