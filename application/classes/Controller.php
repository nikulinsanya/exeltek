<?php defined('SYSPATH') or die('No direct script access.');

class Controller extends Kohana_Controller {
    
    public function before() {
        if (!User::current())
            $this->redirect('/login');
        
        $url = str_replace('.', '_', URL::base() . $this->request->uri());
        if (isset($_GET[$url])) 
            unset($_GET[$url]);
        if (isset($_GET[$url . '/'])) 
            unset($_GET[$url . '/']);
            
        if (Arr::get($_GET, 'limit') && in_array($_GET['limit'], Pager::$counts)) {
            DB::update('users')->set(array('list_items' => intval($_GET['limit'])))->where('id', '=', User::current('id'))->execute();
            die(json_encode(array('success' => 'true')));
        }
        
        if (Arr::get($_GET, 'dismiss')) {
            DB::delete('notifications')->where('user_id', '=', User::current('id'))->and_where('id', '=', intval($_GET['dismiss']))->execute();
            die(json_encode(array('success' => 'true')));
        }
        
        if (!Group::current('allow_assign'))
            Enums::$statuses[Enums::STATUS_UNALLOC] = 'Not active';
            
        View::set_global('notifications', DB::select()->from('notifications')->where('user_id', '=', User::current('id'))->order_by('id', 'desc')->execute());
    }
    
    public function after() {
        $content = $this->response->body();
        
        $view = View::factory('Content')
            ->bind('content', $content);
        $this->response->body($view);
    }
}
