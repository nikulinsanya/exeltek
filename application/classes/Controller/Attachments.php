<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Attachments extends Controller {
    
    public function before() {
        parent::before();

        if (!Group::current('allow_assign'))
            throw new HTTP_Exception_403();
    }
    
    public function action_index() {
        $result = DB::select('folder', 'fda_id', 'address')
            ->distinct(true)
            ->from('attachments')
            ->where('uploaded', '>', 0)
            ->and_where('folder', '<>', '')
            ->and_where('fda_id', '<>', '')
            ->and_where('address', '<>', '')
            ->order_by('folder', 'asc')
            ->order_by('fda_id', 'asc')
            ->order_by('address', 'asc')
            ->execute()->as_array();
            
        $folders = array();
        foreach ($result as $item)
            $folders[$item['folder']][$item['fda_id']][] = $item['address'];
        
        
        $view = View::factory('Attachments')
            ->bind('folders', $folders);
        
        $this->response->body($view);
    }
    
    public function action_files() {
        $folder = Arr::get($_GET, 'folder');
        $fda = Arr::get($_GET, 'fda');
        $address = Arr::get($_GET, 'address');
        
        $data = '<li>' . $folder;
        if ($fda) $data .= ' / ' . $fda;
        if ($address)  $data .= ' / ' . $address;
        $data .= '</li>';

        if ($address) {
            $files = DB::select('id', 'mime', 'filename')
                ->from('attachments')
                ->where('uploaded', '>', 0)
                ->and_where('folder', '=', $folder)
                ->and_where('fda_id', '=', $fda)
                ->and_where('address', '=', $address)
                ->order_by('filename', 'asc')
                ->execute()->as_array();
                
            foreach ($files as $file) {
                $data .= '<li><a href="' . URL::base() . 'download/attachment/' . $file['id'] . '">
                        <img src="http://stdicon.com/' . $file['mime'] . '?size=32&default=http://stdicon.com/text" />'.
                        HTML::chars($file['filename']) . '</a></li>';
            }
        }
            
        die($data);
    }

    public function action_folder() {
        $folder = Arr::get($_GET, 'folder');
        $fda = Arr::get($_GET, 'fda');
        $address = Arr::get($_GET, 'address');

        //$zip_name = $folder . ($fda ? '-' . $fda : '') . ($address ? '-' . $address : '');

        $files = DB::select('id', 'filename', 'folder', 'fda_id', 'address')
            ->from('attachments')
            ->where('uploaded', '>', 0)
            ->and_where('folder', '=', $folder);

        if ($fda)
            $files->and_where('fda_id', '=', $fda);

        if ($address)
            $files->and_where('address', '=', $address);

        $files = $files->order_by('filename', 'asc')
            ->execute()->as_array();

        $expire = time() + 600;

        $download_id = Text::random('alnum', 32);

        $list = array();
        foreach ($files as $file) if (file_exists(DOCROOT . 'storage/' . $file['id'])) {

            $path = '';
            if (!$address) $path = $file['address'] . '/' . $path;
            if (!$fda) $path = $file['fda_id'] . '/' . $path;

            $pos = strrpos($file['filename'], '.');
            if ($pos) {
                $ext = substr($file['filename'], $pos);
                $name = $path . substr($file['filename'], 0, $pos);
            } else {
                $ext = '';
                $name = $path . $file['filename'];
            }

            if (isset($list[$name . $ext])) {
                $i = 1;
                while (isset($list[$name . '(' . $i . ')' . $ext])) $i++;
                $name = $name . '(' . $i . ')';
            }

            $list[] = array(
                'path' => $name . $ext,
                'attachment_id' => $file['id'],
                'size' => filesize(DOCROOT . 'storage/' . $file['id']),
                'user_id' => User::current('id'),
                'download_id' => $download_id,
                'expires' => $expire,
            );
        }

        $total = 0;
        $files = array();
        while (current($list)) {
            $file = current($list);
            $total += $file['size'];
            $files[] = $file;
            next($list);

            if (count($files) > 500 || (!current($list) && $files)) {
                $query = DB::insert('downloads', array_keys($file));

                foreach ($files as $file)
                    $query->values(array_values($file));

                $query->execute();
                $files = array();
            }

        }

        header('Content-type: application/json');
        die(json_encode(array(
            'success' => true,
            'id' => $download_id,
            'count' => count($list),
            'total' => $total,
        )));
    }

    public function action_tickets() {
        $ids = explode(',', Arr::get($_GET, 'id'));

        if (!$ids) throw new HTTP_Exception_404('Not found!');

        //$zip_name = implode('-', $ids);

        $files = DB::select('id', 'filename', 'folder', 'fda_id', 'address')
            ->from('attachments')
            ->where('uploaded', '>', 0);

        if (count($ids) > 1)
            $files->and_where('job_id', 'IN', $ids);
        else {
            $ids = array_shift($ids);
            $files->and_where('job_id', '=', $ids);
        }

        $files = $files->order_by('filename', 'asc')
            ->execute()->as_array();

        $expire = time() + 600;

        $download_id = Text::random('alnum', 32);

        $list = array();
        foreach ($files as $file) if (file_exists(DOCROOT . 'storage/' . $file['id'])) {

            $pos = strrpos($file['filename'], '.');
            if ($pos) {
                $ext = substr($file['filename'], $pos);
                $name = $file['folder'] . '/' . $file['fda_id'] . '/' . $file['address'] . '/' . substr($file['filename'], 0, $pos);
            } else {
                $ext = '';
                $name = $file['folder'] . '/' . $file['fda_id'] . '/' . $file['address'] . '/' . $file['filename'];
            }

            if (isset($list[$name . $ext])) {
                $i = 1;
                while (isset($list[$name . '(' . $i . ')' . $ext])) $i++;
                $name = $name . '(' . $i . ')';
            }

            $list[] = array(
                'path' => $name . $ext,
                'attachment_id' => $file['id'],
                'size' => filesize(DOCROOT . 'storage/' . $file['id']),
                'user_id' => User::current('id'),
                'download_id' => $download_id,
                'expires' => $expire,
            );
        }

        $total = 0;
        $files = array();
        while (current($list)) {
            $file = current($list);
            $total += $file['size'];
            $files[] = $file;
            next($list);

            if (count($files) > 500 || (!current($list) && $files)) {
                $query = DB::insert('downloads', array_keys($file));

                foreach ($files as $file)
                    $query->values(array_values($file));

                $query->execute();
                $files = array();
            }

        }

        if (is_array($ids))
            $query = array('_id' => array('$in' => $ids));
        else
            $query = array('_id' => $ids);

        Database_Mongo::collection('jobs')->update($query, array('$set' => array('downloaded' => '1', 'download-by' => User::current('id'), 'download-at' => time())), array('multiple' => 1));

        header('Content-type: application/json');
        die(json_encode(array(
            'success' => true,
            'id' => $download_id,
            'count' => count($list),
            'total' => $total,
        )));
    }

    public function action_download() {
        $id = preg_replace('/[^a-z0-9]/i', '', $this->request->param('id'));

        DB::delete('downloads')->where('expires', '<=', time())->execute();

        $expired = DB::select('filename')->from('download_packages')->where('expires', '<=', time())->execute()->as_array(NULL, 'filename');

        if ($expired) {
            foreach ($expired as $file)
                if (file_exists($file))
                    unlink($file);

            DB::delete('download_packages')->where('filename', 'IN', $expired)->execute();
        }

        $filename = DOCROOT . 'storage/zip' . $id;

        DB::query(Database::UPDATE, DB::expr('REPLACE INTO `download_packages` (`filename`, `expires`) VALUES (:filename, :expires)', array(':filename' => $filename, ':expires' => time() + 3600))->compile())->execute();

        $files = DB::select()->from('downloads')->where('download_id', '=', $id)->execute()->as_array();

        if (!$files) {
            if (!file_exists($filename)) throw new HTTP_Exception_404('Not found');

            ob_end_clean();

            header('X-SendFile: ' . realpath($filename));
            header('Content-type: application/zip');
            header('Content-disposition: attachment; filename="Attachments.zip"');
            header('X-Accel-Redirect: ' . URL::base() . 'storage/zip' . $id);
            exit;
            /*header('Content-length: ' . filesize($filename));


            $file = fopen($filename, 'r');
            $bufsize = 16 * 1024 * 1024;
            while ($buf = fread($file, $bufsize)) {
                echo $buf;
                $buf = '';
            }
            fclose($file);*/
        }

        $count = 0;
        $total = 0;

        foreach ($files as $file) {
            $total += $file['size'];
            $count++;

            if (!isset($zip)) {
                $zip = new ZipArchive();
                $zip->open($filename, ZipArchive::CREATE);
            }

            $zip->addFile(DOCROOT . 'storage/' . $file['attachment_id'], $file['path']);
            $zip->setCompressionName($file['path'], ZipArchive::CM_STORE);

            DB::delete('downloads')->where('id', '=', $file['id'])->execute();

            if ($count >= 100 || $total >= 100 * 1024 * 1024) {
                $zip->close();
                unset($zip);
                break;
            }
        }

        if (isset($zip))
            $zip->close();

        $info = DB::select(DB::expr('SUM(`size`) as size'), DB::expr('COUNT(*) as cnt'))->from('downloads')->where('download_id', '=', $id)->execute()->current();

        header('Content-type: application/json');
        die(json_encode(array(
            'success' => true,
            'id' => $id,
            'count' => intval($info['cnt']),
            'total' => intval($info['size']),
        )));
    }
}