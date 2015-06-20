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
            )));
        }
        
        $view = View::factory("Jobs/Upload");
        
        $this->response->body($view);
    }

    public function action_start()
    {
        ini_set("memory_limit", "512M");
        
        define('PRIMARY_KEY', 'Ticket Of Work');
        
        $filename = $this->request->param('id');
        
        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }
        
        if (preg_match('/([0-9]{8})_EXEL_([A-Z]*)(_R)?_([SE]OD)/i', $filename, $matches)) {
            
            $date = $matches[1];
            $region = $matches[2];
            $reg_name = $region;
            $time = $matches[4];
            $date = strtotime($date);
            if ($time == 'EOD') $date += 18 * 60 * 60;
            
            $result = DB::select()->from('regions')->where('name', '=', $region)->execute()->current();
            if ($result) {
                $region = Arr::get($result, 'id');
                
                $old = Arr::get($result, 'last_update');
                
                if ($old >= $date && !isset($_GET['force']))
                    $forced = array(
                        'link' => URL::base() . $this->request->uri() . URL::query(array('force' => 1)),
                        'message' => 'Warning: Newer file (' . date('Y-m-d H:i', $old) . ') was already imported! Do you want to proceed import with older file?',
                    );
            }
        } else {
            if (Kohana::$environment == Kohana::PRODUCTION) unlink(DOCROOT . 'files/' . $filename);
            
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Wrong file name - unable to determine region. Import aborted! Proper file name format: DDMMYYYY_EXEL_ZZZ_TTT.csv, ZZZ - State, TTT - SOD or EOD',
            )));
        }
        
        $file = fopen(DOCROOT . 'files/' . $filename, "r");
        $headers = fgetcsv($file);
        $used = array();
        $primary_key_id = -1;
        
        $columns = DB::select('id', 'name', 'type')->from('job_columns')->where('csv', '=', '1')->execute();
        $column_types = $columns->as_array('id', 'type');
        $columns = $columns->as_array('id', 'name');
        
        $extra = array();
        $types = array();

        foreach ($headers as $id => $header) {
            $header = str_replace("(YYYY-MM-DD)", '', $header);
            $header = str_replace("(in m)", '', $header);
            $header = preg_replace("/[^a-z _0-9]/i", ' ', $header);
            $header = trim(preg_replace("/ +/", ' ', $header));

            $key = array_search($header, $columns, true);

            if ($header == PRIMARY_KEY)
                $primary_key_id = $id;
            elseif ($key !== false) {
                $used[$id] = $key;
                $types[$id] = $column_types[$key];
                unset($columns[$key]);
            } else
                $extra[$id] = $header;
        }
        
        if ($extra || $columns) {
            if (count($extra) == count($columns)) {
                if (isset($_GET['replace'])) {
                    $columns = array_keys($columns);
                    foreach ($extra as $id => $column) {
                        $used[$id] = array_shift($columns);
                    }                    
                } else {
                    $list = array();
                    foreach ($columns as $column) {
                        $list[] = $column . ' => ' . array_shift($extra);
                    }
                    $forced = array(
                        'link' => URL::base() . $this->request->uri() . URL::query(array('replace' => 1)),
                        'message' => "Warning! Some fields were renamed:\n" . implode("\n", $list) . "\n" .
                            'Do you want to force import with replacing renamed fields with original names?',
                    );
                }
            } else {
                print_r($extra);
                //unlink(DOCROOT . 'files/' . $filename);
                $res = "Warning: Import aborted, data fields mismatch!\n";
                if ($extra)
                    $res .= "Missing fields in data file: " . implode(', ', $extra) . "\n";
                if ($used)
                    $res .= "Unknown fields in data file: " . implode(', ', array_keys($used)) . "\n";
                
                die(json_encode(array(
                    'success' => true,
                    'error' => $res,
                )));
            }
        }
        
        $pos = ftell($file);
        
        Session::instance()->set('import-' . $filename . '-fields', $used);
        Session::instance()->set('import-' . $filename . '-key', $primary_key_id);
        Session::instance()->delete($filename);
        
        die(json_encode(array(
            'success' => true,
            'filename' => $filename,
            'import_name' => $reg_name . '_' . date('Ymd', $date) . '_' . $time,
            'position' => $pos,
            'forced' => isset($forced) ? $forced : '',
            'time' => 0,
            'memory' => memory_get_peak_usage(true),
            'inserted' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'progress' => 0,
            'done' => 0,
        )));

    }
    
    public function action_process() {
        $pos = Arr::get($_GET, 'pos');
        if (!$pos) die('Error: file position not defined!');

        ini_set("memory_limit", "512M");
        
        $filename = $this->request->param('id');
        
        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }
        
        $partial = false;
        
        if (preg_match('/([0-9]{8})_EXEL_([A-Z]*)(_R)?_([SE]OD)/i', $filename, $matches)) {
            
            $date = $matches[1];
            $region = $matches[2];
            $reg_name = $region;
            $time = $matches[4];

            $date = strtotime($date);
            if ($time == 'EOD') $date += 18 * 60 * 60;
            
            $result = DB::select()->from('regions')->where('name', '=', $region)->execute()->current();
            if ($result) {
                $region = Arr::get($result, 'id');
                DB::update('regions')->set(array('last_update' => $date))->where('id', '=', $region)->execute();
            } elseif (!isset($_GET['partial'])) {
                $region = array(
                    'name' => $region,
                    'last_update' => $date,
                );
                $result = DB::insert('regions', array_keys($region))->values(array_values($region))->execute();
                $region = strval(Arr::get($result, 0));
            }
        } else {
            if (Kohana::$environment == Kohana::PRODUCTION) unlink(DOCROOT . 'files/' . $filename);

            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Wrong file name - unable to determine region. Import aborted! Proper file name format: DDMMYYYY_EXEL_ZZZ_TTT.csv, ZZZ - State, TTT - SOD or EOD',
            )));
        }

        $file = fopen(DOCROOT . 'files/' . $filename, "r");
        fseek($file, $pos);
        
        $keys = Session::instance()->get('import-' . $filename . '-fields');
        $primary_key_id = Session::instance()->get('import-' . $filename . '-key');
        
        $types = DB::select('id', 'type')->from('job_columns')->where('csv', '=', 1)->execute()->as_array('id', 'type');
        
        $static = DB::select('id', 'name')->from('job_columns')->where('csv', '=', 1)->and_where('show_reports', '=', 1)->execute()->as_array('id', 'name');

        $start = microtime(true);
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $deleted = 0;
        
        $values = array();
        $existing = array();
        $updates = array();
        $done = false;

        $jobs = Database_Mongo::collection('jobs');
        $archive = Database_Mongo::collection('archive');
        
        $ids = Session::instance()->get($filename);
        
        if ($ids === NULL || isset($_GET['reset'])) {
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
                    foreach ($row as $key => $value) if ($value) {
                        switch ($types[$keys[$key]]) {
                            case 'date':
                                $value = strtotime($value);
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

                    $job = $jobs->findOne(array('_id' => $id), array('data'));

                    if ($job) {
                        $diff = array();
                        $new = array();
                        foreach ($data as $key => $value) if (Columns::get_csv($key)) {
                            if (!isset($job['data'][$key]) || $job['data'][$key] != $value) {
                                $new['data.' . $key] = $value;
                                $diff[$key] = array(
                                    'old_value' => Arr::get($job['data'], $key),
                                    'new_value' => $value,
                                );
                            }
                            unset($job['data'][$key]);
                        }
                        foreach ($job['data'] as $key => $value) if (Columns::get_csv($key)) if ($value) {
                            $diff[$key] = array(
                                'old_value' => $value,
                                'new_value' => NULL,
                            );
                            $new['data.' . $key] = NULL;
                        }

                        if ($diff) {
                            $new['last_update'] = time();
                            $jobs->update(array('_id' => $id), array('$set' => $new));
                            $archive->insert(array(
                                'job_key' => $id,
                                'update_time' => time(),
                                'update_type' => 2,
                                'user_id' => User::current('id'),
                                'filename' => $reg_name . '_' . date('Ymd', $date) . '_' . $time,
                                'static' => array_intersect_key($data, $static),
                                'data' => $diff,
                                'fields' => array_keys($diff),
                            ));
                            $updated++;

                            $status = preg_replace('/[^a-z]/', '', strtolower(Arr::path($diff, '44.old_value')));
                            $status2 = preg_replace('/[^a-z]/', '', strtolower(Arr::get($new, 'data.44')));
                            $status_updated = isset($new['data.44']);

                            $discrepancy = array();
                            if ($status_updated && (($status == 'tested' && $status2 != 'tested') || ($status == 'built' && ($status2 != 'built' && $status2 != 'tested')) ||
                                    ($status != $status2 && in_array($status2, array('deferred', 'dirty', 'heldnbn'), true)))
                            )
                                $discrepancy[44] = $diff[44];

                            foreach (Columns::get_track() as $key) if (isset($diff[$key]) && !$diff[$key]['new_value'] && $diff[$key]['old_value'])
                                $discrepancy[$key] = $diff[$key];

                            if ($discrepancy) {
                                Database_Mongo::collection('discrepancies')->insert(array(
                                    'job_key' => $id,
                                    'update_time' => time(),
                                    'user_id' => User::current('id'),
                                    'filename' => $reg_name . '_' . date('Ymd', $date) . '_' . $time,
                                    'data' => $discrepancy,
                                    'fields' => array_keys($discrepancy),
                                ));

                            }
                        } else $skipped++;
                    } elseif (!isset($_GET['partial'])) {
                        $inserted++;
                        $job = array(
                            '_id' => $id,
                            'region' => $region,
                            'created' => time(),
                            'data' => $data,
                        );
                        $jobs->insert($job);
                        $archive->insert(array(
                            'job_key' => $id,
                            'update_time' => time(),
                            'update_type' => 1,
                            'user_id' => User::current('id'),
                            'filename' => $reg_name . '_' . date('Ymd', $date) . '_' . $time,
                            'static' => array_intersect_key($data, $static),
                            'data' => array(),
                            'fields' => array(),
                        ));
                    }

                    unset($ids[$id]);
                } catch (Exception $e) {

                }
            }
            
            $pos = ftell($file);
            
            if (feof($file) || microtime(true) - $start > 1) break;
        }
        
        $finished = filesize(DOCROOT . '/files/' . $filename) == $pos ? 1 : 0;
        if ($finished) {
            if ($ids && !isset($_GET['partial'])) {
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
                        'update_time' => time(),
                        'user_id' => User::current('id'),
                        'filename' => $reg_name . '_' . date('Ymd', $date) . '_' . $time,
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
        
        die(json_encode($result));
    }
}
