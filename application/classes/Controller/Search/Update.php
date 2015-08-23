<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Update extends Controller
{
    public function action_index()
    {
        $id = intval($this->request->param('id'));

        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($attachment['job_id'])));

        if (!$job)
            throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true) && !in_array((int)User::current('company_id'), Arr::get($job, 'ex', array()), true))
            throw new HTTP_Exception_403('Forbidden');

        if (!file_exists(DOCROOT . 'storage/' . $id))
            throw new HTTP_Exception_404('Not found');

        list(, $data) = explode(',', file_get_contents('php://input'), 2);
        $src = imagecreatefromstring(file_get_contents(DOCROOT . 'storage/' . $id));
        $image = imagecreatefromstring(base64_decode($data));
        imagealphablending($src, true);
        imagesavealpha($src, true);
        imagecopyresampled($src, $image, 0, 0, 0, 0, imagesx($src), imagesy($src), imagesx($image), imagesy($image));

        if ($attachment['mime'] == 'image/png')
            imagepng($src, DOCROOT . 'storage/' . $id, 9);
        else
            imagejpeg($src, DOCROOT . 'storage/' . $id, 90);

        if (file_exists(DOCROOT . 'storage/' . $id . '.thumb'))
            unlink(DOCROOT . 'storage/' . $id . '.thumb');

        imagedestroy($src);
        imagedestroy($image);
        die(json_encode(array('success' => true)));
    }
}