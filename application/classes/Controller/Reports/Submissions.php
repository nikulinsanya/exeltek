<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Reports_Submissions extends Controller {

    public function action_index()
    {
        $start = Arr::get($_GET, 'start') ? strtotime($_GET['start']) : (strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time()));
        $end = Arr::get($_GET, 'end') ? strtotime($_GET['end']) + 86399: time();
        
        $query = array(
            'update_time' => array('$gt' => intval($start), '$lt' => intval($end)),
        );
        
        if (Arr::get($_GET, 'app-start'))
            $query['process_time']['$gt'] = strtotime($_GET['app-start']);
            
        if (Arr::get($_GET, 'app-end'))
            $query['process_time']['$lt'] = strtotime($_GET['app-end']) + 86399;
        
        if (Group::current('allow_assign'))
            $companies = DB::select('id', 'name')->from('companies')->order_by('name', 'asc')->execute()->as_array('id', 'name');
        
        if (!Group::current('allow_assign') || Arr::get($_GET, 'company'))
            $query['user_id'] = array('$in' => DB::select('id')->from('users')->where('company_id', '=', Group::current('allow_assign') ? $_GET['company'] : User::current('company_id'))->execute()->as_array(NULL, 'id'));

        if (Arr::get($_GET, 'finished')) {
            $keys = Database_Mongo::collection('submissions')->distinct('job_key', $query);
            $keys = Database_Mongo::collection('jobs')->distinct('_id', array('data.245' => $_GET['finished'], '_id' => array('$in' => $keys)));
            $query['job_key'] = array('$in' => $keys);
        }

        if (Arr::get($_GET, 'ticket')) {
            $tickets = explode(',', $_GET['ticket']);
            $q = array();
            foreach ($tickets as $ticket) {
                $ticket = preg_replace('/[^a-z0-9]/i', '', strval($ticket));
                if (!$ticket) continue;
                if (preg_match('/^T1W[0-9]{12}$/', $ticket))
                    $q[] = $ticket;
                else
                    $q[] = new MongoRegex('/.*' . $ticket . '.*/i');
            }
            if (count($q) > 1)
                $query['job_key'] = array('$in' => $q);
            elseif ($q)
                $query['job_key'] = $q[0];
        }

        if (Arr::get($_GET, 'fsa')) {
            $values = is_array($_GET['fsa']) ? $_GET['fsa'] : explode(',', $_GET['fsa']);
            $query['data.12'] = count($values) > 1 ? array('$in' => array_values($values)) : current($values);
        }

        if (Arr::get($_GET, 'fsam')) {
            $values = is_array($_GET['fsam']) ? $_GET['fsam'] : explode(',', $_GET['fsam']);
            $query['data.13'] = count($values) > 1 ? array('$in' => array_values($values)) : current($values);
        }

        if (Arr::get($_GET, 'fda')) {
            $values = is_array($_GET['fda']) ? $_GET['fda'] : explode(',', $_GET['fda']);
            $query['data.14'] = count($values) > 1 ? array('$in' => array_values($values)) : current($values);
        }

        if (Arr::get($_GET, 'address'))
            $query['data.8'] = new MongoRegex('/.*' . strval($_GET['address']) . '.*/i');

        $sort = array('job_key' => 1);

        if (!Arr::get($_GET, 'sort'))
            $_GET['sort'] = array('-submission');

        foreach ($_GET['sort'] as $s) {
            $dir = substr($s, 0, 1) == '-' ? -1 : 1;
            $s = substr($s, 1);
            switch ($s) {
                case 'submission':
                    $sort['update_time'] = $dir;
                    break;
                case 'approval':
                    $sort['process_time'] = $dir;
                    break;
            }
        }

        $result = Database_Mongo::collection('submissions')->find($query)->sort($sort);
        
        $submissions = array();
        $users = array();
        
        foreach ($result as $submission) {
            $submissions[$submission['job_key']][] = $submission;
            $users[$submission['user_id']] = 1;
        }
        
        if ($users) User::get(array_keys($users));
        
        if (isset($_GET['export'])) {
            header('Content-type: text/csv');
            header('Content-disposition: filename="Submissions.' . date('Ymd', $start) . '-' . date('Ymd', $end) . '.' . date('YmdHi', time()) . '.csv"');
            $file = tmpfile();
            $headers = array(
                'Tickets ID',
                'FDA ID',
                'LOC ID',
                'Address',
                'Submission Date',
                'Approval Date',
                'User',
            );
            if (Group::current('allow_assign')) $headers[] = 'Company';
            $headers[] = 'Column';
            $headers[] = 'Value';

            fputcsv($file, $headers);
            $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($submissions))), array('data.8' => 1, 'data.9' => 1, 'data.14' => 1));
            $jobs = array();
            foreach ($result as $job) {
                $jobs[$job['_id']] = array(
                    'a' => Arr::path($job, 'data.8', ''),
                    'f' => Arr::path($job, 'data.14', ''),
                    'l' => Arr::path($job, 'data.9', ''),
                );
            }

            foreach ($submissions as $job => $list)
                foreach ($list as $submission) {
                    $key = substr($submission['key'], 5);
                    $data = array(
                        $job,
                        Arr::path($jobs, array($job, 'f')),
                        Arr::path($jobs, array($job, 'l')),
                        Arr::path($jobs, array($job, 'a')),
                        date('d-m-Y H:i', $submission['update_time']),
                        Arr::get($submission, 'process_time') ? date('d-m-Y H:i', $submission['process_time']) : '',
                        User::get($submission['user_id'], 'login'),
                    );
                    if (Group::current('allow_assign')) $data[] = Arr::get($companies, User::get($submission['user_id'], 'company_id'), 'Unknown');
                    $data[] = Columns::get_name($key);
                    $data[] = Columns::output($submission['value'], Columns::get_type($key), true);

                    fputcsv($file, $data);
                }
            fseek($file, 0);
            fpassthru($file);
            fclose($file);
            die();
        } elseif (isset($_GET['export2'])) {
            //header('Content-type: text/plain');
            header('Content-type: text/csv');
            header('Content-disposition: filename="Submissions.' . date('Ymd', $start) . '-' . date('Ymd', $end) . '.' . date('YmdHi', time()) . '.csv"');
            $result = array();
            $columns = array();
            foreach ($submissions as $job => $list) foreach ($list as $submission) {
                $key = substr($submission['key'], 5);
                $result[$job][$submission['update_time']][$submission['user_id']][$key] = $submission['value'];
                $columns[$key] = 1;
            }
            $submissions = $result;

            $columns = array_keys($columns);
            sort($columns);

            $headers = array(
                'Tickets ID',
                'FDA ID',
                'LOC ID',
                'Address',
                'Submission Date',
                'User',
            );
            if (Group::current('allow_assign')) $headers[] = 'Company';
            foreach ($columns as $column) $headers[] = Columns::get_name($column);

            $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($submissions))), array('data.8' => 1, 'data.9' => 1, 'data.14' => 1));
            $jobs = array();
            foreach ($result as $job) {
                $jobs[$job['_id']] = array(
                    'f' => Arr::path($job, 'data.14', ''),
                    'l' => Arr::path($job, 'data.9', ''),
                    'a' => Arr::path($job, 'data.8', ''),
                );
            }

            $file = tmpfile();
            fputcsv($file, $headers);
            foreach ($submissions as $job => $list) foreach ($list as $time => $values) foreach ($values as $user => $submission) {
                $row = array(
                    $job,
                    Arr::path($jobs, $job . '.f'),
                    Arr::path($jobs, $job . '.l'),
                    Arr::path($jobs, $job . '.a'),
                    date('d-m-Y H:i', $time),
                    User::get($user, 'login'),
                );
                if (Group::current('allow_assign')) $row[] = Arr::get($companies, User::get($user, 'company_id'));
                foreach ($columns as $column) $row[] = Columns::output(Arr::get($submission, $column, ''), Columns::get_type($column), true);
                fputcsv($file, $row);
            }
            fseek($file, 0);
            fpassthru($file);
            fclose($file);

            //print_r($result);
            die();
        }
        
        $view = View::factory("Reports/Submissions")
            ->bind('companies', $companies)
            ->bind('submissions', $submissions);
        
        $this->response->body($view);
    }
}
