<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Upload extends Controller
{
    public function action_index()
    {
        $id = intval($this->request->param('id'));

        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        if (!$attachment)
            throw new HTTP_Exception_404('Not found');

        if (Arr::get($attachment, 'uploaded'))
            throw new HTTP_Exception_403('Forbidden');

        $job_id = Arr::get($attachment, 'job_id');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($job_id)));

        if (!$job)
            throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            throw new HTTP_Exception_403('Forbidden');

        if ($_FILES) {
            try {
                $file = Arr::get($_FILES, 'attachment', array());

                $file['name'] = trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-\.]/i', '-', Arr::get($file, 'name', ''))), '-');

                preg_match_all("/([0-9]+)/", Arr::get($_SERVER, 'HTTP_CONTENT_RANGE', ''), $matches);

                $range = Arr::get($matches, 0);

                $size = Arr::get($range, 2, filesize($file['tmp_name']));
                if (!is_uploaded_file($file['tmp_name'])) {
                    die(json_encode(array(
                        'attachment' => array(
                            'name' => $file['name'],
                            'size' => $size,
                            'error' => 'Error!',
                        ),
                    )));
                }

                $src = fopen($file['tmp_name'], 'r');
                $dest = fopen(DOCROOT . 'storage/' . $id, 'c');
                fseek($dest, Arr::get($range, 0, 0));
                $buf = fread($src, $size);
                fwrite($dest, $buf);

                fclose($dest);
                fclose($src);
                unlink($file['tmp_name']);

                if (!$range || ((Arr::get($range, 1) + 1) == Arr::get($range, 2))) {
                    $data = array(
                        'filename' => $file['name'],
                        'mime' => $file['type'],
                        'uploaded' => time(),
                    );
                    if ($attachment['filename']) {
                        $data['filename'] = $attachment['filename'];

                        $pos = strrpos($file['name'], '.');
                        if ($pos !== false)
                            $data['filename'] .= substr($file['name'], $pos);
                    } else {
                        $data['filename'] = ($attachment['folder'] == 'Other' ? $attachment['title'] : '') . $file['name'];
                    }
                    $data['filename'] = str_replace('%NUM%', $attachment['numbering'], $data['filename']);
                    Database::instance()->begin();
                    DB::update('attachments')->set($data)->where('id', '=', $id)->execute();
                    $data = array(
                        'user_id' => User::current('id'),
                        'job_id' => $attachment['job_id'],
                        'uploaded' => $data['uploaded'],
                        'location' => $attachment['location'],
                        'filename' => $attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $data['filename'],
                        'action' => 1,
                    );
                    DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                    Database::instance()->commit();
                    Messages::save("File " . $file['name'] . ' was successfully uploaded!', 'success');
                    die(json_encode(array(
                        'attachment' => array(
                            'name' => $file['name'],
                            'size' => $size,
                            'content' => (Group::current('allow_assign') ? '<a href="' . URL::base() . 'search/view/' . $id . '?delete=' . $id . '"
                                confirm="Do you really want to delete this attachment? This action can\'t be undone!!!"
                                class="text-danger glyphicon glyphicon-remove remove-link"></a>' : '') .
                                '<a href="' . URL::base() . 'download/attachment/' . $id . '">
                                <img target="_blank" src="http://stdicon.com/' . $file['type'] . '?size=32&default=http://stdicon.com/text" />' .
                                HTML::chars($data['filename']) . '</a>
                                - Uploaded ' . date('d-m-Y H:i', $data['uploaded']) . ' by ' . User::current('login'),
                            'message' => Messages::render(),
                        ),
                    )));
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            die(json_encode(array(
                'attachment' => array(
                    'name' => $file['name'],
                    'size' => $size,
                ),
            )));
        }

        $view = View::factory("Jobs/UploadFile");

        $this->response->body($view);
    }
}