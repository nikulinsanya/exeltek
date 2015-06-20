<?php defined('SYSPATH') or die('No direct script access.');

class Messages {

    public static function save($text, $type = 'danger')
    {
        $messages = Session::instance()->get('messages', array());
        $messages[] = array('text' => $text, 'type' => $type);
        Session::instance()->set('messages', $messages);
    }

    public static function render() {
        $messages = Session::instance()->get_once('messages', array());
        $result = '';
        foreach ($messages as $message)
            $result .= View::factory('Message')
                ->set('type', $message['type'])
                ->set('text', $message['text']);
                
        return $result;
    }
}