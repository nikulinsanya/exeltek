<?php defined('SYSPATH') or die('No direct script access.');

class Enums {
    
    private static $enums = array();
    private static $values = array();
    
    const STATUS_UNALLOC = 0;
    const STATUS_ALLOC = 1;
    const STATUS_PENDING = 2;
    const STATUS_COMPLETE = 3;
    const STATUS_ARCHIVE = 4;
    
    public static $statuses = array(
        self::STATUS_UNALLOC => 'Unallocated tickets',
        self::STATUS_ALLOC => 'Allocated tickets',
        self::STATUS_PENDING => 'Tickets submitted',
        self::STATUS_COMPLETE => 'Tickets processed',
        self::STATUS_ARCHIVE => 'Archived',
    );
    
    private static function init() {
        self::$enums = DB::select()->from('enumerations')->execute()->as_array('id');
        
        $result = DB::select()->from('enumeration_values')->order_by('value', 'asc')->execute()->as_array();
        self::$values = array();
        foreach ($result as $value) {
            self::$values[$value['enum_id']][$value['value']] = $value['value'];
        }
    }
    
    public static function get_values($id, $default = NULL) {
        if (!self::$enums) self::init();
        
        $values = Arr::get(self::$values, $id, array());
        if ($default !== NULL && !isset($values[$default])) $values[$default] = $default;
        return $values;
    }
    
    public static function is_multi($id) {
        if (!self::$enums) self::init();
        
        return Arr::path(self::$enums, $id . '.allow_multi');
    }

}