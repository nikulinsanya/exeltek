<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Assign extends Controller {
    
    public function before() {
        if (!Group::current('allow_reports'))
            throw new HTTP_Exception_403('Forbidden');
        parent::before();
    }
    
    public function action_index() {
        
        $id = strval(Arr::get($_GET, 'ticket'));
        
        if (!$id) throw new HTTP_Exception_404('Not found');
        
        Pager::$count = DB::select(DB::expr('COUNT(*) as cnt'))->from('assign_log')->where('job_id', '=', $id)->execute()->get('cnt');
        
        $result = DB::select()->from('assign_log')
            ->where('job_id', '=', $id)
            ->order_by('time', 'desc')
            ->offset(Pager::offset())
            ->limit(Pager::limit())
            ->execute()->as_array();
        
        $users = array();
        foreach ($result as $row){
            $users[$row['user_id']] = 1;
        }
        
        if ($users) User::get(array_keys($users));
        
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $types = DB::select('id', 'name')->from('job_types')->execute()->as_array('id', 'name');
        
        $view = View::factory('Jobs/Assign');

        $view->bind('list', $result)
            ->bind('types', $types)
            ->bind('companies', $companies);

        $this->response->body($view);
    }
    
}