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
            ->bind('companies', $companies);

        $this->response->body($view);
    }
    
}