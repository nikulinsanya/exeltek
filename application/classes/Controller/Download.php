<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Download extends Controller {

    private $attachment = false;

    public function before() {
        $id = intval($this->request->param('id'));

        $this->attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        if (!$this->attachment)
            throw new HTTP_Exception_404('Not found');
    }

    public function action_attachment()
    {
        if (!file_exists(DOCROOT . 'storage/' . $this->attachment['id']))
            throw new HTTP_Exception_404('Not found');

        header('Content-type: '. $this->attachment['mime']);
        header('Content-disposition: filename="' . $this->attachment['filename'] . '"');
        header('Content-length: ' . filesize(DOCROOT . 'storage/' . $this->attachment['id']));

        header('X-Accel-Redirect: /storage/' . $this->attachment['id']);
        header('X-SendFile: ' . DOCROOT . 'storage/' . $this->attachment['id']);

        //readfile(DOCROOT . 'storage/' . $this->attachment['id']);
        
        die();
    }

    public function action_thumb() {

        if (!file_exists(DOCROOT . 'storage/' . $this->attachment['id'] . '.thumb')) {
            if (!file_exists(DOCROOT . 'storage/' . $this->attachment['id']))
                $this->redirect('http://stdicon.com/' . $this->attachment['mime'] . '?size=96&default=http://stdicon.com/text');

            $data = file_get_contents(DOCROOT . 'storage/' . $this->attachment['id']);
            $image = imagecreatefromstring($data);

            $x = imagesx($image);
            $y = imagesy($image);
            $size = max($x, $y);
            $x = round($x / $size * 96);
            $y = round($y / $size * 96);

            $thumb = imagecreatetruecolor($x, $y);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $x, $y, imagesx($image), imagesy($image));

            imagepng($thumb, DOCROOT . 'storage/' . $this->attachment['id'] . '.thumb', 9);
        }

        header('Content-type: image/png');
        header('Content-disposition: filename="thumbnail.png"');
        header('Content-length: ' . filesize(DOCROOT . 'storage/' . $this->attachment['id'] . '.thumb'));

        header('X-Accel-Redirect: /storage/' . $this->attachment['id'] . '.thumb');
        header('X-SendFile: ' . DOCROOT . 'storage/' . $this->attachment['id'] . '.thumb');

        //readfile(DOCROOT . 'storage/' . $this->attachment['id'] . '.thumb');

        die();
    }
}
