<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Submissions extends Controller {
    
    public function before() {
        parent::before();

        if (!Group::current('allow_submissions'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index() {
        
        $id = strval(Arr::get($_GET, 'ticket'));
        
        if (!$id) throw new HTTP_Exception_404('Not found');

        $filter = array('_id' => $id);

        if (!Group::current('allow_assign'))
            $filter['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );

        $job = Database_Mongo::collection('jobs')->findOne($filter);

        if (!$job) throw new HTTP_Exception_404('Not found');

        $filter = array('job_key' => $id);
        
        if (!Group::current('allow_assign'))
            $filter['user_id'] = array('$in' => DB::select('id')->from('users')->where('company_id', '=', User::current('company_id'))->execute()->as_array(NULL, 'id'));
        
        $result = Database_Mongo::collection('submissions')
            ->find($filter)->sort(array('update_time' => -1));
            
        Pager::$count = $result->count(true);
        $result->limit(Pager::limit())->skip(Pager::offset());
        
        $submissions = array();
        $users = array();
        while ($row = $result->next()) {
            $users[$row['user_id']] = 1;
            $submissions[] = $row;
        }
        
        if ($users) User::get(array_keys($users));
        
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        
        $view = View::factory('Jobs/Submissions');

        $view->bind('submissions', $submissions)
            ->bind('companies', $companies)
            ->bind('job', $job);

        $this->response->body($view);
    }

    public function action_approve() {
        if (!Group::current('allow_assign')) throw new HTTP_Exception_403('Forbidden');

        $id = Arr::get($_GET, 'id');

        $submission = Database_Mongo::collection('submissions')->findOne(array(
            '_id' => new MongoId($id),
        ));

        if (!$submission || Arr::get($submission, 'active')) throw new HTTP_Exception_404('Not found');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $submission['job_key']));

        $value = Arr::get($job, $submission['key'], '');

        if ($value != $submission['value']) {
            $archive = array(
                'data' => array(
                    substr($submission['key'], 5) => array(
                        'old_value' => $value,
                        'new_value' => $submission['value'],
                    ),
                ),
                'fields' => substr($submission['key'], 5),
                'job_key' => $job['_id'],
                'user_id' => User::current('id'),
                'update_time' => time(),
                'update_type' => 2,
                'filename' => 'MANUAL',
            );

            $update = array('$set' => array(
                'last_update' => time(),
            ));
            if ($submission['value'])
                $update['$set'][$submission['key']] = $submission['value'];
            else
                $update['$unset'][$submission['key']] = 1;

            $company = intval(User::get($submission['user_id'], 'company_id'));

            $sub = array('$set' => array('admin_id' => User::current('id'), 'process_time' => time(), 'active' => -1));

            $financial = floatval(DB::select('financial')->from('job_columns')->where('id', '=', substr($submission['key'], 5))->execute()->get('financial'));

            if ($financial && !in_array($company, Arr::get($job, 'companies', array()), true))
                $sub['$set']['financial_time'] = 0;

            Database_Mongo::collection('archive')->insert($archive);
            Database_Mongo::collection('jobs')->update(array('_id' => $job['_id']), $update);
            Database_Mongo::collection('submissions')->update(array('_id' => new MongoId($id)), $sub);
        }

        die(json_encode(array(
            'success' => true,
        )));
    }
    
}