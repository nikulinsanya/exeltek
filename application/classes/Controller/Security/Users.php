<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Users extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $result = DB::select()->from('user_regions')->execute()->as_array();
        $regs = array();
        foreach ($result as $region)
            $regs[$region['user_id']][] = $region['region_id'];
            
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');
        
        $list = DB::select('users.*', 'is_admin', array('groups.name', 'group'), array('companies.name', 'company'))
            ->from('users')
            ->join('groups', 'left')->on('groups.id', '=', 'group_id')
            ->join('companies', 'left')->on('companies.id', '=', 'company_id')
            ->order_by('company', 'asc')
            ->order_by('group', 'asc')
            ->order_by('login', 'asc')
            ->execute()->as_array();
        
        $view = View::factory('Security/Users')
            ->bind('regs', $regs)
            ->bind('regions', $regions)
            ->bind('list', $list);
        $this->response->body($view);
    }
    
    public function action_edit() {
        $id = $this->request->param('id');
        
        $form = new Form();
        
        $groups = DB::select('id', 'name')->from('groups')->execute()->as_array('id', 'name');
        $partners = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');
        
        $form->add("login", 'Login', Form::STRING, '', array('not_empty', 'min_length' => array(':value', 4)))
            ->add("email", 'E-Mail', Form::STRING, '', array('not_empty', 'email'))
            ->add('group_id', 'Group', Form::SELECT, array ('' => 'Please select...') + $groups, array('not_empty'), array('class'=>'multiselect'))
            ->add('company_id', 'Partner', Form::SELECT, array('' => 'None') + $partners, null, array('class'=>'multiselect'))
            ->add('default_region', 'Default region', Form::SELECT, array(0 => 'None') + $regions, null , array('class'=>'multiselect'));
            
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        $form->add('region[]', 'Available regions', Form::SELECT, $regions, null, array('multiple'=>'multiple','class'=>'multiselect'));
        $form->add('passw', 'Password', Form::PASSWORD, '', $id ? false : array('not_empty', 'min_length' => array(':value', 6)))
             ->add('pass2', 'Confirm password', Form::PASSWORD, '', array('matches' => array(':validation', 'pass2', 'passw')));

        $item = $id ? User::get($id) : array();
        
        $form->values($item);
        
        $error = false;
        
        if ($_POST) {
            $item = $form->filter($_POST);
            
            if (!$form->validate($item)) {
                unset($item['pass2']);
                $exists = DB::select('id')
                    ->from('users')
                    ->where_open()
                        ->where('login', '=', $item['login'])
                        ->or_where('email', '=', $item['email'])
                    ->where_close()
                    ->and_where('id', '<>', $id)
                    ->execute()->get('id');
                    
                if ($exists)
                    Messages::save("User with given login or email already exists! Please, enter different login/email!");
                else {
                    $regs = $item['region'];
                    unset($item['region']);

                    if ($id) {
                        if (!Arr::get($item, 'passw'))
                            unset($item['passw']);
                            
                        DB::update('users')->set($item)->where('id', '=', $id)->execute();
                        DB::delete('user_regions')->where('user_id', '=', $id)->execute();
                    } else {
                        $result = DB::insert('users', array_keys($item))->values(array_values($item))->execute();
                        $id = Arr::get($result, 0);
                    }
                    
                    if ($regs) {
                        $result = DB::insert('user_regions', array('user_id', 'region_id'));
                        
                        foreach ($regs as $reg)
                            $result->values(array($id, $reg));
                            
                        $result->execute();
                    }

                    Messages::save('User successfully saved!', 'success');
                    $this->redirect('/security/users');
                }
            }
            
            $form->values($item);
        }
        
        $this->response->body($form->render($error));
        
    }
    
    public function action_delete() {
        $id = $this->request->param('id');
        
        if (!Group::get(User::get($id, 'group_id'), 'is_admin')) {
            DB::delete('users')->where('id', '=', $id)->execute();
            Messages::save('User successfully deleted!', 'info');
        }
        
        $this->redirect('/security/users');
    }
}
