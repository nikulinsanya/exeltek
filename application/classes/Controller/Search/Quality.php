<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search_Quality extends Controller
{
    public function before() {
        parent::before();

        if (!Group::current('allow_quality')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index()
    {
        $id = Arr::get($_GET, 'id');
        if ($id) {
            $report = Database_Mongo::collection('quality')->findOne(array('_id' => new MongoId($id)));
            if (!$report) throw new HTTP_Exception_404('Not found');
            $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $report['job_key']));
            if (!$job) throw new HTTP_Exception_404('Not found');
        } else {
            $report = array();
            $job = strval(Arr::get($_GET, 'job'));
            $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $job));
            if (!$job) throw new HTTP_Exception_404('Not found');
        }

        /*$action = $this->request->param('id');
        if ($action == 'print') {

            $view = View::factory('Reports/QualityPrint');

            require_once(APPPATH . 'mpdf/mpdf.php');
            $pdf = new mPDF();
            $pdf->ignore_invalid_utf8 = true;
            $pdf->WriteHTML($view);
            $pdf->Output();
            die('test');

            die($view);
        }*/

        if ($_POST) {
            function filter($array) {
                $result = array();
                foreach ($array as $key => $value) {
                    if (is_array($value)) $value = filter($value);
                    if ($value) $result[$key] = $value;
                }

                return $result;
            };
            $report = filter($_POST);

            if (isset($_POST['finalize'])) {
                require_once(APPPATH . 'mpdf/mpdf.php');
                $pdf = new mPDF();
                $pdf->ignore_invalid_utf8 = true;

                $view = View::factory('Reports/QualityPrint')
                    ->bind('job', $job)
                    ->bind('report', $report);

                $pdf->WriteHTML($view);
                $content = $pdf->Output('', 'S');

                $data = array(
                    'filename' => 'Report-' . date('dmY-His') . '.pdf',
                    'mime' => 'application/pdf',
                    'uploaded' => time(),
                    'user_id' => User::current('id'),
                    'job_id' => $job['_id'],
                    'folder' => 'Reports',
                    'fda_id' => Arr::path($job, 'data.14'),
                    'address' => trim(preg_replace('/-{2,}/', '-', preg_replace('/[^0-9a-z\-]/i', '-', Arr::path($job, 'data.8'))), '-'),
                    'title' => '',
                );
                Database::instance()->begin();
                $result = DB::insert('attachments', array_keys($data))->values(array_values($data))->execute();
                $image_id = Arr::get($result, 0);

                if ($image_id && file_put_contents(DOCROOT . 'storage/' . $image_id, $content)) {
                    unset($data['mime']);
                    $data = array(
                        'filename' => 'Reports / ' . Arr::path($job, 'data.14') . ' / ' . $data['address'] . ' / ' . $data['filename'],
                        'uploaded' => time(),
                        'user_id' => User::current('id'),
                        'job_id' => $job['_id'],
                        'action' => 1,
                    );
                    DB::insert('upload_log', array_keys($data))->values(array_values($data))->execute();
                    Database::instance()->commit();
                    Database_Mongo::collection('quality')->remove(array('_id' => new MongoId($id)));
                    $this->redirect('search/view/' . $job['_id'] . '#attachments');
                } else Messages::save('Error occurred during report processing... Please try again later');
            } else {
                $report['job_key'] = $job['_id'];
                if ($id) {
                    Database_Mongo::collection('quality')->update(array('_id' => new MongoId($id)), $report);
                } else {
                    Database_Mongo::collection('quality')->insert($report);
                }
            }
            $this->redirect('search/view/' . $job['_id'] . '#quality');
        }

        $view = View::factory('Reports/Quality')
            ->bind('report', $report)
            ->bind('job', $job);

        $this->response->body($view);
    }

    public function action_print() {
    }
}