<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Login extends Kohana_Controller {
    
    public function action_index() {
        if (Session::instance()->get('user_id'))
            $this->redirect('/');
            
        if ($_POST) {
            $login = Arr::get($_POST, 'login');
            $passw = Arr::get($_POST, 'passw');
            
            $id = DB::select('id')
                ->from('users')
                ->where('login', '=', $login)
                ->and_where('passw', '=', Auth::instance()->hash($passw))
                ->execute()->get('id');
            
            if ($id) {
                DB::update('users')->set(array('last_seen' => time()))->where('id', '=', $id)->execute();
                
                Session::instance()->set('user_id', $id);
                
                $this->redirect('/');
            } else Messages::save('Wrong login/password');
        }
        
        $view = View::factory("Login");
        $this->response->body($view);
    }
    
    public function action_deauth() {
        Session::instance()->destroy();
        $this->redirect('/login');
    }
}
