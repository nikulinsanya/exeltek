<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Prepare extends Controller
{
    public function action_index()
    {
        $id = $this->request->param('id');
        $location = Arr::get($_GET, 'location', '');
        $type = Arr::get($_GET, 'type', 'other');
        $title = Arr::get($_GET, 'title', 'other');

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => strval($id)));

        if (!$job)
            throw new HTTP_Exception_404('Not found');

        if (!Group::current('show_all_jobs') && !in_array((int)User::current('company_id'), Arr::get($job, 'companies', array()), true))
            throw new HTTP_Exception_403('Forbidden');

        switch ($type) {
            case 'photo-before':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.before.%NUM%';
                $title = '';
                break;
            case 'photo-after':
                $type = 'Photos';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.after.%NUM%';
                $title = '';
                break;
            case 'jsa':
                $type = 'JSA-forms';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.JSA.%NUM%';
                $title = '';
                break;
            case 'waiver':
                $type = 'Waiver';
                $filename = $id . '.' . Arr::path($job, 'data.9') . '.' . Arr::path($job, 'data.14') . '.Waiver.%NUM%';
                $title = '';
                break;
            case 'odtr':
                $title = '';
                $type = 'otdr-traces';
                $filename = '';
                break;
            default:
                $type = 'Other';
                $filename = '';
                break;
        }

        $number = DB::select('numbering')
            ->from('attachments')
            ->where('job_id', '=', $id)
            ->and_where('folder', '=', $type)
            ->order_by('numbering', 'desc')
            ->limit(1)
            ->execute()->get('numbering');

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
            'numbering' => intval($number) + 1,
        );

        $result = Arr::get(DB::insert('attachments', array_keys($data))->values(array_values($data))->execute(), 0);

        if (file_exists(DOCROOT . 'storage/' . $result))
            unlink(DOCROOT . 'storage/' . $result);

        die(json_encode(array(
            'success' => true,
            'id' => $result,
        )));
    }

}