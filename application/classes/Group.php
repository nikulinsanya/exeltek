<?php defined('SYSPATH') or die('No direct script access.');

class Group {
    private static $groups = array();
    
    public static function get($id, $key = NULL) {
        
        $single = !is_array($id);
        if (is_array($id))
            $ids = $id;
        else
            $ids = array($id);
        
        $groups = array();
        
        $keys = array();
        foreach ($ids as $id)
            if (isset(self::$groups[$id])) 
                $groups[$id] = self::$groups[$id];
            else $keys[$id] = 1;
            
        if ($keys) {
            $res = DB::select()
                ->from('groups')
                ->where('id', 'IN', array_keys($keys))
                ->execute();
                
            while ($group = $res->current()) {
                self::$groups[$group['id']] = $group;
                $groups[$group['id']] = $group;
                $res->next();
            }
        }
        
        if (!$groups)
            return false;
            
        if ($single) {
            $group = array_shift($groups);
            return $key ? Arr::get($group, $key) : $group;
        } else {
            if ($key) {
                $res = array();
                foreach ($groups as $id => $group)
                    $res[$id] = Arr::get($group, $key);
                return $res;
            } else
                return $groups;
        }
    }
    
    public static function current($key = NULL) {
        $group = self::get(User::current('group_id'), $key);
        return $group;
    }
}