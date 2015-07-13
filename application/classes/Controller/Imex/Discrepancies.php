<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imex_Discrepancies extends Controller {
    
    public function before() {
        parent::before();

        if (!Group::current('allow_reports'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    private function get_results($all = false) {
        $archive = Database_Mongo::collection('discrepancies');
        
        $filters = array();

        if (Arr::get($_GET, 'ticket')) {
            $filters['job_key'] = $_GET['ticket'];
        }
        if (Arr::get($_GET, 'file')) {
            $filters['filename'] = $_GET['file'];
        }
        if (Arr::get($_GET, 'start')) {
            $start = strtotime($_GET['start']);
            $filters['update_time']['$gt'] = $start;
        }
        if (isset($filters['end'])) {
            $end = strtotime($_GET['end']);
            $filters['update_time']['$lt'] = $end;
        }
        
        $result = $archive->find($filters)->sort(array('update_time' => -1, 'job_key' => 1));
        
        if (!$all) {
            Pager::$count = $result->count();
            $result->skip(Pager::offset())->limit(Pager::limit());
        }
        
        $list = array();
        $ids = array();
        
        foreach ($result as $row) {
            $list[] = $row;
            $ids[$row['job_key']] = 1;
        }
        $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($ids))));
        $jobs = array();
        
        foreach ($result as $job)
            $jobs[$job['_id']] = Arr::get($job, 'data');
            
        $items = array();
        
        foreach ($list as $row) {
            $row['data'] = array_intersect_key($row['data'], Columns::get_visible());
            $row['current'] = array();

            foreach ($row['data'] as $key => $value) {
                $row['current'][$key] = Arr::path($jobs, array($row['job_key'], $key));
            }

            foreach (Columns::get_static() as $static => $show)
                $row['static'][$static] = Arr::path($jobs, array($row['job_key'], $static));
                            
            $items[] = $row;
        }

        return $items;
    }
    
    public function action_index() {
        
        $view = View::factory('Jobs/Discrepancies');
        
        $result = $this->get_results();
        
        $reports = array();
        foreach (Columns::get_static() as $column => $value)
            $reports[$column] = Columns::get_name($column);
        //print_r(array_shift($result));
        
        $view->bind('tickets', $result)
            ->bind('hidden', $hidden)
            ->bind('reports', $reports);

        $this->response->body($view);
    }
    
    public function action_export() {
        $id = $this->request->param('id');
        
        if ($id == 'all') ini_set('memory_limit', '512M');
        
        header('Content-type: text/csv');
        header('Content-disposition: filename=Discrepancy.csv');
        
        $result = $this->get_results($id == 'all');
        
        $reports = array();
        foreach (Columns::get_static() as $column => $value)
            $reports[$column] = Columns::get_name($column);
        
        $file = tmpfile();
        
        $data = array(
            'Ticket ID',
            'Date',
            'File name',
        );
        foreach ($reports as $id => $name) $data[] = $name;
        $data[] = "Column name";
        $data[] = "Old value";
        $data[] = "New value";
        $data[] = 'Current value';

        fputcsv($file, $data);
        
        foreach ($result as $ticket) {
            $ticket['update_time'] = date('d-m-Y H:i', $ticket['update_time']);

            $data = array(
                $ticket['job_key'],
                $ticket['update_time'],
                $ticket['filename'],
            );
            foreach ($reports as $id => $name) $data[] = Arr::path($ticket, 'static.' . $id);

            $base = $data;
            foreach ($ticket['data'] as $key => $value) {
                $data = $base;
                $date = Columns::get_type($key) == 'date';
                $data[] = Columns::get_name($key);
                $data[] = $date && $value['old_value'] ? date('d-m-Y H:i', $value['old_value']) : $value['old_value'];
                $data[] = $date && $value['new_value'] ? date('d-m-Y H:i', $value['new_value']) : $value['new_value'];
                $current = $ticket['current'][$key];
                $data[] = $date && $current ? date('d-m-Y H:i', $current) : $current;
                fputcsv($file, $data);
            }
        }
        
        rewind($file);
        
        fpassthru($file);
        
        die();
    }
    
}