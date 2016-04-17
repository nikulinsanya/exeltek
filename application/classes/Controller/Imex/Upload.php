<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Imex_Upload extends Controller {

    public function action_index()
    {
        if ($_FILES) {
            try {
                $file = array();
                foreach (Arr::get($_FILES, 'files', array()) as $id => $value)
                    $file[$id] = Arr::get($value, 0);
                    
                preg_match_all("/([0-9]+)/", Arr::get($_SERVER, 'HTTP_CONTENT_RANGE', ''), $matches);
                $range = Arr::get($matches, 0);
                
                $size = filesize($file['tmp_name']);
                if (!is_uploaded_file($file['tmp_name'])) {
                    die(json_encode(array(
                        'files' => array(
                            array(
                                'name' => $file['name'],
                                'size' => $size,
                                'error' => 'Error!',
                            ),
                        ),
                    )));
                }
                
                $src = fopen($file['tmp_name'], 'r');
                $dest = fopen(DOCROOT . '/files/' . $file['name'], 'c');
                fseek($dest, Arr::get($range, 0, 0));
                $buf = fread($src, $size);
                fwrite($dest, $buf, $size);

                fclose($dest);
                fclose($src);
                unlink($file['tmp_name']);
            } catch (Exception $e) {
                throw $e;
                die(json_encode(array(
                        'files' => array(
                            array(
                                'name' => $file['name'],
                                'size' => 0,
                                'error' => $e->getMessage(),
                            ),
                        ),
                    )));
            }
            
            die(json_encode(array(
                'files' => array(
                    array(
                        'name' => $file['name'],
                        'size' => $size,
                    ),
                ),
                'header' => isset($header) ? $header : '',
            )));
        }

        $regions = DB::select('id', 'name', 'last_update')->from('regions')->execute()->as_array('id');
        
        $view = View::factory("Jobs/Upload")
            ->bind('regions', $regions);
        
        $this->response->body($view);
    }

    public function action_prepare() {
        ini_set("memory_limit", "512M");

        define('PRIMARY_KEY', 'Ticket Of Work');

        $filename = $this->request->param('id');

        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }

        $region = 0;
        $last_update = 0;
        $time = '';
        $date = 0;
        if (preg_match('/([0-9]{8})_EXEL_([A-Z]*)(_R)?_([SE]OD)/i', strtoupper($filename), $matches)) {
            $date = $matches[1];
            $region = $matches[2];
            $time = $matches[4];
            $date = strtotime($date);

            switch ($time) {
                case 'EOD':
                    $time = 1;
                    break;
                case 'SOD':
                    $time = 0;
                    break;
                default:
                    $time = '';
                    break;
            }

            $result = DB::select()->from('regions')->where('name', '=', $region)->execute()->current();
            if ($result) {
                $region = Arr::get($result, 'id');
            } elseif ($region == 'PARTIAL')
                $region = -1;
            else
                $region = 0;
        }

        $file = fopen(DOCROOT . '/files/' . $filename, 'r');
        $header = fgetcsv($file);
        $total = 0;
        while (fgetcsv($file) !== false) $total++;
        fclose($file);

        $columns = array(
            -1 => 'Ticket Of Work',
            0 => 'Please, select a column...',
            -2 => '<<< IGNORE >>>',
        );
        $result = DB::select('id', 'name')->from('job_columns')->execute()->as_array('id', 'name');
        foreach ($result as $key => $value)
            $columns[$key] = $value;
        $map = array('ticketofwork' => -1);
        foreach ($columns as $key => $name)
            $map[preg_replace('/[^a-z0-9]/', '', strtolower($name))] = $key;

        foreach ($header as $value) {
            $id = str_replace("(YYYY-MM-DD)", '', $value);
            $id = str_replace("(in m)", '', $id);
            $id = preg_replace('/[^a-z0-9]/', '', strtolower($id));
            foreach ($columns as $key => $name)
                if (preg_replace('/[^a-z0-9]/', '', strtolower($name)) == $id) {
                    unset($columns[$key]);
                    break;
                }
        }
        $html = '';
        $matched = 0;
        foreach ($header as $key => $value) {
            $id = str_replace("(YYYY-MM-DD)", '', $value);
            $id = str_replace("(in m)", '', $id);
            $id = preg_replace('/[^a-z0-9]/', '', strtolower($id));

            if (isset($map[$id]))
                $matched++;
            else
                $html .= '<tr class="bg-' . (isset($map[$id]) ? 'success' : 'danger') . '"><td>' . $value . '</td><td>' .
                    Form::select(NULL, $columns, '', array('class' => 'form-control', 'data-id' => $key))
                . '</td></tr>';
        }
        if ($html)
            $html = '<table class="table"><tr><th class="col-xs-4"></th><th class="col-xs-8"></th></tr>' . $html . '</table>';
        else
            $html = '<h4 class="text-success">All columns are matched - you can proceed to import</h4>';

        $html = '<h3 class="text-success">Columns matched: ' . $matched . '/' . count($header) . '. Total tickets: ' . $total . '</h3>' . $html;

        die(json_encode(array(
            'success' => true,
            'region' => $region,
            'date' => $date ? date('d-m-Y', $date) : '',
            'tod' => $time,
            'html' => $html,
        )));
    }

    public function action_process() {
        if (!isset($_POST['pos'])) die('Error: file position not defined!');

        $pos = $_POST['pos'];

        $region = Arr::get($_POST, 'region', 0);
        $date = strtotime(Arr::get($_POST, 'date'));
        $tod = Arr::get($_POST, 'tod', 0);
        if ($tod) $date += 18 * 60 * 60;
        $time = $tod ? 'EOD' : 'SOD';

        if ($region > 0)
            $reg_name = DB::select('name')->from('regions')->where('id', '=', $region)->execute()->get('name');
        else
            $reg_name = 'PARTIAL';

        ini_set("memory_limit", "512M");
        
        $filename = $this->request->param('id');
        
        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }

        $file = fopen(DOCROOT . '/files/' . $filename, 'r');
        $header = fgetcsv($file);

        $columns = array(
            -1 => 'Ticket Of Work',
            0 => 'Please, select a column...',
            -2 => '<<< IGNORE >>>',
        );
        $result = DB::select('id', 'name')->from('job_columns')->execute()->as_array('id', 'name');
        foreach ($result as $key => $value)
            $columns[$key] = $value;
        $map = array('ticketofwork' => -1);
        foreach ($result as $key => $name) {
            $id = preg_replace('/[^a-z0-9]/', '', strtolower($name));
            if (isset($map[$id]))
                if (is_array($map[$id]))
                    $map[$id][] = $key;
                else
                    $map[$id] = array($map[$id], $key);
            else
                $map[$id] = $key;
        }

        foreach ($header as $value) {
            $id = str_replace("(YYYY-MM-DD)", '', $value);
            $id = str_replace("(in m)", '', $id);
            $id = preg_replace('/[^a-z0-9]/', '', strtolower($id));
            foreach ($columns as $key => $name)
                if (preg_replace('/[^a-z0-9]/', '', strtolower($name)) == $id) {
                    unset($columns[$key]);
                    break;
                }
        }

        $keys = array();
        $data = Arr::get($_POST, 'data', array());
        foreach ($header as $key => $value) {
            $id = str_replace("(YYYY-MM-DD)", '', $value);
            $id = str_replace("(in m)", '', $id);
            $id = preg_replace('/[^a-z0-9]/', '', strtolower($id));

            if (isset($map[$id])) {
                if (is_array($map[$id])) {
                    $keys[$key] = array_shift($map[$id]);
                    if (!$map[$id]) unset($map[$id]);
                } else {
                    $keys[$key] = $map[$id];
                    unset($map[$id]);
                }
            } else
                if (isset($data[$key]) && isset ($columns[$data[$key]]))
                    $keys[$key] = $data[$key];
        }

        if (count($keys) != count($header)) die(json_encode(array(
            'success' => true,
            'error' => 'Error: Unable to map all columns!',
        )));

        $primary_key_id = -1;
        foreach ($keys as $key => $id)
            if ($id == -1) $primary_key_id = $key;

        if ($primary_key_id < 0) die(json_encode(array(
            'success' => true,
            'error' => 'Error: Unable to map Ticket ID!',
        )));
        else unset($keys[$primary_key_id]);

        if ($pos) fseek($file, $pos);
        
        $types = DB::select('id', 'type')->from('job_columns')->where('csv', '=', 1)->execute()->as_array('id', 'type');
        
        $static = DB::select('id', 'name')->from('job_columns')->where('csv', '=', 1)->and_where('show_reports', '=', 1)->execute()->as_array('id', 'name');

        $start = microtime(true);
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $deleted = 0;

        $update_time = time();
        
        $jobs = Database_Mongo::collection('jobs');
        $archive = Database_Mongo::collection('archive');

        $ids = Session::instance()->get($filename);
        
        if ($region > 0 && $ids === NULL && !$pos) {
            $result = $jobs->find(array('region' => $region), array('_id'));
            $ids = array();
            while ($row = $result->next()) {
                $ids[$row['_id']] = 1;
            }
        }
            
        while (true) {
            $row = fgetcsv($file);
            $id = Arr::get($row, $primary_key_id);
            
            if ($id && strlen($id) == 15 && substr($id, 0, 3) == 'T1W') {
                try {
                    unset($row[$primary_key_id]);
                    $data = array();
                    foreach ($row as $key => $value) if ($keys[$key] > 0) {
                        switch (Columns::get_type($keys[$key])) {
                            case 'date':
                            case 'datetime':
                                $value = $value ? strtotime(str_replace('/', '-', $value)) : '';
                                break;
                            case 'int':
                                $value = intval($value);
                                break;
                            case 'float':
                                $value = floatval($value);
                                break;
                            default:
                                $value = iconv("CP1251", 'UTF-8//ignore', $value);
                                break;
                        }
                        $data[$keys[$key]] = $value;
                    }

                    $job = $jobs->findOne(array('_id' => $id));

                    if ($job) {
                        $diff = array();
                        $new = array();
                        foreach ($data as $key => $value) {
                            $old = Arr::get($job['data'], $key, '');
                            if (($old || $value) && $old != $value) {
                                if (!Columns::get_persistent($key))
                                    if ($value)
                                        $new['$set']['data.' . $key] = $value;
                                    else
                                        $new['$unset']['data.' . $key] = 1;
                                $diff[$key] = array(
                                    'old_value' => Arr::get($job['data'], $key),
                                    'new_value' => $value ? : '',
                                );
                            }
                        }
                        if (Arr::get($job, 'removed'))
                            $new['$unset']['removed'] = 1;

                        if ($diff || $new) {
                            if (isset($data[44])) {
                                $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($diff, '44.old_value')));
                                $status2 = preg_replace('/[^a-z]/', '', strtolower(Arr::path($new, array('$set', 'data.44'))));

                                $status_updated = isset($new['$set']['data.44']);

                                $discrepancy = array();
                                if ($status_updated && (($status == 'tested' && $status2 != 'tested') || ($status == 'built' && ($status2 != 'built' && $status2 != 'tested')) ||
                                        ($status != $status2 && in_array($status2, array('deferred', 'dirty', 'heldnbn'), true)))
                                )
                                    $discrepancy[44] = $diff[44];

                                foreach (Columns::get_track() as $key) if ($key != 44 && isset($diff[$key]) && !$diff[$key]['new_value'] && $diff[$key]['old_value'])
                                    $discrepancy[$key] = $diff[$key];

                                if ($discrepancy) {
                                    $discrepancy = array(
                                        'job_key' => $id,
                                        'update_time' => $update_time,
                                        'user_id' => User::current('id'),
                                        'filename' => $filename,
                                        'data' => $discrepancy,
                                        'fields' => array_keys($discrepancy),
                                    );
                                    Database_Mongo::collection('discrepancies')->insert($discrepancy);
                                    Database_Mongo::collection('jobs')->update(array('_id' => $id), array('$set' => array('discrepancies' => $discrepancy['_id'])));
                                }
                            }
                            if ($new) {
                                $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($new, array('$set', 'data.44'))));

                                if ($status == 'built' && !Arr::path($job, 'data.264'))
                                    $new['$set']['data.264'] = $update_time;

                                if ($status == 'tested' && !Arr::path($job, 'data.265')) {
                                    $new['$set']['data.265'] = $update_time;
                                    if (!Arr::path($job, 'data.264'))
                                        $new['$set']['data.264'] = $update_time;
                                }

                                $new['$set']['last_update'] = $update_time;
                                if (isset($new['$set']['data.8']))
                                    $new['$set']['address'] = MapQuest::parse($new['$set']['data.8']);
                                elseif (isset($new['$unset']['data.8']))
                                    $new['$unset']['address'] = 1;
                                $jobs->update(array('_id' => $id), $new);

                                foreach (array_keys($diff) as $key)
                                    if (Columns::get_persistent($key))
                                        unset($diff[$key]);

                                if ($diff) {
                                    $archive->insert(array(
                                        'job_key' => $id,
                                        'update_time' => $update_time,
                                        'update_type' => 2,
                                        'user_id' => User::current('id'),
                                        'filename' => $filename,
                                        'static' => array_intersect_key($data, $static),
                                        'data' => $diff,
                                        'fields' => array_keys($diff),
                                    ));
                                }
                                $updated++;
                            } else $skipped++;
                        } else $skipped++;
                    } elseif ($region > 0) {
                        $inserted++;
                        $job = array(
                            '_id' => $id,
                            'region' => $region,
                            'created' => $update_time,
                            'last_update' => $update_time,
                            'data' => $data,
                        );
                        if (isset($data[8]))
                            $job['address'] = MapQuest::parse($data[8]);

                        $jobs->insert($job);
                        $archive->insert(array(
                            'job_key' => $id,
                            'update_time' => $update_time,
                            'update_type' => 1,
                            'user_id' => User::current('id'),
                            'filename' => $filename,
                            'static' => array_intersect_key($data, $static),
                            'data' => array(),
                            'fields' => array(),
                        ));
                    }

                    unset($ids[$id]);
                } catch (Exception $e) {
                    print_r($e);
                    die();
                }
            }
            
            $pos = ftell($file);
            
            if (feof($file) || microtime(true) - $start > 1) break;
        }
        
        $finished = filesize(DOCROOT . '/files/' . $filename) == $pos ? 1 : 0;
        if ($finished) {
            if ($ids && $region > 0 && !isset($_POST['skip-deleted'])) {
                $jobs->update(array('_id' => array('$in' => array_keys($ids))), array('$set' => array('removed' => "1")), array('multiple' => 1));
                $result = $archive->find(
                    array('job_key' => array('$in' => array_keys($ids))),
                    array('job_key', 'update_type', 'update_time')
                );
            
                $list = array();
                while ($value = $result->next())
                    if (!isset($list[$value['job_key']])) {
                        $list[$value['job_key']] = array(
                            'date' => $value['update_time'],
                            'state' => $value['update_type'],
                        );
                    } elseif ($list[$value['job_key']]['date'] < $value['update_time'])
                        $list[$value['job_key']]['state'] = $value['update_type'];

                $cnt = 0;
                foreach ($ids as $id => $v) if (Arr::path($list, array($id, 'state'), 0) != 3) {
                    $cnt++;
                    $values = array(
                        'job_key' => $id,
                        'update_type' => 3,
                        'update_time' => $update_time,
                        'user_id' => User::current('id'),
                        'filename' => $filename,
                        'data' => array(),
                        'fields' => array(),
                    );
                    $archive->insert($values);
                }
                $deleted = $cnt;
            }
            
            if (Kohana::$environment == Kohana::PRODUCTION) unlink(DOCROOT . 'files/' . $filename);
        } else {
            Session::instance()->set($filename, $ids);
        }

        $result = array(        
            'success' => true,
            'time' => microtime(true) - $start,
            'memory' => memory_get_peak_usage(true),
            'inserted' => $inserted,
            'updated' => $updated,
            'deleted' => $deleted,
            'skipped' => $skipped,
            'position' => $pos,
            'progress' => $finished ? 100 : number_format($pos * 100/ filesize(DOCROOT . '/files/' . $filename)),
            'done' => $finished,
        );
        if ($finished) {
            $result['import_name'] = $reg_name . '_' . date('Ymd', $date) . '_' . $time;
            DB::update('regions')->set(array('last_update' => $date))->where('id', '=', $region)->execute();
        }
        
        die(json_encode($result));
    }
}
