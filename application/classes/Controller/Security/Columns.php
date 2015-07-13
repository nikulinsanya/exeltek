<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Columns extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $groups = DB::select('id', 'name')->from('groups')->where('is_admin', '=', 0)->execute()->as_array('id', 'name');
        
        $result = DB::select()->from('group_columns')->where('group_id', 'IN', array_keys($groups))->execute()->as_array();
        $permissions = array();
        $search = array();

        foreach ($result as $perm) {
            $permissions[$perm['group_id']][$perm['column_id']] = $perm['permissions'];
            $search[$perm['group_id']][$perm['column_id']] = $perm['search'];
        }
        
        $view = View::factory('Security/Columns')
            ->bind('groups', $groups)
            ->bind('search', $search)
            ->bind('permissions', $permissions);
        $this->response->body($view);
    }
    
    public function action_save() {
        $id = Arr::get($_GET, 'id');
        if (!DB::select('id')->from('job_columns')->where('id', '=', $id)->execute()->get('id'))
            throw new HTTP_Exception_403();
            
        $group = Arr::get($_GET, 'group');
        if (!Group::get($group))
            throw new HTTP_Exception_403();
            
        $state = intval(Arr::get($_GET, 'state'));
        DB::query(Database::INSERT,
            DB::expr("INSERT INTO `group_columns` (`group_id`, `column_id`, `permissions`) VALUES (:group, :id, :state) ON DUPLICATE KEY UPDATE `permissions` = :state",
                array(
                    ':group' => $group,
                    ':id' => $id,
                    ':state' => $state,
                )
            )->compile())->execute();
        
        die(json_encode(array('success' => true)));
    }
    
    public function action_search() {
        $id = Arr::get($_GET, 'id');
        if (!DB::select('id')->from('job_columns')->where('id', '=', $id)->execute()->get('id'))
            throw new HTTP_Exception_403();
            
        $group = Arr::get($_GET, 'group');
        if (!Group::get($group))
            throw new HTTP_Exception_403();
            
        $state = intval(Arr::get($_GET, 'state'));
        DB::query(Database::INSERT,
            DB::expr("INSERT INTO `group_columns` (`group_id`, `column_id`, `search`) VALUES (:group, :id, :state) ON DUPLICATE KEY UPDATE `search` = :state",
                array(
                    ':group' => $group,
                    ':id' => $id,
                    ':state' => $state,
                )
            )->compile())->execute();
        
        die(json_encode(array('success' => true)));
    }

    public function action_persistent() {
        $id = Arr::get($_GET, 'id');
        $state = intval(Arr::get($_GET, 'state'));

        DB::update('job_columns')->set(array('persistent' => $state))->where('id', '=', $id)->execute();
        die(json_encode(array('success' => true)));
    }
    
    public function action_show() {
        $id = Arr::get($_GET, 'id');
        $state = intval(Arr::get($_GET, 'state'));
        
        DB::update('job_columns')->set(array('show_reports' => $state))->where('id', '=', $id)->execute();
        die(json_encode(array('success' => true)));
    }
}
