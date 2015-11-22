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

        $zip_name = $folder . ($fda ? '-' . $fda : '') . ($address ? '-' . $address : '');

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

        $fname = tempnam(sys_get_temp_dir(), 'jobs');

        $zip = new ZipArchive();
        $zip->open($fname, ZipArchive::CREATE);
        foreach ($files as $file) {
            $path = '';
            if (!$address) $path = $file['address'] . '/' . $path;
            if (!$fda) $path = $file['fda_id'] . '/' . $path;

            $pos = strrpos($file['filename'], '.');
            if ($pos) {
                $ext = substr($file['filename'], $pos);
                $name = $path . '/' . substr($file['filename'], 0, $pos);
            } else {
                $ext = '';
                $name = $path . '/' . $file['filename'];
            }

            if (isset($list[$name . $ext])) {
                $i = 1;
                while (isset($list[$name . '(' . $i . ')' . $ext])) $i++;
                $name = $name . '(' . $i . ')';
            }

            $zip->addFile('storage/' . $file['id'], $name . $ext);
            $list[$name . $ext] = 1;
        }
        $zip->close();

        header('Content-type: application/zip');
        header('Content-disposition: filename="' . $zip_name . '.zip"');
        readfile($fname);
        unlink($fname);

        die();
    }

    public function action_tickets() {
        $ids = explode(',', Arr::get($_GET, 'id'));

        if (!$ids) throw new HTTP_Exception_404('Not found!');

        $zip_name = implode('-', $ids);

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

        $fname = tempnam(sys_get_temp_dir(), 'jobs');

        $zip = new ZipArchive();
        $zip->open($fname, ZipArchive::CREATE);
        $list = array();
        foreach ($files as $file) {
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

            $zip->addFile('storage/' . $file['id'], $name . $ext);
            $list[$name . $ext] = 1;

        }
        $zip->close();

        header('Content-type: application/zip');
        header('Content-disposition: filename="' . $zip_name . '.zip"');
        readfile($fname);
        unlink($fname);

        if (is_array($ids))
            $query = array('_id' => array('$in' => $ids));
        else
            $query = array('_id' => $ids);

        Database_Mongo::collection('jobs')->update($query, array('$set' => array('downloaded' => '1', 'download-by' => User::current('id'), 'download-at' => time())), array('multiple' => 1));

        die();
    }
}
