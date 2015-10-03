<?php defined('SYSPATH') or die('No direct script access.');
class Columns {
    const COLUMN_HIDDEN = 0;
    const COLUMN_READ = 1;
    const COLUMN_WRITE = 2;
    
    public static $states = array(
        self::COLUMN_HIDDEN => 'Hidden',
        self::COLUMN_READ => 'Read-only',
        self::COLUMN_WRITE => 'Read/Write',
    );
    
    public static $searches = array(
        self::COLUMN_HIDDEN => 'Don\'t show',
        self::COLUMN_READ => 'Simple filtering',
        self::COLUMN_WRITE => 'Advanced filtering',
    );
    
    public static $fixed = array(
        'last_update' => 'Last update',
        'last_submit' => 'Last submit',
        'status' => 'Job status',
        'types' => 'Assigned job types',
        'companies' => 'Assigned companies',
        'ex' => 'Previously assigned companies',
        'settings' => 'Job settings',
        'pending' => 'Pending submissions',
        'attachments' => 'Attachments',
    );
    
    public static $settings = array(
        'dilapidation' => 'Dilapidation photos',
        'locked' => 'Lock submissions',
        'trace' => 'Trace results OK',
        'jsa' => 'JSA form OK',
        'sor' => 'SOR data correct',
        'quality' => 'QA audit done',
        'passed' => 'QA audit passed',
        'removed' => 'Removed from SOD',
        'downloaded' => 'Attachments downloaded',
        'discrepancies' => 'Has discrepancies',
        'partial' => 'Is partially paid',
        'paid' => 'Is paid',
    );

    public static $settings_read_only = array(
        'removed',
        'downloaded',
        'discrepancies',
        'partial',
        'paid',
    );
    
    public static $settings_img = array(
        'dilapidation' => 'picture',
        'locked' => 'lock',
        'trace' => 'search',
        'jsa' => 'list-alt',
        'sor' => 'ok',
        'quality' => 'pencil',
        'passed' => 'check',
        'removed' => 'remove',
        'downloaded' => 'floppy-saved',
        'discrepancies' => 'duplicate',
        'partial' => 'align-left',
        'paid' => 'align-justify',
    );
    
    private static $columns = array();
    private static $all = array();
    private static $types = array();
    private static $permissions = array();
    private static $tabs = array();
    private static $static = array();
    private static $list = array();
    private static $csv = array();
    private static $search = array();
    private static $direct = array();
    private static $track = array();
    private static $persistent = array();
    private static $readonly = array();
    private static $financial = array();
    
    private static function init() {
        $columns = DB::select()->from('job_columns')->execute()->as_array();
        
        $result = DB::select('column_id', 'permissions', 'search')
            ->from('group_columns')
            ->where('group_id', '=', Group::current('id'))
            ->execute();
        
        $permissions = $result->as_array('column_id', 'permissions');
        $search = $result->as_array('column_id', 'search');
        
        foreach ($columns as $column) {
            self::$all[$column['id']] = $column['name'];
            self::$types[$column['id']] = $column['type'];
            self::$tabs[$column['id']] = $column['tab_id'];
            if ($column['csv']) self::$csv[$column['id']] = $column['name'];
            if ($column['direct']) self::$direct[$column['id']] = $column['name'];
            if ($column['show_reports']) self::$static[$column['id']] = $column['show_reports'];
            if (Arr::get($permissions, $column['id'])) self::$permissions[$column['id']] = Arr::get($permissions, $column['id']);
            if (Arr::get($search, $column['id'])) self::$search[$column['id']] = Arr::get($search, $column['id']);
            if ($column['track']) self::$track[$column['id']] = $column['id'];
            if ($column['persistent']) self::$persistent[$column['id']] = $column['id'];
            if ($column['read_only']) self::$readonly[$column['id']] = $column['id'];
            if (floatval($column['financial'])) self::$financial[$column['id']] = floatval($column['financial']);
        }
        
        if (Group::current('is_admin')) {
            self::$columns = self::$all;
            self::$search = self::$static;
        } else
            self::$columns = array_intersect_key(self::$all, self::$permissions);
    }

    public static function get_readonly($id = false) {
        if (!self::$all)
            self::init();

        return $id ? Arr::get(self::$readonly, $id) : self::$readonly;
    }

    public static function get_financial($id = false) {
        if (!self::$all)
            self::init();

        return $id ? Arr::get(self::$financial, $id) : self::$financial;
    }

    public static function get_name($id) {
        if (!self::$all)
            self::init();
        
        return Arr::get(self::$all, $id);
    }

    public static function get_track($id = false) {
        if (!self::$all)
            self::init();

        return $id ? Arr::get(self::$track, $id) : self::$track;
    }

    public static function get_persistent($id = false) {
        if (!self::$all)
            self::init();

        return $id ? Arr::get(self::$persistent, $id) : self::$persistent;
    }

    public static function get_static($id = false) {
        if (!self::$all)
            self::init();
        
        return $id ? Arr::get(self::$static, $id) : self::$static;
    }
    
    public static function get_search($id = false) {
        if (!self::$all)
            self::init();
        
        return $id ? Arr::get(self::$search, $id) : self::$search;
    }
    
    public static function get_all() {
        if (!self::$all)
            self::init();
        
        return self::$all;
    }
    
    public static function get_csv($id = false) {
        if (!self::$csv)
            self::init();
        
        return $id !== false ? Arr::get(self::$csv, $id, '') : self::$csv;
    }
    
    public static function get_direct($id = false) {
        if (!self::$all)
            self::init();
        
        return $id !== false ? Arr::get(self::$direct, $id, '') : self::$direct;
    }
    
    public static function get_visible() {
        if (!self::$all)
            self::init();        
            
        return self::$columns;
    }
    
    public static function get_type($id = false) {
        if (!self::$all)
            self::init();
        
        
        return $id !== false ? Arr::get(self::$types, $id, '') : self::$types;
    }
    
    public static function get_tab($id = false) {
        if (!self::$all)
            self::init();
        
        return $id ? Arr::get(self::$tabs, $id) : self::$tabs;
    }
    
    public static function set_readonly() {
        if (!self::$all)
            self::init();

        foreach (self::$permissions as $key => $value)
            self::$permissions[$key] = self::COLUMN_READ;
    }
    
    public static function allowed($id = false) {
        if (!self::$all)
            self::init();
            
        if (Group::current('is_admin')) return self::COLUMN_WRITE;
        
        return $id ? Arr::get(self::$permissions, $id) : self::$permissions;
    }
    
    public static function parse($value, $type = false) {
        if (strpos($type, 'enum') !== false) {
            $enum = substr($type, 5);
            if (Enums::is_multi($enum))
                return is_array($value) ? implode(', ', $value) : $value;
            else
                return $value;
        } elseif ($type == 'date' || $type == 'datetime')
            return strtotime($value);
        elseif ($type == 'float')
            return floatval($value);
        elseif ($type == 'int')
            return intval($value);
        elseif ($type == 'bool')
            return $value ? 1 : 0;
        else
            return $value;
    }
    
    public static function input($name, $id, $type, $value, $title = '', $required = false) {
        if (strpos($type, 'enum') !== false) {
            $enum = substr($type, 5);
            if (Enums::is_multi($enum)) {
                $class = array('class' => 'form-control multiselect', 'multiple'=>'multiple');
                if ($required) $class['data-validation'] = 'required';
                return Form::select($name . ($id ? '[' . $id . '][]' : '[]'),Enums::get_values($enum), explode(', ', $value), $class);

//                $values = explode(', ', $value);
//                $result = '';
//                foreach (Enums::get_values($enum) as $value)
//                    $result .= Form::label(NULL, Form::checkbox($name . ($id ? '[' . $id . ']' : '') . '[]', $value, in_array($value, $values, true)) . $value, array('class' => 'control-label')) . '<br/>';
//                return $result;
            } else {
                return Form::select($name . ($id ? '[' . $id . ']' : ''), array('' => '') + Enums::get_values($enum, $value), $value, array('class' => 'form-control'));
            }
        } else switch ($type) {
            case 'bool':
                return '<input type="hidden" value="0" name="' . $name . ($id ? '[' . $id . ']' : '') . '" /><label class="control-label"><input type="checkbox" name="' . $name . ($id ? '[' . $id . ']' : '') . '" id="' . $name . '-' . $id . '" ' . ($value ? 'checked' : '') . '/>' . $title . '</label>';
            case 'text':
                return '<textarea rows="10" id="' . $name . ($id ? '-' . $id : '') . '" class="form-control" name="' . $name . ($id ? '[' . $id . ']' : '') . '">' . HTML::chars($value) . '</textarea>';
                break;
            case 'date':
                return '<input type="text" id="' . $name . ($id ? '-' . $id : '') . '" class="form-control datepicker" name="' . $name . ($id ? '[' . $id . ']' : '') . '" value="' . ($value ? date('d-m-Y', $value) : '') . '" />';
                break;
            case 'datetime':
                return '<input type="text" id="' . $name . ($id ? '-' . $id : '') . '" class="form-control datetimepicker" name="' . $name . ($id ? '[' . $id . ']' : '') . '" value="' . ($value ? date('d-m-Y H:i', $value) : '') . '" />';
                break;
            case 'float':
                return '<input class="form-control input-float" id="' . $name . ($id ? '-' . $id : '') . '" name="' . $name . ($id ? '[' . $id . ']' : '') . '" type="text" value="'. HTML::chars($value) . '" />';
                break;
            case 'int':
                return '<input class="form-control input-int" id="' . $name . ($id ? '-' . $id : '') . '" name="' . $name . ($id ? '[' . $id . ']' : '') . '" type="text" value="'. HTML::chars($value) . '" />';
                break;
            default:
                return '<input class="form-control" id="' . $name . ($id ? '-' . $id : '') . '" name="' . $name . ($id ? '[' . $id . ']' : '') . '" type="text" value="'. HTML::chars($value) . '" />';
        }
    }
    
    public static function output($value, $type, $export = false) {
        if ($type == 'date')
            return $value ? date('d-m-Y', $value) : '';
        elseif ($type == 'datetime')
            return $value ? date('d-m-Y H:i', $value) : '';
        elseif ($type == 'float')
            return floatval($value);
        elseif ($type == 'int')
            return intval($value);
        elseif ($type == 'bool')
            return $export ? ($value ? 1 : 0) : '<span class="glyphicon glyphicon-' . $value ? 'ok text-success' : 'remove text-danger' . '"></span>';
        elseif ($type == 'text')
            return $export ? $value : nl2br(HTML::chars($value));
        else
            return $export ? $value : HTML::chars($value);        
    }
}