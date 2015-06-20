<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Reports_Financial extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('allow_finance')) throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $start = Arr::get($_GET, 'start') ? strtotime($_GET['start']) : (strtotime('this week', strtotime('this week') > time() ? strtotime('yesterday') : time()));
        $end = Arr::get($_GET, 'end') ? strtotime($_GET['end']) + 86399 : time();

        $query = array(
            'financial_time' => isset($_GET['excel']) ? array('$gt' => 0) : array('$exists' => 1),
            'update_time' => array('$gt' => intval($start), '$lt' => intval($end)),
        );

        if (Arr::get($_GET, 'app-start'))
            $query['process_time']['$gt'] = strtotime($_GET['app-start']);

        if (Arr::get($_GET, 'app-end'))
            $query['process_time']['$lt'] = strtotime($_GET['app-end']) + 86399;

        if (Arr::get($_GET, 'fin-start'))
            $query['financial_time']['$gt'] = strtotime($_GET['fin-start']);

        if (Arr::get($_GET, 'fin-end'))
            $query['financial_time']['$lt'] = strtotime($_GET['fin-end']) + 86399;

        if (Group::current('allow_assign'))
            $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');

        if (!Group::current('allow_assign') || Arr::get($_GET, 'company'))
            $query['user_id'] = array('$in' => DB::select('id')->from('users')->where('company_id', '=', Group::current('allow_assign') ? $_GET['company'] : User::current('company_id'))->execute()->as_array(NULL, 'id'));

        $result = Database_Mongo::collection('submissions')->find($query)->sort(array('job_key' => 1, 'update_time' => -1));

        $submissions = array();
        $users = array();

        $jobs = array();
        $keys = array();

        foreach ($result as $submission) {
            $jobs[$submission['job_key']] = 1;
            $keys[$submission['key']] = 1;
            $submissions[$submission['job_key']][] = $submission;
            $users[$submission['user_id']] = 1;
        }

        $result = Database_Mongo::collection('jobs')->find(array('_id' => array('$in' => array_keys($jobs))), $keys);
        $jobs = array();
        foreach ($result as $job) {
            $jobs[$job['_id']] = $job;
        }

        if ($users) User::get(array_keys($users));

        if (isset($_GET['export'])) {
            header('Content-type: text/csv');
            header('Content-disposition: filename="Submissions.' . date('Ymd', $start) . '-' . date('Ymd', $end) . '.' . date('YmdHi', time()) . '.csv"');
            $file = tmpfile();
            $headers = array(
                'Tickets ID',
                'Submission Date',
                'Approval Date',
                'User',
            );
            if (Group::current('allow_assign')) $headers[] = 'Company';
            $headers[] = 'Column';
            $headers[] = 'Value';

            $keys = array();

            fputcsv($file, $headers);
            foreach ($submissions as $job => $list)
                foreach ($list as $submission) {
                    $keys[$submission['key']] = 1;
                    $key = substr($submission['key'], 5);
                    $data = array(
                        $job,
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
        } elseif (isset($_GET['excel'])) {
            $doc = PHPExcel_IOFactory::load(DOCROOT . 'financial.template.xlsx');
            $sheet = $doc->getSheet();

            $s = date('M Y', $start);
            $e = date('M Y', $end);
            if ($s == $e) $month = $s; else $month = $s . ' - ' . $e;

            $map = array(
                'G' => '190',
                'H' => '191',
                'I' => '192',
                'J' => '193',
                'K' => '194',
                'L' => '195',
                'M' => '196',
                'N' => '197',
                'O' => '198',
                'P' => '199',
                'Q' => '200',
                'R' => '201',
                'S' => '202',
                'T' => '203',
                'U' => '204',
                'V' => '205',
                'W' => '206',
                'X' => '207',
                'Y' => '208',
                'Z' => '43',
                'AA' => '209',
            );

            $i = 10;
            $total = array();
            $amount = array();
            $users = array();

            foreach ($submissions as $job => $list) {
                $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $job));
                $data = Arr::get($job, 'data');
                $inst_min = false;
                $inst_max = false;
                $app_min = false;
                $app_max = false;

                $result = array();
                $rate = array();

                foreach ($list as $submission) {
                    $users[$submission['user_id']] = 1;
                    $inst = $submission['update_time'];
                    if ($inst_min === false || $inst_min > $inst) $inst_min = $inst;
                    if ($inst_max === false || $inst_max < $inst) $inst_max = $inst;

                    $app = Arr::get($submission, 'process_time', false);
                    if ($app_min === false && $app !== false || $app_min > $app) $app_min = $app;
                    if ($app_max === false && $app !== false || $app_max < $app) $app_max = $app;

                    $key = substr($submission['key'], 5);

                    $result[$key] = $submission['paid'];
                    $rate[$key] = $submission['rate'];
                }

                $sum = 0;
                foreach ($result as $key => $value) {
                    $sum += $value * $rate[$key];
                    $total[$key] = Arr::get($total, $key, 0) + $value * $rate[$key];
                    $amount[$key] = Arr::get($amount, $key, 0) + $value;
                }

                $i++;
                $inst_min = date('d-m-Y', $inst_min);
                $inst_max = date('d-m-Y', $inst_max);
                $app_min = date('d-m-Y', $app_min);
                $app_max = date('d-m-Y', $app_max);

                $sheet->setCellValue('B' . $i, $inst_min == $inst_max ? $inst_min : $inst_min . ' - ' . $inst_max)
                    ->setCellValue('C' . $i, $app_min == $app_max ? $app_min : $app_min . ' - ' . $app_max)
                    ->setCellValue('D' . $i, $job['_id'])
                    ->setCellValue('E' . $i, Arr::get($data, 8))
                    ->setCellValue('F' . $i, Arr::get($data, 14))
                    ->setCellValue('AC' . $i, $sum);

                foreach ($map as $key => $value)
                    $sheet->setCellValue($key . $i, Arr::get($result, $value) ? $result[$value] : '');
            }

            foreach ($map as $key => $value)
                $sheet->setCellValue($key . '10', Arr::get($amount, $value) ? $total[$value] / $amount[$value] : '');

            if ($users)
                $companies = DB::select('name')
                    ->from('companies')
                    ->where('id', 'IN', DB::select('company_id')->distinct(true)->from('users')->where('id', 'IN', array_keys($users)))
                    ->execute()->as_array(NULL, 'name');
            else
                $companies = array('None');

            $sheet->setCellValue('D2', implode(', ', $companies))
                ->setCellValue('D3', $month);

            header('Content-type: application/xlsx');
            header('Content-disposition: filename="Report.xlsx"');
            $name = tempnam(sys_get_temp_dir(), 'excel');
            $writer = new PHPExcel_Writer_Excel2007($doc);
            $writer->setPreCalculateFormulas(true);
            $writer->save($name);
            readfile($name);
            unlink($name);
            die();
        }

        $columns = DB::select('id', 'financial')->from('job_columns')->where('financial', '>', 0)->execute()->as_array('id', 'financial');

        $rates = array();

        $result = DB::select('company_id', 'column_id', 'rate')->from('rates')->execute();

        foreach ($result as $row)
            $rates[$row['company_id']][$row['column_id']] = $row['rate'];

        $discrepancies = array();
        foreach ($submissions as $job => $list) {
            $fl = false;
            foreach ($list as $submission)
                if (Arr::path($jobs, $job . '.' . $submission['key']) != $submission['value']) {
                    $fl = true;
                    break;
                }

            if (!$fl) $discrepancies[$job] = 1;
        }

        $view = View::factory("Reports/Financial")
            ->bind('companies', $companies)
            ->bind('submissions', $submissions)
            ->bind('discrepancies', $discrepancies)
            ->bind('columns', $columns)
            ->bind('jobs', $jobs)
            ->bind('rates', $rates);

        $this->response->body($view);
    }
    
    public function action_approve() {
        if (!Group::current('allow_assign')) throw new HTTP_Exception_403('Forbidden');
        
        $id = new MongoId(strval(Arr::get($_GET, 'id')));
        $value = floatval(Arr::get($_GET, 'value'));
        $result = Database_Mongo::collection('submissions')->findOne(array('_id' => $id));
        
        if (!$result) throw new HTTP_Exception_404('Not found');
        
        $key = substr($result['key'], 5);
        $max = DB::select('financial')->from('job_columns')->where('id', '=', $key)->execute()->get('financial');
        
        if ($value > $max) $value = $max;
        
        $company = User::get($result['user_id'], 'company_id');
        $rate = DB::select('rate')->from('rates')->where('company_id', '=', $company)->and_where('column_id', '=', $key)->execute()->get('rate');
        
        if (!$rate) throw new HTTP_Exception_403('Forbidden');
        
        $time = time();
        
        $update = array('$set' => array(
            'financial_time' => $time,
            'paid' => $value,
            'rate' => $rate,
        ));
        
        Database_Mongo::collection('submissions')->update(array('_id' => $id), $update);

        die(json_encode(array('success' => true, 'rate' => $rate, 'value' => $value, 'time' => date('d-m-Y H:i', $time))));
    }
}
