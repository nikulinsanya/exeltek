<?php defined('SYSPATH') or die('No direct script access.');
class Pager {
    public static $limit = 50;
    public static $count = 0;
    
    public static $counts = array(10, 20, 50, 100, 200, 500);
    
    public static function limit() {
        return User::current('list_items') ? : self::$limit;
    }
    
    public static function offset() {
        if (!self::$count) return 0;
        $page = max(Arr::get($_GET, 'page', 1) - 1, 0);
        if ($page * self::limit() > self::$count) $page = floor((self::$count - 1) / self::limit());
        return $page * self::limit();
    }
    
    public static function pages() {
        if (!self::$count) return 1;
        return floor((self::$count - 1) / self::limit()) + 1;
    }
    
    public static function page() {
        return min(max(Arr::get($_GET, 'page', 1), 1), self::pages());
    }
}
