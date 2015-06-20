<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Defects_Upload extends Controller {

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
        
        $view = View::factory("Defects/Upload");
        
        $this->response->body($view);
    }

    public function action_start()
    {
        ini_set("memory_limit", "512M");
        
        define('PRIMARY_KEY', 'Ticket_Of_Work');
        
        $filename = $this->request->param('id');
        
        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }
        
        $region = false;
        if (preg_match('/defects([A-Z]*)/i', $filename, $matches))
            $region = DB::select()->from('regions')->where('name', '=', $matches[1])->execute()->get('id');

        if (!$region) {
            unlink(DOCROOT . 'files/' . $filename);

            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Wrong file name - unable to determine region. Import aborted! Proper file name format: DDMMYYYY_EXEL_ZZZ_TTT.csv, ZZZ - State, TTT - SOD or EOD',
            )));
        }
        
        $file = fopen(DOCROOT . 'files/' . $filename, "r");
        $headers = fgetcsv($file);
        $comments = array();
        $used = array();
        foreach ($headers as $id => $header) {
            $comments[$id] = trim(preg_replace("/ +/", ' ', str_replace(array("\n", "\r"), '', $header)));
            $header = preg_replace("/[^a-z0-9]/i", ' ', $header);
            $header = ucwords(strtolower($header));
            $header = trim(preg_replace("/ +/", '_', $header), '_');
            if (isset($used[$header])) {
                $i = 1;
                while (1) {
                    $i++;
                    if (!isset($used[$header . '_' . $i])) {
                        $header .= '_' . $i;
                        break;
                    }
                }
            }
            $headers[$id] = $header;
            $used[$header] = $id;
        }

        $dates = array();
        $extra = array();

        $exclude = array('Region');
        
        $result = DB::select('COLUMN_NAME', 'DATA_TYPE')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', '=', 'defects')
            ->and_where('COLUMN_NAME', 'NOT IN', $exclude)
            ->execute()->as_array('COLUMN_NAME', 'DATA_TYPE');

        $size = DB::select('COLUMN_NAME', 'CHARACTER_MAXIMUM_LENGTH')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', '=', 'defects')
            ->and_where('COLUMN_NAME', 'NOT IN', $exclude)
            ->execute()->as_array('COLUMN_NAME', 'CHARACTER_MAXIMUM_LENGTH');
        foreach ($result as $column => $type) {
            if (isset($used[$column])) {
                if ($type == 'date')
                    $dates[$used[$column]] = $column;

                unset($used[$column]);
            } else {
                $extra[] = $column;
            }
        }
        if ($extra || $used) {
            if (count($extra) == count($used)) {
                if (!isset($_GET['replace'])) {
                    $list = array();
                    foreach ($used as $column => $id) {
                        $list[] = array_shift($extra) . ' => ' . $column;
                    }
                    $forced = array(
                        'link' => URL::base() . $this->request->uri() . URL::query(array('replace' => 1)),
                        'message' => "Warning! Some fields were renamed:\n" . implode("\n", $list) . "\n" .
                            'Do you want to force import with replacing renamed fields with original names?',
                    );
                }
            } else {
                unlink(DOCROOT . 'files/' . $filename);
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
        
        die(json_encode(array(
            'success' => true,
            'filename' => $filename,
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
        
        define('PRIMARY_KEY', 'Id');
        
        $filename = $this->request->param('id');
        
        if (!$filename || !file_exists(DOCROOT . 'files/' . $filename)) {
            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Import aborted - file not found!',
            )));
        }
        
        $region = false;
        if (preg_match('/defects([A-Z]*)/i', $filename, $matches)) {
            $reg_code = $matches[1];
            $region = DB::select()->from('regions')->where('name', '=', $reg_code)->execute()->get('id');
        }

        if (!$region) {
            unlink(DOCROOT . 'files/' . $filename);

            die(json_encode(array(
                'success' => true,
                'error' => 'Warning: Wrong file name - unable to determine region. Import aborted! Proper file name format: DDMMYYYY_EXEL_ZZZ_TTT.csv, ZZZ - State, TTT - SOD or EOD',
            )));
        }
        
        $file = fopen(DOCROOT . 'files/' . $filename, "r");
        $headers = fgetcsv($file);
        $used = array();
        foreach ($headers as $id => $header) {
            $header = preg_replace("/[^a-z0-9]/i", ' ', $header);
            $header = ucwords(strtolower($header));
            $header = trim(preg_replace("/ +/", '_', $header), '_');
            if (isset($used[$header])) {
                $i = 1;
                while (1) {
                    $i++;
                    if (!isset($used[$header . '_' . $i])) {
                        $header .= '_' . $i;
                        break;
                    }
                }
            }
            if (strlen($header) > 64) $header = substr($header, 0, 64);
            $headers[$id] = $header;
            $used[$header] = $id;
        }
        
        $dates = array();
        $extra = array();

        $exclude = array('Region');
        
        $result = DB::select('COLUMN_NAME', 'DATA_TYPE')
            ->from('information_schema.COLUMNS')
            ->where('TABLE_NAME', '=', 'defects')
            ->and_where('COLUMN_NAME', 'NOT IN', $exclude)
            ->execute()->as_array('COLUMN_NAME', 'DATA_TYPE');
        foreach ($result as $column => $type) {
            if (isset($used[$column])) {
                if ($type == 'datetime')
                    $dates[$used[$column]] = $column;
                unset($used[$column]);
            } else {
                $extra[] = $column;
            }
        }
        if ($extra || $used) {
            if (count($extra) == count($used)) {
                
                foreach ($used as $column => $id)
                    $headers[$id] = array_shift($extra);
            } else {
                unlink(DOCROOT . 'files/' . $filename);
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
        
        fseek($file, $pos);
        
        $start = microtime(true);
        $new = 0;
        $updated = 0;
        $skipped = 0;

        $ids = DB::select(PRIMARY_KEY, DB::expr('1 as v'))
            ->from('defects')
            ->where('Region', '=', $region)
            ->execute()->as_array(PRIMARY_KEY, 'v');
            
        $used = Session::instance()->get($filename, array());
            
        $row = fgetcsv($file);
        $values = array();
        $existing = array();
        $updates = array();
        $archive = array();
        
        $archive_headers = $headers;
        $archive_headers[] = 'filename';
        $archive_headers[] = 'update_type';
        
        $insert_headers = array_merge($headers, $exclude);
        
        while ($row) {
            foreach ($row as $id => $value) if (!$value) $row[$id] = NULL;
            
            foreach ($dates as $id => $name) {
                $time = strtotime(str_replace('/', '-', $row[$id]));
                $row[$id] = $time ? date("Y-m-d H:i:s", $time) : NULL;
            }
            
            $id = $row[0];
            
            if (isset($ids[$id])) {
                $existing[$id] = array_combine($headers, $row);
            } else {
                $values[] = $row;
            }
            $used[$id] = 1;
            
            if (microtime(true) - $start > 1) {
                $pos = ftell($file);
                $row = false;
            } else {
                $row = fgetcsv($file);
            }
            
            if (count($existing) >= 500 || (!$row && $existing)) {
                $stored = DB::select()->from('defects')->where(PRIMARY_KEY, 'IN', array_keys($existing))->execute();
                while ($stored->current()) {
                    $value = $stored->current();
                    $key = $value[PRIMARY_KEY];
                    $diff = array_diff_assoc($existing[$key], $value);
                    if ($diff) {
                        //Log::instance()->add(Log::INFO, print_r($diff, true));
                        //Log::instance()->add(Log::INFO, print_r(array_diff_assoc($value, $existing[$key]), true));
                        $value = array();
                        foreach ($diff as $id => $val) {
                            $value[$id] = $val;
                        }
                        $updates[$key] = $value;
                        $value = $existing[$key];
                        $value['Id'] = $reg_code . '/' . $value['Id'];
                        $value[] = $filename;
                        $value[] = 2;
                        $archive[] = $value;
                    } else $skipped++;
                    $stored->next();
                }
                $existing = array();
            }
            if (count($values) >= 500 || (!$row && $values)) {
                Database::instance()->begin();
                $query = DB::insert('defects', array_values($insert_headers));
                $a_query = DB::insert('defects_archive', array_values($archive_headers));
                foreach ($values as $value) {
                    array_push($value, $region);
                    $query->values($value);
                    array_pop($value);
                    $value[0] = $reg_code . '/' . $value[0];
                    $value[] = $filename;
                    $value[] = 1;
                    $a_query->values($value);
                }
                    
                $result = $query->execute();
                $a_query->execute();
                Database::instance()->commit();
                $values = array();
                $new += Arr::get($result, 1, 0);

            }
            if (count($updates) >= 500 || (!$row && $updates)) {
                Database::instance()->begin();
                foreach ($updates as $id => $value) {
                    DB::update('defects')
                        ->set($value)
                        ->where(PRIMARY_KEY, '=', $id)
                        ->and_where('Region', '=', $region)
                        ->execute();
                }
                $a_query = DB::insert('defects_archive', array_values($archive_headers));
                foreach ($archive as $value)
                    $a_query->values($value);
                    
                $result = $a_query->execute();
                Database::instance()->commit();
                
                $updated += Arr::get($result, 1, 0);
                
                $updates = array();
                $archive = array();
            }
            if (!$row) break;
        }
        
        Session::instance()->set($filename, $used);
        
        $finished = feof($file) ? 1 : 0;
        if ($finished) {
            $deleted = array_diff_key($ids, $used);
            if ($deleted) {
                $result = DB::select(PRIMARY_KEY, 'update_type', 'update_time')
                    ->from('defects_archive')
                    ->where(PRIMARY_KEY, 'IN', array_keys($deleted))
                    ->and_where('Region', '=', $region)
                    ->execute()->as_array();
                    
                $list = array();
                foreach ($result as $value) {
                    if (!isset($list[$value[PRIMARY_KEY]])) {
                        $list[$value[PRIMARY_KEY]] = array(
                            'date' => $value['update_time'],
                            'state' => $value['update_type'],
                        );
                    } elseif ($list[$value[PRIMARY_KEY]]['date'] < $value['update_time'])
                        $list[$value[PRIMARY_KEY]]['state'] = $value['update_type'];
                }
                Database::instance()->begin();
                $cnt = 0;
                foreach ($deleted as $id => $v) if (Arr::path($list, array($id, 'state'), 0) != 3) {
                    $cnt++;
                    $values = array(
                        PRIMARY_KEY => $id,
                        'Region' => $region,
                        'update_type' => 3,
                        'filename' => $filename,
                    );
                    DB::insert('defects_archive', array_keys($values))
                        ->values(array_values($values))
                        ->execute();
                }
                $deleted = $cnt;
                Database::instance()->commit();
            } else $deleted = 0;
            
            Session::instance()->delete($filename);
            unlink(DOCROOT . 'files/' . $filename);
        } else $deleted = 0;;

        $result = array(        
            'success' => true,
            'time' => microtime(true) - $start,
            'memory' => memory_get_peak_usage(true),
            'inserted' => $new,
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
