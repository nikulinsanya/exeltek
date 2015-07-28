<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Assign extends Controller {

    public function action_index() {
        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403('Forbidden');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        $type = intval(Arr::get($_POST, 'type'));
        $company = intval(Arr::get($_POST, 'company'));

        if (!$ids)
            Messages::save('Please, select at least one job!');
        elseif (!$type)
            Messages::save('Please, select works type!');
        elseif (!$company)
            Messages::save('Please, select company!');
        elseif (DB::select('id')->from('job_types')->where('id', '=', $type)->execute()->get('id') &&
            ($company == -1 || DB::select('id')->from('companies')->where('id', '=', $company)->execute()->get('id')))  {

            $jobs = Database_Mongo::collection('jobs');
            $result = $jobs->find(array('_id' => array('$in' => $ids)));
            $count = 0;
            $values = array();
            $users = array();
            while ($job = $result->next()) if (Arr::path($job, 'assigned.' . $type) != $company) {
                $update = array();
                if (Arr::path($job, 'assigned.' . $type)) {
                    $users = DB::select('id')->from('users')->where('company_id', '=', $job['assigned'][$type])->execute()->as_array(NULL, 'id');

                    $subs = Database_Mongo::collection('submissions')->findOne(array('job_key' => $job['_id'], 'active' => 1, 'user_id' => array('$in' => $users)));

                    if ($subs) {
                        continue;
                    } else {
                        $subs = Database_Mongo::collection('submissions')->find(array(
                            'job_key' => $job['_id'],
                            'active' => -1,
                        ))->sort(array('process_time' => 1));

                        $list = array();
                        foreach ($subs as $sub) $list[$sub['key']] = $sub['_id'];

                        $financial = DB::select('id')->from('job_columns')->where('financial', '>', 0)->execute()->as_array(NULL, 'id');

                        $res = array();
                        foreach ($financial as $column)
                            if (isset($list['data.' . $column]))
                                $res[$column] = $list['data.' . $column];

                        Database_Mongo::collection('submissions')->update(
                            array('_id' => array('$in' => array_values($res))),
                            array('$set' => array('financial_time' => 0)),
                            array('multiple' => 1)
                        );

                        if (!in_array($job['assigned'][$type], Arr::get($job, 'ex', array()), true)) {
                            $job['ex'][] = intval($job['assigned'][$type]);
                            $update['$set']['ex'] = $job['ex'];
                        }
                    }
                }

                if ($company == -1)
                    unset($job['assigned'][$type]);
                else
                    $job['assigned'][$type] = $company;

                if (Arr::get($job, 'assigned'))
                    $update['$set']['assigned'] = $job['assigned'];
                else
                    $update['$unset']['assigned'] = 1;
                $update['$set']['last_update'] = time();

                $companies = array();
                if (Arr::get($job, 'assigned'))
                    foreach ($job['assigned'] as $cmp) if ($cmp) $companies[$cmp] = 1;

                $update['$set']['companies'] = array_keys($companies);
                $status = Arr::get($job, 'status', Enums::STATUS_UNALLOC);
                if ($companies && $status == Enums::STATUS_UNALLOC) {
                    $update['$set']['status'] = Enums::STATUS_ALLOC;
                } elseif (!$companies && $status == Enums::STATUS_ALLOC) {
                    $update['$unset']['status'] = 1;
                } else $update['$set']['status'] = Enums::STATUS_COMPLETE;

                $count++;

                $jobs->update(array('_id' => $job['_id']), $update);

                $values[] = array(
                    'time' => time(),
                    'user_id' => User::current('id'),
                    'company_id' => max($company, 0),
                    'job_id' => $job['_id'],
                    'type' => $type,
                );
            }

            if ($values) {
                $insert = DB::insert('assign_log', array_keys($values[0]));
                foreach ($values as $value)
                    $insert->values($value);

                $insert->execute();
            }

            if ($company == -1)
                Messages::save($count . ' jobs were succesfully unassigned', 'success');
            else {
                Messages::save($count . ' jobs were succesfully assigned to ' . Company::get($company, 'name'), 'success');

                $users = DB::select('id')->from('users')->where('company_id', '=', $company)->execute()->as_array(NULL, 'id');

                $message = $count . ' tickets were allocated on ' . date('d-m-Y H:i');
                $insert = DB::insert('notifications', array('user_id', 'message'));

                foreach ($users as $user)
                    $insert->values(array($user, $message));
                $insert->execute();
            }
        }

        $this->redirect('/search');
    }

}