<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Export extends Controller {

    public function action_index() {
        $action = $this->request->param('id');

        $ids = array_keys(Arr::get($_POST, 'job', array()));
        if (!$ids)
            throw new HTTP_Exception_404('Not found');

        $regs = DB::select('region_id')->from('user_regions')->where('user_id', '=', User::current('id'))->execute()->as_array('region_id', 'region_id');

        $query = array();

        if ($regs)
            $query['region'] = array('$in' => array_values($regs));

        if (!Group::current('show_all_jobs')) {
            $query['$or'] = array(
                array('ex' => intval(User::current('company_id'))),
                array('companies' => intval(User::current('company_id'))),
            );
        }

        $query['_id'] = array('$in' => $ids);

        $jobs = Database_Mongo::collection('jobs')->find($query);

        $static = array_flip(explode(',', Group::current('columns')));

        $header = array(
            'Ticket ID',
        );

        $types = DB::select('id', 'name')->from('job_types')->execute()->as_array('id', 'name');
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        if ($action == 'excel') {
            $result = DB::select()->from('attachments')->where('job_id', 'IN', $ids)->and_where('uploaded', '>', 0)->and_where('folder', '<>', 'Signatures')->execute()->as_array();
            $attachments = array();
            $attachments_list = array();
            foreach ($result as $row) {
                $attachments[$row['job_id']] = Arr::get($attachments, $row['job_id'], 0) + 1;
                if (preg_match('/^image\/.*$/i', $row['mime']))
                    $attachments_list[$row['job_id']][] = $row['id'];
            }
        } elseif ($ids && $static['attachments'])
            $attachments = DB::select('job_id', DB::expr('COUNT(*) as cnt'))
                ->from('attachments')
                ->where('job_id', 'IN', $ids)
                ->and_where('uploaded', '>', 0)
                ->group_by('job_id')
                ->execute()->as_array('job_id', 'cnt');
        else $attachments = array();

        if (Group::current('allow_assign')) {
            $result = Database_Mongo::collection('submissions')->aggregate(
                array(
                    array('$match' => array('job_key' => array('$in' => $ids), 'active' => 1)),
                    array('$group' => array('_id' => '$job_key', 'count' => array('$sum' => 1))),
                ));

            $submissions = array();
            foreach (Arr::get($result, 'result', array()) as $value)
                $submissions[$value['_id']] = $value['count'];
        }

        if (isset($static['last_update'])) $header[] = 'Last update';
        if (isset($static['last_submit'])) $header[] = 'Last submit';
        if (isset($static['status']) && Group::current('show_all_jobs')) $header[] = 'Job status';
        if (isset($static['types'])) $header[] = 'Assigned works';
        if (isset($static['companies'])) $header[] = 'Assigned companies';
        if (isset($static['pending'])) $header[] = 'Pending submissions';
        if (isset($static['attachments'])) $header[] = 'Attachments';

        foreach (Columns::get_search() as $id => $type)
            $header[] = Columns::get_name($id);

        if ($action == 'excel') {
            $excel = new PHPExcel();
            $sheet = $excel->getActiveSheet();
            $sheet->setTitle('Search Results');
            $sheet->fromArray($header, NULL, 'A1');
            $i = 1;
            foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            $file = tmpfile();

            fputcsv($file, $header);
        }

        foreach ($jobs as $ticket) {
            $row = array($ticket['_id']);

            if (isset($static['last_update'])) $row[] = date('d-m-Y H:i', Arr::get($ticket, 'last_update', $ticket['created']));
            if (isset($static['last_submit'])) $row[] = Arr::get($ticket, 'last_submit') ? date('d-m-Y H:i', $ticket['last_submit']) : '';
            if (isset($static['status']) && Group::current('show_all_jobs')) $row[] = Arr::get(Enums::$statuses, Arr::get($ticket, 'status', 0), 'Unknown');
            if (isset($static['types']))
                if (Group::current('allow_assign'))
                    $row[] = implode(', ', array_intersect_key($types, Arr::get($ticket, 'assigned', array())));
                else
                    $row[] = implode(', ', array_intersect_key($types, array_filter(Arr::get($ticket, 'assigned', array()), function ($x) {
                        return $x == User::current('company_id');
                    })));


            if (isset($static['companies'])) $row[] = implode(', ', array_intersect_key($companies, array_flip(Arr::get($ticket, 'assigned', array()))));
            if (isset($static['pending'])) $row[] = Arr::get($submissions, $ticket['_id']);
            if (isset($static['attachments'])) $row[] = Arr::get($attachments, $ticket['_id']);

            foreach (Columns::get_search() as $id => $type)
                $row[] = Arr::path($ticket, array('data', $id)) ? Columns::output($ticket['data'][$id], Columns::get_type($id), true) : '';

            if ($action == 'excel') {
                $i++;
                $sheet->fromArray($row, NULL, 'A' . $i);
                $x = count($row);
                foreach (Arr::get($attachments_list, $ticket['_id'], array()) as $image) {
                    if (!file_exists(DOCROOT . 'storage/' . $image . '.thumb')) {
                        if (!file_exists(DOCROOT . 'storage/' . $image)) continue;

                        $data = file_get_contents(DOCROOT . 'storage/' . $image);
                        try {
                            $img = @imagecreatefromstring($data);

                            if (!$img) continue;

                            $x = imagesx($img);
                            $y = imagesy($img);
                            $size = max($x, $y);
                            $x = round($x / $size * 128);
                            $y = round($y / $size * 128);

                            $thumb = imagecreatetruecolor($x, $y);
                            imagealphablending($thumb, false);
                            imagesavealpha($thumb, true);

                            imagecopyresampled($thumb, $img, 0, 0, 0, 0, $x, $y, imagesx($img), imagesy($img));

                            imagepng($thumb, DOCROOT . 'storage/' . $image . '.thumb', 9);
                        } catch (Exception $e) {
                            continue;
                        }
                    }

                    $data = file_get_contents(DOCROOT . 'storage/' . $image . '.thumb');
                    $img = imagecreatefromstring($data);

                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setImageResource($img);

                    $coord = PHPExcel_Cell::stringFromColumnIndex($x++);
                    $objDrawing->setCoordinates($coord . $i);
                    $objDrawing->setResizeProportional(true);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setWorksheet($sheet);
                    $sheet->getRowDimension($i)->setRowHeight(100);
                    $sheet->getColumnDimension($coord)->setWidth(20);
                    //$sheet->getCell($coord . $i)->setHyperlink(new PHPExcel_Cell_Hyperlink(URL::site('download/attachment/' . $image, 'http'), 'Show Image'));
                }
            } else {
                fputcsv($file, $row);
            }
        }

        if ($action == 'excel') {
            $name = tempnam(sys_get_temp_dir(), 'excel');

            header('Content-type: application/xlsx');
            header('Content-disposition: filename="SearchResults.xlsx"');

            $writer = new PHPExcel_Writer_Excel2007($excel);
            $writer->save($name);
            readfile($name);
            unlink($name);
        } else {
            fseek($file, 0);

            header('Content-type: text/csv');
            header('Content-disposition: filename="SearchResults.csv"');

            fpassthru($file);

            fclose($file);
        }

        die();
    }

}