<?php defined('SYSPATH') or die('No direct script access.');

class Company {
    private static $companies = array();
    
    public static function get($id, $key = NULL) {
        
        $single = !is_array($id);
        if (is_array($id))
            $ids = $id;
        else
            $ids = array($id);
        
        $companies = array();
        
        $keys = array();
        foreach ($ids as $id)
            if (isset(self::$companies[$id])) 
                $companies[$id] = self::$companies[$id];
            else $keys[$id] = 1;
            
        if ($keys) {
            $res = DB::select()
                ->from('companies')
                ->where('id', 'IN', array_keys($keys))
                ->execute();
                
            while ($company = $res->current()) {
                self::$companies[$company['id']] = $company;
                $companies[$company['id']] = $company;
                $res->next();
            }
        }
        
        if (!$companies)
            return false;
            
        if ($single) {
            $company = array_shift($companies);
            return $key ? Arr::get($company, $key) : $company;
        } else {
            if ($key) {
                $res = array();
                foreach ($companies as $id => $company)
                    $res[$id] = Arr::get($company, $key);
                return $res;
            } else
                return $companies;
        }
    }
    
    public static function current($key = NULL) {
        $company = self::get(User::current('company_id'), $key);
        //print_r(self::get(User::current('company_id'))); die();
        return $company;
    }
}