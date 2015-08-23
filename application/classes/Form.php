<?php defined('SYSPATH') or die('No direct script access.');

class Form extends Kohana_Form {
    
    const STRING = 0;
    const NUMBER = 1;
    const TEXT = 2;
    const SELECT = 3;
    const BOOL = 4;
    const FILE = 5;
    const PASSWORD = 6;
    const INFO = 7;
    const URL = 8;
    const HIDDEN = 9;

    public static $static_title = array(8, 9, 14, 19, 52, 53, 70, 44, 45);
    public static $columns = array(
        'No - job not yet finished / cannot be completed' => array(
            242 => 'Job Comment',
            246 => 'Status',
            247 => 'Date of calling card left',
            249 => 'Installation Type A: LIC Repair - Job could NOT be finished',
        ),
        'Yes - proceed to completed job submission form' => array(
            242 => 'Job comment',
            243 => 'Installation Date',
            25 => 'As built Drop Installation Type',
            26 => 'As Built Lead Length',
            28 => 'As Built Conduit Material',
            29 => 'As Built Conduit Length',
            31 => 'Hazards',
            33 => 'Property Access',
            34 => 'Wayleave Consent',
            35 => 'Permits Required',
            36 => 'PCD Location Comment',
            37 => 'LIC Present',
            38 => 'Part LIC Used',
            39 => 'Remediation Performed',
            40 => 'Non Usage Reason Code',
            41 => 'Non Usage Note',
            42 => 'Spliced',
            'ot1' => 'Optical Test Results on 1310 wavelength',
            'ot2' => 'Optical Test Results on 1625 wavelength',
            44 => 'Status',
            244 => 'Job Stages Completed',
            '162,190' => 'Installation Type A: (Existing Underground)',
            '163,191' => 'Installation Type B: (Aerial)',
            '164,192' => 'Installation Type C: (New Underground)',
            '165,193' => 'Installation Type A: Small Pit Installation',
            '166,194' => 'Installation Type A: Additional Length',
            '167,195' => 'Installation Type A: 10mm LIC',
            '168,196' => 'Installation Type A: LIC Repair',
            '169,197' => 'Installation Type B: Additional Span',
            '170,198' => 'Installation Type B: Wall Riser Installation',
            '171,199' => 'Installation Type C: Additional length in Metres',
            '172,200' => 'Installation Type C: Breakout and Reinstatement - Concrete in sqm',
            '173,201' => 'Installation Type C: Breakout and Reinstatement - Bitumen in sqm',
            '174,202' => 'Installation Type C: Breakout and Reinstatement - Pavers in sqm',
            '175,203' => 'Installation Type C: Breakout and Reinstatement - Pebblecrete in sqm',
            '176,204' => 'Aerial to Underground',
            'z,177,205' => 'Installation Type C: Under Bore',
            '178,206' => 'Installation Type C: Rock Installation in Metres',
            '179,207' => 'Installation Type C: Core Bore',
            '180,208' => 'Installation Type C: Asbestos Pit P20 Install',
            '181,209' => 'Non-Standard Variation in $AUD excl. GST',
            228 => 'Variation Sequence Number',
        ),
        'Yes - submit OTDR testing data only' => array(
            242 => 'Job Comment',
            252 => 'OTDR Testing date',
            34 => 'Wayleave Consent',
            42 => 'Spliced - is FIC Connector Installed',
            'ot1' => 'Optical Test Results on 1310 wavelength',
            'ot2' => 'Optical Test Results on 1625 wavelength',
            246 => 'Status',
        ),
    );

    public static $required = array(
        'No - job not yet finished / cannot be completed' => array(
            246 => 'Status',
        ),
        'Yes - proceed to completed job submission form' => array(
        ),
        'Yes - submit OTDR testing data only' => array(
            246 => 'Status',
        ),
    );
    
    private $elements = array();
    private $has_files = false;
    private $action;
    private $method;
    private $validator = array();
    private $back_button = false;
    
    public function __construct($action = '', $method = 'post', $back_button = true) {
        $this->action = $action;
        $this->method = $method;
        $this->back_button = $back_button;
    }
    
    /**
    * put your comment there...
    * 
    * @param string $name
    * @param string $descr
    * @param integer $type
    * @param mixed $value
    * @param array $attr
    * @return Forms
    */
    public function add($name, $descr, $type = self::STRING, $value = NULL, $validate = array(), $attr = array()) {
        $this->elements[] = array('name' => $name, 'descr' => $descr, 'value' => $value, 'type' => $type, 'attr' => $attr);
        if ($type == 'file') $this->has_files = true;
        if ($type == self::NUMBER) $validate += array('numeric', 'not_empty');
        if ($validate) $this->validator[$name] = $validate;
        
        return $this;
    } 
    
    public function values($values) {
        foreach ($this->elements as $id => $value) {
            $name = $value['name'];
            if (Arr::get($values, $name) !== NULL)
                if ($value['type'] == self::SELECT)
                    $this->elements[$id]['attr']['value'] = $values[$name];
                else
                    $this->elements[$id]['value'] = $values[$name];
        }
    }
    
    public function validate($data) {
        $val = new Validation($data);
        foreach ($this->validator as $key => $rules) foreach ($rules as $rule => $value)
            if (is_numeric($rule))
                $val->rule($key, $value);
            else
                $val->rule($key, $rule, $value);
        $val->check();
        return $val->errors();
    }
    
    public function filter($data) {
        $result = array();
        foreach ($this->elements as $element) 
            switch ($element['type']) {
                case self::PASSWORD:
                    if (isset($data[$element['name']]))
                        $result[$element['name']] = $data[$element['name']] ? Auth::instance()->hash_password($data[$element['name']]) : '';
                    break;
                case self::BOOL:
                    $result[$element['name']] = Arr::get($data, $element['name']) ? 1 : 0;
                    break;
                default:
                    if (isset($data[$element['name']]))
                        $result[$element['name']] = Arr::get($data, $element['name'], '');
                break;
            }
                
        return $result;
    }
    
    public function render($errors = false) {
        $params = array('method' => $this->method);
        if ($this->has_files)
            $params['enctype'] = 'multipart/form-data';
        
        $form = Form::open($this->action, $params);

        if (!$errors) {
            $values = array();
            foreach ($this->elements as $value)
                $values[$value['name']] = $value['type'] == Form::SELECT ? Arr::path($value, 'attr.value') : $value['value'];
                
            $errors = $this->validate($values);
        } else {
            $form .= '<div class="alert alert-danger">' . (is_array($errors) ? implode("\n", $errors) : $errors) . '</div>';
        }
        
        foreach ($this->elements as $element) {
            if (Arr::get($element, 'type') == self::BOOL && Arr::path($element, 'attr.default')) {
                $form .= Form::input($element['name'], 0, array('type' => 'hidden'));
                unset($element['attr']['default']);
            }
                
            if (strpos(Arr::path($element, 'attr.class', ''), 'has-success') !== false) {
                $element['attr']['class'] = str_replace('has-success', '', $element['attr']['class']);
                $warning = ' has-success';
            } else $warning = '';
            $form .= '<div class="' . Arr::path($element, 'attr.parent', '') . ($element['type'] == Form::BOOL ? 'checkbox' : 'form-group') . (Arr::get($errors, $element['name']) ? ' has-error' : $warning) . '">';
            //$element['descr'] .= Arr::path($element, 'attr.parent', '');
            if (!isset($element['attr']['id']))
                $element['attr']['id'] = 'form-' . $element['name'];
                
            if ($element['type'] != Form::BOOL) {
                $element['descr'] = $element['descr'] . (in_array('not_empty', Arr::get($this->validator, $element['name'], array()))? '*' : '') . ': ';

                $form .= Form::label('form-' . $element['name'], $element['descr'], array('class' => 'control-label'));
            
                if ($element['type'] != Form::FILE) if (isset($element['attr']['class']))
                    $element['attr']['class'] .= ' form-control';
                else
                    $element['attr']['class'] = 'form-control';
            }
                
            if (!isset($element['attr']['placeholder']) && !$element['descr'])
                $element['attr']['placeholder'] = $element['descr'];
                
            switch ($element['type']) {
                case self::STRING:
                    $form .= Form::input($element['name'], $element['value'], $element['attr']);
                    break;
                case self::NUMBER:
                    $form .= Form::input($element['name'], $element['value'], $element['attr']);
                    break;
                case self::TEXT:
                    $form .= Form::textarea($element['name'], $element['value'], $element['attr']);
                    break;
                case self::SELECT:
                    $selected = '';
                    $selected = Arr::get($element['attr'], 'value', NULL);
                    if ($selected)
                        unset($element['attr']['value']);
                    $form .= Form::select($element['name'], $element['value'], $selected, $element['attr']);
                    break;
                case self::BOOL:
                    $form .= Form::label(NULL, Form::checkbox($element['name'], Arr::get($element['attr'], 'value'), (bool)$element['value'], $element['attr']) . ' ' . $element['descr']);
                    break;
                case self::FILE:
                    $form .= Form::file($element['name'], $element['attr']);
                    break;
                case self::PASSWORD:
                    $form .= Form::password($element['name'], NULL, $element['attr']);
                    break;
                case self::INFO:
                    $form .= '<p class="form-control-static">' . HTML::chars($element['value']) . '</p>';
                    break;
                case self::URL:
                    $value = HTML::chars($element['value']);
                    $form .= '<a href="' . $value . '">' . $value . '</a>';
                    break;
            }
            $form .= '</div>';
        }
        $form .= '<div class="form-group">' . Form::submit(NULL, 'Save', array('class' => 'btn btn-primary'));
        if ($this->back_button)
            $form .= '&nbsp;' . Form::button(NULL, 'Back', array('class' => 'btn btn-danger back-button', 'type' => 'button'));
        $form .= '</div>';
        
        $form .= Form::close();
        
        return $form;
    }
    
}