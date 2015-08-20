<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Update extends Controller
{
    public function action_index()
    {
        $id = intval($this->request->param('id'));

        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        if (!file_exists(DOCROOT . 'storage/' . $id))
            throw new HTTP_Exception_404('Not found');

        list(, $data) = explode(',', file_get_contents('php://input'), 2);
        $src = imagecreatefromstring(file_get_contents(DOCROOT . 'storage/' . $id));
        $image = imagecreatefromstring(base64_decode($data));
        imagecopyresampled($src, $image, 0, 0, 0, 0, imagesx($src), imagesy($src), imagesx($image), imagesy($image));
        imagejpeg($src, DOCROOT . 'storage/' . $id, 90);
        imagedestroy($src);
        imagedestroy($image);
        die(json_encode(array('success' => true)));
    }
}