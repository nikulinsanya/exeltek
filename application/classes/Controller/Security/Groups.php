<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Groups extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $list = DB::select('groups.*', DB::expr('COUNT(`users`.`id`) as cnt'))
            ->from('groups')
            ->join('users', 'left')->on('groups.id', '=', 'group_id')
            ->group_by('groups.id')
            ->execute()->as_array();
        
        $view = View::factory('Security/Groups')
            ->bind('list', $list);
        $this->response->body($view);
    }
    
    public function action_edit() {
        $id = $this->request->param('id');
        
        $form = new Form();
        
        $form->add("name", 'Name', Form::STRING, '', array('not_empty'))
            ->add('is_admin', 'Administrative group', Form::BOOL)
            ->add('show_all_jobs', 'Show all jobs (unchecked - show only assigned jobs)', Form::BOOL)
            ->add('allow_assign', 'Allow assigning jobs', Form::BOOL)
            ->add('allow_reports', 'Allow tracking changes', Form::BOOL)
            ->add('allow_submissions', 'Allow tracking submissions', Form::BOOL)
            ->add('allow_finance', 'Financial reports', Form::BOOL)
            ->add('allow_forms', 'Forms submission', Form::BOOL)
            ->add('allow_quality', 'Quality Reports', Form::BOOL)
            ->add('time_machine', 'Time Machine', Form::BOOL);

        $form->add('columns', 'Show columns in job search', Form::INFO);
        
        foreach (Columns::$fixed as $key => $value)
            $form->add($key, $value, Form::BOOL);
        
        $item = $id ? Group::get($id) : array();
        
        if ($item) {
            $columns = explode(',', $item['columns']);
            foreach ($columns as $column)
                $item[$column] = 1;
                
            unset($item['columns']);
        }
            
        $form->values($item);
        
        if ($_POST) {
            $value = $form->filter($_POST);
            if ($value['is_admin']) {
                $value['show_all_jobs'] = 1;
                $value['allow_assign'] = 1;
                $value['allow_reports'] = 1;
                $value['allow_submissions'] = 1;
                $value['allow_finance'] = 1;
                $value['allow_forms'] = 0;
                $value['allow_quality'] = 1;
                $value['time_machine'] = 1;
                $value['columns'] = implode(',', array_keys(Columns::$fixed));
            } else {
                $columns = array();
                foreach (Columns::$fixed as $key => $name)
                    if (Arr::get($value, $key))
                        $columns[] = $key;
                        
                $value['columns'] = implode(',', $columns);
            }
            
            $value = array_diff_key($value, Columns::$fixed);
            
            if (!$form->validate($value)) {
                if ($id)
                    DB::update('groups')->set($value)->where('id', '=', $id)->execute();
                else {
                    $origin = Arr::get($_POST, 'permissions');
                    unset($_POST['permissions']);
                    $id = Arr::get(DB::insert('groups', array_keys($value))->values(array_values($value))->execute(), 0);
                    DB::query(Database::INSERT, 
                        DB::expr("INSERT INTO `group_columns` (`group_id`, `column_id`, `permissions`) 
                        (SELECT :id, `column_id`, `permissions` FROM `group_columns` WHERE `group_id` = :origin)")
                        ->param(':id', $id)->param(':origin', $origin)->compile())->execute();
                }
                    
                Messages::save('Group successfully saved!', 'success');
                $this->redirect('/security/groups');
            }
        }
        
        if (!$id) {
            $groups = DB::select('id', 'name')->from('groups')->execute()->as_array('id', 'name');
            $form->add('permissions', 'Copy permissions from group', Form::SELECT, $groups);
        }
        
        $this->response->body($form->render());
        
    }

    public function action_delete() {
        $id = $this->request->param('id');
        
        $users = DB::select('id')->from('users')->where('group_id', '=', $id)->limit(1)->execute()->get('id');
        if (!$users && !Group::get($id, 'is_admin')) {
            Database::instance()->begin();
            DB::delete('group_columns')->where('group_id', '=', $id)->execute();
            DB::delete('groups')->where('id', '=', $id)->execute();
            Database::instance()->commit();
            Messages::save('Group succesfully deleted!', 'info');
        }
        
        $this->redirect('/security/groups');
    }
}
