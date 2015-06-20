<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Download extends Controller {

    public function action_attachment()
    {
        $id = intval($this->request->param('id'));
        
        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();
        
        header('Content-type: '. $attachment['mime']);
        header('Content-disposition: filename="' . $attachment['filename'] . '"');
        
        readfile(DOCROOT . 'storage/' . $id);
        
        die();
    }
}
