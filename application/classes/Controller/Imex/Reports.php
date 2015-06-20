<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imex_Reports extends Controller {
    
    public function before() {
        parent::before();

        if (!Group::current('allow_reports'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    private function get_results($all = false) {
        $archive = Database_Mongo::collection('archive');
        
        $filters = array();

        if (Arr::get($_GET, 'action')) {
            if ($_GET['action'] == 2 && !Group::current('is_admin')) {
                $show = array_keys(Columns::get_visible());
                $filters['fields'] = array(
                    '$in' => $show,
                );
            }            
            
            $filters['update_type'] = intval($_GET['action']);
        }
            
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
        
        $result = $archive->find($filters)->sort(array('job_key' => 1, 'update_time' => -1));
        
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
            
            foreach (Columns::get_static() as $static => $show)
                $row['static'][$static] = Arr::path($jobs, array($row['job_key'], $static));
                            
            $items[] = $row;
        }

        return $items;
    }
    
    public function action_index() {
        
        $view = View::factory('Jobs/Reports');
        
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
        header('Content-disposition: filename=Export.csv');
        
        $result = $this->get_results($id == 'all');
        
        $reports = array();
        foreach (Columns::get_static() as $column => $value)
            $reports[$column] = Columns::get_name($column);
        
        $file = tmpfile();

        $actions = array(
            '1' => 'Created',
            '2' => 'Updated',
            '3' => 'Removed',
        );
        
        $data = array(
            'Ticket ID',
            'Date',
            'Action',
            'File name',
        );
        foreach ($reports as $id => $name) $data[] = $name;
        $data[] = "Column name";
        $data[] = "Old value";
        $data[] = "New value";

        fputcsv($file, $data);
        
        foreach ($result as $ticket) {
            $ticket['update_time'] = date('d-m-Y H:i', $ticket['update_time']);
            $ticket['type'] = Arr::get($actions, $ticket['update_type'], 'Unknown');

            $data = array(
                $ticket['job_key'],
                $ticket['update_time'],
                $ticket['type'],
                $ticket['filename'],
            );
            foreach ($reports as $id => $name) $data[] = Arr::path($ticket, 'static.' . $id);


            if ($ticket['update_type'] == 2) {
                if ($ticket['data']) {
                    $base = $data;
                    foreach ($ticket['data'] as $key => $value) {
                        $data = $base;
                        $date = Columns::get_type($key) == 'date';
                        $data[] = Columns::get_name($key);
                        $data[] = $date && $value['old_value'] ? date('d-m-Y H:i', $value['old_value']) : $value['old_value'];
                        $data[] = $date && $value['new_value'] ? date('d-m-Y H:i', $value['new_value']) : $value['new_value'];
                        fputcsv($file, $data);
                    }
                } else {
                    $data[] = "Non-relevant";
                    fputcsv($file, $data);
                }
            } else {
                $data[] = "N/A";
                fputcsv($file, $data);
            }
        }
        
        rewind($file);
        
        fpassthru($file);
        
        die();
    }
    
    public function action_uploads() {
        $id = $this->request->param('id');
        
        Pager::$count = DB::select(DB::expr('COUNT(*) as cnt'))->from('upload_log')->where('job_id', '=', $id)->execute()->get('cnt');
        
        $result = DB::select()
            ->from('upload_log')
            ->where('job_id', '=', $id)
            ->order_by('uploaded', 'desc')
            ->offset(Pager::offset())
            ->limit(Pager::limit())
            ->execute()->as_array();
            
        $users = array();
        foreach ($result as $log)
            $users[$log['user_id']] = 1;
            
        User::get(array_keys($users));
        
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        
        $view = View::factory('Jobs/UploadLog')
            ->bind('logs', $result)
            ->bind('companies', $companies);
            
        $this->response->body($view);
    }
    
    public function action_files() {
        $id = $this->request->param('id');
        
        $id = preg_replace('/[^a-z0-9_]/i', '', $id);
        
        $result = Database_Mongo::collection('archive')
            ->aggregate(array(
                array('$match' => array('filename' => array('$regex' => '.*' . $id . '.*', '$options' => 'i'))),
                array('$group' => array('_id' => '$filename')),
                array('$sort' => array('_id' => 1)),
                array('$limit' => 10),
            ));
            
        $files = array();
        foreach ($result['result'] as $row)
            $files[] = $row['_id'];
         
        die(json_encode($files));
    }
    
    public function action_tickets() {
        $id = $this->request->param('id');
        
        $id = preg_replace('/[^a-z0-9]/i', '', $id);
        
        $result = Database_Mongo::collection('archive')
            ->aggregate(array(
                array('$match' => array('job_key' => array('$regex' => '.*' . $id . '.*', '$options' => 'i'))),
                array('$group' => array('_id' => '$job_key')),
                array('$sort' => array('_id' => 1)),
                array('$limit' => 10),
            ));
            
        $tickets = array();
        foreach ($result['result'] as $row)
            $tickets[] = $row['_id'];
         
        die(json_encode($tickets));
    }
}
