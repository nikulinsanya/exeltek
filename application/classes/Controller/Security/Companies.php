<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Companies extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $list = DB::select('companies.*', DB::expr('COUNT(`users`.`id`) as cnt'), array('company_types.name', 'company_type'))
            ->from('companies')
            ->join('users', 'left')->on('companies.id', '=', 'company_id')
            ->join('company_types')->on('companies.type', '=', 'company_types.id')
            ->group_by('companies.id')
            ->order_by('companies.name')
            ->execute()->as_array();
        
        $view = View::factory('Security/Companies')
            ->bind('list', $list);
        $this->response->body($view);
    }
    
    public function action_edit() {
        $id = $this->request->param('id');
        
        $form = new Form('security/companies/edit' . ($id ? '/' . $id : ''));
        
        $types = DB::select('id', 'name')->from('company_types')->execute()->as_array('id', 'name');
        
        $form->add("name", 'Name', Form::STRING, '', array('not_empty'))
            ->add('type', 'Company type', Form::SELECT, $types, array('not_empty'));
        
        $item = DB::select()->from('companies')->where('id', '=', $id)->execute()->current();
        
        $form->values($item);
        
        if ($_POST) {
            $value = $form->filter($_POST);
            
            if (!$form->validate($value)) {
                if ($id)
                    DB::update('companies')->set($value)->where('id', '=', $id)->execute();
                else {
                    DB::insert('companies', array_keys($value))->values(array_values($value))->execute();
                }
                
                Messages::save('Company successfully saved!', 'success');
                    
                $this->redirect('/security/companies');
            }
        }
        
        $this->response->body($form->render());
        
    }

    public function action_delete() {
        $id = $this->request->param('id');
        
        $users = DB::select('id')->from('users')->where('company_id', '=', $id)->limit(1)->execute()->get('id');
        if (!$users) {
            DB::delete('companies')->where('id', '=', $id)->execute();
            Messages::save('Company successfully deleted!', 'info');
        }
        
        $this->redirect('/security/companies');
    }
}
