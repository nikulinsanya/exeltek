<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Payment extends Controller
{
    public function before() {
        parent::before();

        if (!Group::current('allow_finance') || !Group::current('show_all_jobs'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index()
    {
        $ids = array_keys(Arr::get($_POST, 'job', array()));
        $ids = array_filter($ids, function($st) { return preg_match('/^T1W[0-9]{12}$/i', $st);});
        if (!$ids)
            throw new HTTP_Exception_404('Not found');

        $users = DB::select('id', 'company_id')->from('users')->execute()->as_array('id', 'company_id');
        $companies = array();
        $result = Database_Mongo::collection('submissions')->find(array('job_key' => array('$in' => $ids), 'financial_time' => array('$gt' => 0)), array('user_id' => 1, 'job_key' => 1, 'paid' => 1, 'rate' => 1));
        foreach ($result as $submission) if (Arr::get($users, $submission['user_id'])) {
            $path = array($users[$submission['user_id']], $submission['job_key']);
            Arr::set_path($companies, $path, Arr::path($companies, $path, 0) + $submission['paid'] * $submission['rate']);
        }

        $paid = array();
        if ($companies) {
            $result = DB::select('job_key', 'company_id')->from('payment_jobs')->join('payments')->on('payment_id', '=', 'id')->where('job_key', 'IN', $ids)->execute()->as_array();
            foreach ($result as $payment)
                $paid[$payment['company_id']][$payment['job_key']] = 1;
        }

        $jobs = array_fill_keys($ids, array('c' => array(), 'p' => array()));

        foreach ($companies as $company => $list)
            foreach ($list as $key => $value)
                $jobs[$key]['c'][$company] = $value;

        foreach ($paid as $company => $list)
            foreach ($list as $key => $dummy)
                $jobs[$key]['p'][$company] = 1;

        if (Arr::get($_POST, 'company') && Arr::get($_POST, 'amount')) {
            $company = Arr::get($companies, $_POST['company']);
            if (!$company) {
                die(json_encode(array('success' => false)));
            }
            $payment = array(
                'company_id' => intval($_POST['company']),
                'admin_id' => User::current('id'),
                'payment_time' => time(),
                'amount' => floatval($_POST['amount']),
            );
            Database::instance()->begin();
            $id = Arr::get(DB::insert('payments', array_keys($payment))->values(array_values($payment))->execute(), 0);
            $query = DB::insert('payment_jobs', array('payment_id', 'job_key'));
            $partial = array();
            $paid = array();
            foreach ($company as $job => $value) {
                $diff = array_diff_key($jobs[$job]['c'], $jobs[$job]['p']);
                if (count($diff) == 0 || (count($diff) == 1 && isset($diff[$_POST['company']])))
                    $paid[] = $job;
                else
                    $partial[] = $job;

                $query->values(array($id, $job));
            }
            $query->execute();
            if ($partial)
                Database_Mongo::collection('jobs')->update(array('_id' => array('$in' => $partial)), array('$unset' => array('paid' => 1), '$set' => array('partial' => 1)), array('multiple' => 1));
            if ($paid)
                Database_Mongo::collection('jobs')->update(array('_id' => array('$in' => $paid)), array('$unset' => array('partial' => 1), '$set' => array('paid' => 1)), array('multiple' => 1));
            Database::instance()->commit();
            Messages::save('Payment successfully added!', 'success');
            die(json_encode(array('success' => true)));
        }

        foreach ($jobs as $key => $list)
            $jobs[$key]['p'] = array_keys($list['p']);

        ksort($jobs);

        $companies = DB::select('id', 'name')->from('companies')->where('id', 'IN', array_keys($companies))->order_by('name', 'ASC')->execute()->as_array('id', 'name');

        $view = View::factory('Jobs/Payment')
            ->bind('jobs', $jobs)
            ->bind('companies', $companies);

        $this->response->body($view);
    }
}