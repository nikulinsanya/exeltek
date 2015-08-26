<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Images extends Kohana_Controller {

    public function action_prepare() {
        $token = Arr::get($_REQUEST, 'token');
        $location = Arr::get($_REQUEST, 'location', '');
        $type = Arr::get($_REQUEST, 'type', 'other');
        $title = Arr::get($_REQUEST, 'title', '');
        $filename = Arr::get($_REQUEST, 'name', '');

        /*Database_Mongo::collection('api')->insert(array(
            'get' => $_GET,
            'post' => $_POST,
            'request' => $_REQUEST,
            'body' => \http\Env::getRequestBody(),
            'body2' => file_get_contents('php://input'),
            'headers' => \http\Env::getRequestHeader(),
        ));*/

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $id = strval(Arr::get($_REQUEST, 'id'));

        if (!$id)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($id)));

        if (!$job)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (!$type)
            die(json_encode(array('success' => false, 'error' => 'type required')));

        $pos = strrpos($filename, '.');
        $ext = $pos ? substr($filename, $pos) : '';

        $number = DB::select('numbering')
            ->from('attachments')
            ->where('job_id', '=', $id)
            ->and_where('folder', '=', $type)
            ->order_by('numbering', 'desc')
            ->limit(1)
            ->execute()->get('numbering');

        $number = intval($number) + 1;

        switch ($type) {
            case 'photo-before':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.before.' . $number . $ext;
                $title = '';
                break;
            case 'photo-after':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.after.' . $number . $ext;
                $title = '';
                break;
            case 'jsa':
                $type = 'JSA-forms';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.JSA.' . $number . $ext;
                $title = '';
                break;
            case 'waiver':
                $type = 'Waiver';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.Waiver.' . $number . $ext;
                $title = '';
                break;
            case 'odtr':
                $title = '';
                $type = 'otdr-traces';
                break;
            default:
                $type = 'Other';
                $filename = $title . $filename;
                break;
        }

        $data = array(
            'filename' => $filename,
            'mime' => '',
            'uploaded' => 0,
            'user_id' => User::current('id'),
            'job_id' => $id,
            'folder' => $type,
            'fda_id' => Arr::path($job, 'data.14'),
            'address' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-'),
            'title' => $title,
            'numbering' => $number,
            'location' => $location,
        );

        $result = Arr::get(DB::insert('attachments', array_keys($data))->values(array_values($data))->execute(), 0);

        if (file_exists(DOCROOT . 'storage/' . $result))
            unlink(DOCROOT . 'storage/' . $result);

        die(json_encode(array(
            'success' => true,
            'id' => $result,
            'folder' => $type,
            'name' => $filename,
        )));
    }

    public function action_upload() {
        $token = Arr::get($_GET, 'token');
        $id = intval(Arr::get($_GET, 'id'));
        $pos = intval(Arr::get($_GET, 'pos'));

        /*Database_Mongo::collection('api')->insert(array(
            'get' => $_GET,
            'post' => $_POST,
            'request' => $_REQUEST,
            'body' => \http\Env::getRequestBody(),
            'body2' => file_get_contents('php://input'),
            'headers' => \http\Env::getRequestHeader(),
        ));*/

        if (!API::check($token))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        $attachment = DB::select()->from('attachments')->where('id', '=', $id)->execute()->current();

        if (!$attachment)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (Arr::get($attachment, 'uploaded'))
            die(json_encode(array('success' => false, 'error' => 'not found')));

        $job_id = Arr::get($attachment, 'job_id');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($job_id)));

        if (!$job)
            die(json_encode(array('success' => false, 'error' => 'not found')));

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            die(json_encode(array('success' => false, 'error' => 'forbidden')));

        try {
            $filedata = base64_decode(file_get_contents('php://input'));

            $dest = fopen(DOCROOT . 'storage/' . $id, 'c');
            fseek($dest, $pos);
            fwrite($dest, $filedata);

            $pos = ftell($dest);

            fclose($dest);


            if (isset($_GET['last'])) {
                $data = array(
                    'mime' => File::mime(DOCROOT . 'storage/' . $id),
                    'uploaded' => time(),
                );
                Database::instance()->begin();
                DB::update('attachments')->set($data)->where('id', '=', $id)->execute();
                $data = array(
                    'user_id' => User::current('id'),
                    'job_id' => $attachment['job_id'],
                    'uploaded' => $data['uploaded'],
                    'location' => $attachment['location'],
                    'filename' => $attachment['folder'] . ' / ' . $attachment['fda_id'] . ' / ' . $attachment['address'] . ' / ' . $attachment['filename'],
                    'action' => 1,
                );
                DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                Database::instance()->commit();
                Database_Mongo::collection('jobs')->update(array('_id' => $attachment['job_id']), array('$unset' => array('downloaded' => 1), '$set' => array('last_update' => time())));
                die(json_encode(array('success' => true)));
            }
        } catch (Exception $e) {
            die(json_encode(array('success' => false, 'error' => 'exception')));
        }

        die(json_encode(array(
            'success' => true,
            'position' => $pos,
        )));
    }
}