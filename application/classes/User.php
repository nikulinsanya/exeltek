<?php defined('SYSPATH') or die('No direct script access.');

class User {
    private static $users = array();
    
    public static function get($id, $key = NULL) {
        
        $single = !is_array($id);
        if (is_array($id))
            $ids = $id;
        else
            $ids = array($id);
        
        $users = array();
        
        $keys = array();
        foreach ($ids as $id)
            if (isset(self::$users[$id])) 
                $users[$id] = self::$users[$id];
            else $keys[$id] = 1;
            
        if ($keys) {
            $res = DB::select()
                ->from('users')
                ->where('id', 'IN', array_keys($keys))
                ->execute();
                
            while ($user = $res->current()) {
                self::$users[$user['id']] = $user;
                $users[$user['id']] = $user;
                $res->next();
            }
        }
        
        if (!$users)
            return false;
            
        if ($single) {
            $user = array_shift($users);
            return $key ? Arr::get($user, $key) : $user;
        } else {
            if ($key) {
                $res = array();
                foreach ($users as $id => $user)
                    $res[$id] = Arr::get($user, $key);
                return $res;
            } else
                return $users;
        }
    }
    
    public static function current($key = NULL) {
        $id = Request::$current->directory() == 'Api' ? API::user() : Session::instance()->get('user_id');

        return self::get($id, $key);
    }
}