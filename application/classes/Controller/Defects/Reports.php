<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Defects_Reports extends Controller {
    
    private function get_columns() {
        return DB::select('COLUMN_NAME', 'COLUMN_COMMENT')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', '=', 'defects')
            ->execute()->as_array('COLUMN_NAME', 'COLUMN_COMMENT');
    }
    
    private function get_results($hidden) {
        $filters = array();
        
        if (Arr::get($_GET, 'ticket')) $filters['ticket'] = $_GET['ticket'];
        if (Arr::get($_GET, 'file')) $filters['file'] = $_GET['file'];
        if (Arr::get($_GET, 'start')) $filters['start'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $filters['end'] = strtotime($_GET['end']);
        
        $count = DB::select(DB::expr("COUNT(id) as cnt"))->from('defects_archive');
        $result = DB::select()->from('defects_archive');
        
        if (isset($filters['ticket'])) {
            $count->where('Id', '=', $filters['ticket']);
            $result->where('Id', '=', $filters['ticket']);
        }
        if (isset($filters['file'])) {
            $count->where('filename', '=', $filters['file']);
            $result->where('filename', '=', $filters['file']);
        }
        if (isset($filters['start'])) {
            $count->where('update_time', '>', date('Y-m-d H:i:s', $filters['start']));
            $result->where('update_time', '>', date('Y-m-d H:i:s', $filters['start']));
        }
        if (isset($filters['end'])) {
            $count->where('update_time', '<', date('Y-m-d H:i:s', $filters['end']));
            $result->where('update_time', '<', date('Y-m-d H:i:s', $filters['end']));
        }
        
        if (Arr::get($_GET, 'action')) {
            $count->where('update_type', '=', $_GET['action']);
            $result->where('update_type', '=', $_GET['action']);
        }
            
        $count = $count->execute()->get('cnt');
        
        Pager::$count = $count;
        
        $result->offset(Pager::offset())->limit(Pager::limit())->order_by('Id', 'asc');
        
        $result = $result->execute()->as_array();
        
        $ids = array();
        foreach ($result as $id => $ticket) if ($ticket['update_type'] == 2)
            $ids[$ticket['Id']] = 1;
            
        if ($ids) {
            $res = DB::select('key', 'Id', 'update_time', 'update_type')
                ->from('defects_archive')
                ->where('Id', 'IN', array_keys($ids))
                ->execute();
            
            $history = array();
            while ($row = $res->current()) {
                if (isset($history[$row['Id']]))
                    $history[$row['Id']][] = $row;
                else
                    $history[$row['Id']] = array($row);
                
                $res->next();
            }
        
            $ids = array();

            foreach ($result as $id => $ticket) if ($ticket['update_type'] == 2 && isset($history[$ticket['Id']])) {
                $max_date = 0;
                $max_id = 0;
                foreach ($history[$ticket['Id']] as $item) if ($item['update_type'] < 3)
                    if ($item['update_time'] < $ticket['update_time'] && $item['update_time'] > $max_date) {
                        $max_date = $item['update_time'];
                        $max_id = $item['key'];
                    }
                    
                if ($max_id) $ids[$id] = $max_id;
            }
            
            $res = DB::select()
                ->from('defects_archive')
                ->where('key', 'IN', array_values($ids))
                ->execute()->as_array('key');
                
            foreach ($result as $id => $ticket) if ($ticket['update_type'] == 2) $result[$id]['diff'] = array();

            foreach ($ids as $id => $key) if (isset($res[$key])) {
                $old = $res[$key];
                
                $diff = array_diff_assoc($old, $result[$id]);
                unset($diff['key']);
                unset($diff['update_time']);
                unset($diff['update_type']);
                unset($diff['filename']);
                
                $result[$id]['diff'] = array();
                $result[$id]['hidden'] = array();
                foreach ($diff as $key => $value)
                    if (isset($hidden[$key]))
                        $result[$id]['hidden'][$key] = $value;
                    else
                        $result[$id]['diff'][$key] = $value;
            }

        }
        
        return $result;
    }
    
    public function action_index() {
        
        $hidden = json_decode(Cookie::get('hidden_fields'), true) ? : array();
        
        $view = View::factory('Defects/Reports');
        
        $result = $this->get_results($hidden);
        
        $columns = $this->get_columns();
        
        //print_r(array_shift($result));
        
        $view->bind('tickets', $result)
            ->bind('hidden', $hidden)
            ->bind('columns', $columns);

        $this->response->body($view);
    }
    
    public function action_export() {
        header('Content-type: text/csv');
        header('Content-disposition: filename=Export.csv');
        
        $hidden = json_decode(Cookie::get('hidden_fields'), true) ? : array();
        
        $result = $this->get_results($hidden);
        
        $columns = $this->get_columns();
        
        $file = tmpfile();

        $actions = array(
            '1' => 'Created',
            '2' => 'Updated',
            '3' => 'Removed',
        );
        
        $data = array(
            'Defect ID',
            'Date',
            'Action',
            'File name',
            'Additional information',
        );
        fputcsv($file, $data);
        
        foreach ($result as $ticket) {
            $data = array(
                $ticket['Id'],
                $ticket['update_time'],
                Arr::get($actions, $ticket['update_type']),
                $ticket['filename'],
            );
            if ($ticket['update_type'] == 2) {
                if ($ticket['diff']) {
                    $data[] = "Column name";
                    $data[] = "Old value";
                    $data[] = "New value";
                    fputcsv($file, $data);
                    foreach ($ticket['diff'] as $key => $value) {
                        $data = array('', '', '', '', Arr::get($columns, $key, $key), $value, Arr::get($ticket, $key, ''));
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
    
    public function action_hidden() {
        $hidden = json_decode(Cookie::get('hidden_fields'), true) ? : array();
        
        $columns = $this->get_columns();
        
        if ($_POST) {
            $hidden = array();
            foreach ($_POST as $key => $value)
                if (isset($columns[$key]))
                    $hidden[$key] = 1;
                    
            Cookie::set('hidden_fields', json_encode($hidden) ? : '', strtotime("+1 year"));
            
            $this->redirect($this->request->uri());
        }

        $view = View::factory('Hidden')
            ->bind('columns', $columns)
            ->bind('hidden', $hidden);
            
        $this->response->body($view);
    }
    
    public function action_fields() {
        $id = $this->request->param('id');
        
        $result = DB::select('COLUMN_NAME', 'COLUMN_COMMENT')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', '=', 'defects')
            ->and_where('COLUMN_NAME', 'LIKE', $id ? '%' . $id . '%' : '%')
            ->limit(10)
            ->execute()->as_array(NULL, 'COLUMN_NAME', 'COLUMN_COMMENT');
        
        die(json_encode($result));
    }
    
    public function action_files() {
        $id = $this->request->param('id');
        
        $files = DB::select('filename')
            ->distinct(true)
            ->from('defects_archive')
            ->where('filename', 'LIKE', '%' . $id . '%')
            ->order_by('filename', 'asc')
            ->offset(0)->limit(10)
            ->execute()->as_array(NULL, 'filename');
            
        die(json_encode($files));
    }
    
    public function action_tickets() {
        $id = $this->request->param('id');
        
        $tickets = DB::select('Id')
            ->distinct(true)
            ->from('defects_archive')
            ->where('Id', 'LIKE', '%' . $id . '%')
            ->order_by('Id', 'asc')
            ->offset(0)->limit(10)
            ->execute()->as_array(NULL, 'Id');
            
        die(json_encode($tickets));
    }
    
}
