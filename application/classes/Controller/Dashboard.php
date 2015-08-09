<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Dashboard extends Controller {

    private function group_jobs($key, $query) {
        $result = Database_Mongo::collection('jobs')->find($query, array($key => 1, 'data.44' => 1));
        $query = array(
            'data.44' => array('$exists' => 1),
        );
        if (Arr::get($_GET, 'start')) $query['update_time']['$gt'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $query['update_time']['$lt'] = strtotime($_GET['end']) + 86399;
        $total = array();
        $jobs = array();
        $statuses = array();
        foreach ($result as $job) {
            $fsam = Arr::path($job, $key);
            $jobs[$job['_id']] = $fsam;
            $status = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44')));
            if (!isset($query['update_time'])) $statuses[$job['_id']] = $status;
            if (!in_array($status, array('dirty', 'deferred', 'heldnbn')))
                $total[$fsam] = Arr::get($total, $fsam, 0) + 1;
        }
        $query['job_key'] = array('$in' => array_keys($jobs));
        $result = Database_Mongo::collection('archive')->find($query, array('_id' => 0, 'job_key' => 1, 'data.44' => 1, 'update_time' => 1))->sort(array('update_time' => 1));
        foreach ($result as $job)
            $statuses[$job['job_key']] = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44.new_value')));
        $built = array();
        $tested = array();
        foreach ($jobs as $id => $fsam) {
            switch (Arr::get($statuses, $id)) {
                case 'built':
                    $built[$fsam] = Arr::get($built, $fsam) + 1;
                    break;
                case 'tested':
                    $tested[$fsam] = Arr::get($tested, $fsam) + 1;
                    break;
            }
        }
        $fsam = array();
        foreach ($total as $key => $value)
            $fsam[$key ? : 'Unknown'] = array(
                'total' => $value,
                'built' => Arr::get($built, $key, 0),
                'tested' => Arr::get($tested, $key, 0),
            );
        ksort($fsam);
        return $fsam;
    }

    public function action_fsa() {
        $query = array();

        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && isset($companies[$_GET['company']]))
                $query['$or'] = array(
                    array('companies' => intval($_GET['company'])),
                    array('ex' => intval($_GET['company'])),
                );
            if (Arr::get($_GET, 'region') && isset($regions[$_GET['region']]))
                $query['region'] = strval($_GET['region']);
        }

        $result = Database_Mongo::collection('jobs')->find($query, array('data.12' => 1, 'data.13' => 1, 'data.14' => 1, 'data.44' => 1, 'created' => 1));
        $query = array(
            'data.44' => array('$exists' => 1),
        );

        $start = strtotime(Arr::get($_GET, 'start'));
        $end = strtotime(Arr::get($_GET, 'end'));
        if ($end) $end += 86399;

        if ($start) $query['update_time']['$gt'] = $start;
        if ($end) $query['update_time']['$lt'] = $end;

        $jobs = array();
        foreach ($result as $job) {
            $jobs[$job['_id']] = array(
                'fsa' => Arr::path($job, 'data.12', 'Unknown'),
                'fsam' => Arr::path($job, 'data.13', 'Unknown'),
                'fda' => Arr::path($job, 'data.14', 'Unknown'),
                'date' => $job['created'],
                'status' => strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44'))),
            );
        }

        $query['job_key'] = array('$in' => array_keys($jobs));

        $result = Database_Mongo::collection('archive')->find($query, array('_id' => 0, 'job_key' => 1, 'data.44' => 1, 'update_time' => 1))->sort(array('update_time' => 1));

        foreach ($result as $job) {
            $jobs[$job['job_key']]['status'] = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44.new_value')));
            $jobs[$job['job_key']]['date'] = $job['update_time'];
        }

        usort($jobs, function($a, $b) {
            return strcmp($a['fsa'], $b['fsa']) ? : (strcmp($a['fsam'], $b['fsam']) ? : strcmp($a['fsam'], $b['fsam']));
        });

        $total = array();
        $fsam = array();
        foreach ($jobs as $job) if ((!$start || $start <= $job['date']) && (!$end || $end >= $job['date'])) {
            $status = $job['status'];
            if (!in_array($status, array('dirty', 'deferred', 'heldnbn', 'built', 'tested')))
                $status = 'total';

            if (in_array($status, array('total', 'built', 'tested'))) {
                $total[$status] = Arr::get($total, $status) + 1;
                $fsam[$job['fsa']]['total'][$status] = Arr::path($fsam, array($job['fsa'], 'total', $status), 0) + 1;
                $fsam[$job['fsa']][$job['fsam']]['total'][$status] = Arr::path($fsam, array($job['fsa'], $job['fsam'], 'total', $status), 0) + 1;
                $fsam[$job['fsa']][$job['fsam']][$job['fda']][$status] = Arr::path($fsam, array($job['fsa'], $job['fsam'], $job['fda'], $status), 0) + 1;

                if ($status != 'total') {
                    $status = 'total';
                    $total[$status] = Arr::get($total, $status) + 1;
                    $fsam[$job['fsa']]['total'][$status] = Arr::path($fsam, array($job['fsa'], 'total', $status), 0) + 1;
                    $fsam[$job['fsa']][$job['fsam']]['total'][$status] = Arr::path($fsam, array($job['fsa'], $job['fsam'], 'total', $status), 0) + 1;
                    $fsam[$job['fsa']][$job['fsam']][$job['fda']][$status] = Arr::path($fsam, array($job['fsa'], $job['fsam'], $job['fda'], $status), 0) + 1;
                }
            }
        }

        $view = View::factory('Dashboard/Fsa')
            ->bind('total', $total)
            ->bind('fsam', $fsam)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_lifd() {
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');


        if ($this->request->is_ajax()) {

            $query = array();

            $url = array();

            if (!Group::current('allow_assign')) {
                $query['$or'] = array(
                    array('companies' => intval(User::current('company_id'))),
                    array('ex' => intval(User::current('company_id'))),
                );
            } else {
                if (Arr::get($_GET, 'company') && is_array($_GET['company'])) {
                    $company = array_map('intval', $_GET['company']);
                    if (count($company) == 1) $company = array_shift($company);
                    $query['$or'] = array(
                        array('companies' => is_array($company) ? array('$in' => $company) : $company),
                        array('ex' => is_array($company) ? array('$in' => $company) : $company),
                    );
                    $url['company'] = is_array($company) ? implode(',', $company) : $company;
                    $url['ex'] = '1';
                }
            }
            if (Arr::get($_GET, 'region') && isset($regions[$_GET['region']])) {
                $query['region'] = strval($_GET['region']);
            }

            if (Arr::get($_GET, 'fsa')) {
                $fsa = is_array($_GET['fsa']) ? array_map('strval', $_GET['fsa']) : explode(', ', $_GET['fsa']);
                $query['data.12'] = count($fsa) > 1 ? array('$in' => $fsa) : array_shift($fsa);
            }

            if (Arr::get($_GET, 'fsam')) {
                $fsam = is_array($_GET['fsam']) ? array_map('strval', $_GET['fsam']) : explode(', ', $_GET['fsam']);
                $query['data.13'] = count($fsam) > 1 ? array('$in' => $fsam) : array_shift($fsam);
            }

            $result = Database_Mongo::collection('jobs')->find($query, array('data.12' => 1, 'data.13' => 1, 'data.14' => 1, 'data.17' => 1, 'data.18' => 1, 'data.44' => 1, 'created' => 1, 'companies' => 1, 'ex' => 1));

            $jobs = array();
            foreach ($result as $job) {
                $jobs[$job['_id']] = array(
                    'fsa' => Arr::path($job, 'data.12', 'Unknown'),
                    'fsam' => Arr::path($job, 'data.13', 'Unknown'),
                    'lifd' => Arr::path($job, 'data.17', 0) . '|' . Arr::path($job, 'data.18', 0),
                    'fda' => Arr::path($job, 'data.14', 'Unknown'),
                    'status' => strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44'))),
                    'companies' => array('now' => Arr::get($job, 'companies', array()), 'ex' => Arr::get($job, 'ex', array())),
                );
            }

            usort($jobs, function ($a, $b) {
                foreach ($a as $key => $v) {
                    $v2 = $b[$key];
                    if ($key == 'lifd') {
                        $v = explode('|', $v);
                        $v2 = explode('|', $v2);
                        if ($v[0] < $v2[0]) return -1;
                        elseif ($v[0] > $v2[0]) return 1;
                        elseif ($v[1] < $v2[1]) return -1;
                        elseif ($v[1] > $v2[1]) return 1;
                    } elseif (!is_array($v)) {
                        if ($v < $v2) return -1;
                        elseif ($v > $v2) return 1;
                    }
                }
                return 0;
            });

            $list = array();

            $total = array();

            foreach ($jobs as $job) {
                $comp = $job['companies'];
                unset($job['companies']);
                Arr::set_path($list, $job, Arr::path($list, $job, 0) + 1);
                $total[$job['fsa']][$job['status']] = Arr::path($total, array($job['fsa'], $job['status'])) + 1;
                $total[$job['fsam']][$job['status']] = Arr::path($total, array($job['fsam'], $job['status'])) + 1;
                $total[$job['fsam'] . $job['lifd']][$job['status']] = Arr::path($total, array($job['fsam'] . $job['lifd'], $job['status'])) + 1;
                $job['status'] = 'companies';
                $job['comp'] = 'now';
                Arr::set_path($list, $job, array_merge(Arr::path($list, $job, array()), $comp['now']));
                $job['comp'] = 'ex';
                Arr::set_path($list, $job, array_merge(Arr::path($list, $job, array()), $comp['ex']));
            }

            $view = View::factory('Dashboard/LifdReport')
                ->set('url', URL::query($url, false))
                ->bind('total', $total)
                ->bind('list', $list)
                ->bind('companies', $companies);

            $filters = array();
            if (Arr::get($_GET, 'region'))
                $filters[] = array('name' => 'Region', 'value' => Arr::get($regions, $_GET['region'], 'Unknown'));

            if (Arr::get($_GET, 'company') && is_array($_GET['company']))
                $filters[] = array('name' => 'Contractor', 'value' => implode(', ', array_intersect_key($companies, array_flip($_GET['company']))));

            die(json_encode(array(
                'filters' => $filters,
                'html' => strval($view),
            )));
        }

        $query = array();

        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        }

        $fsa = Database_Mongo::collection('jobs')->distinct('data.12', $query ? : NULL);
        $fsam = Database_Mongo::collection('jobs')->distinct('data.13', $query ? : NULL);

        sort($fsa);
        sort($fsam);

        $view = View::factory('Dashboard/Lifd')
            ->bind('fsa', $fsa)
            ->bind('fsam', $fsam)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_reports() {
        $companies = Group::current('allow_assign') ? DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name') : array();
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');
        $fsa = Database_Mongo::collection('jobs')->distinct('data.12');
        $fsam = Database_Mongo::collection('jobs')->distinct('data.13');
        $fsa = array_combine($fsa, $fsa);
        $fsam = array_combine($fsam, $fsam);

        $view = View::factory('Dashboard/Reports')
            ->bind('fsa', $fsa)
            ->bind('fsam', $fsam)
            ->bind('companies', $companies)
            ->bind('regions', $regions);

        $this->response->body($view);
    }

    public function action_api() {
        header('Content-type: application/json');

        $type = Arr::get($_GET, 'type');
        $range = array();
        if (Arr::get($_GET, 'start')) $range['$gte'] = strtotime($_GET['start']);
        if (Arr::get($_GET, 'end')) $range['$lte'] = strtotime($_GET['end']) + 86399;

        $separate = strtolower(Arr::get($_GET, 'sep'));

        $filter = array();

        if (!Group::current('allow_assign')) {
            $filter['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company') && is_array($_GET['company'])) {
                $company = array_map('intval', $_GET['company']);
                if (count($company) == 1) $company = array_shift($company);
                $filter['$or'] = array(
                    array('companies' => is_array($company) ? array('$in' => $company) : $company),
                    array('ex' => is_array($company) ? array('$in' => $company) : $company),
                );
            }
        }
        if (Arr::get($_GET, 'region'))
            $filter['region'] = strval($_GET['region']);

        if (Arr::get($_GET, 'fsa')) {
            $fsa = is_array($_GET['fsa']) ? array_map('strval', $_GET['fsa']) : explode(', ', $_GET['fsa']);
            $filter['data.12'] = count($fsa) > 1 ? array('$in' => $fsa) : array_shift($fsa);
        }

        if (Arr::get($_GET, 'fsam')) {
            $fsam = is_array($_GET['fsam']) ? array_map('strval', $_GET['fsam']) : explode(', ', $_GET['fsam']);
            $filter['data.13'] = count($fsam) > 1 ? array('$in' => $fsam) : array_shift($fsam);
        }

        switch ($type) {
            case 'companies':
                $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'companies' => 1, 'ex' => 1));
                $list = array();
                $total = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';

                    $total[$key] = Arr::get($total, $key, 0) + 1;

                    foreach (Arr::get($job, 'companies', array()) as $company) if (Group::current('show_all_jobs') || $company == User::current('company_id'))
                        $list[$company][$key] = Arr::path($list, array($company, $key)) + 1;

                    foreach (Arr::get($job, 'ex', array()) as $company) if (Group::current('show_all_jobs') || $company == User::current('company_id'))
                        $list[$company][$key] = Arr::path($list, array($company, $key)) + 1;
                }
                $result = array();
                foreach ($list as $key => $values)
                    $result[Arr::get($companies, $key, 'Unknown')] = $values;

                ksort($result);

                $list = array(
                    'total' => $total,
                    'companies' => $result,
                );
                break;
            case 'fsa':
                unset($filter['data.12']);
                unset($filter['data.13']);
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'data.12' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.12', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            case 'fsam':
                $fsa = strval(Arr::get($_GET, 'fsa', ''));
                $filter['data.12'] = $fsa;
                unset($filter['data.13']);
                $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'data.13' => 1));
                $list = array();
                foreach ($result as $job) {
                    $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', strtolower(Arr::path($job, 'data.44'))))) ? : 'Unknown';
                    $fsa = Arr::path($job, 'data.13', 'Unknown');
                    $list[$fsa][$key] = Arr::path($list, array($fsa, $key)) + 1;
                }
                break;
            default:
                if ($range || $separate == 'd' || $separate == 'w' || $separate == 'm') {
                    $jobs = array();
                    $result = Database_Mongo::collection('jobs')->find($filter, array('data.44' => 1, 'created' => 1));
                    $ids = array();
                    $start = Arr::get($range, '$gte');
                    $end = Arr::get($range, '$lte');

                    foreach ($result as $job) {
                        if ($filter) $ids[] = $job['_id'];
                        if ((!$start || $start <= $job['created']) && (!$end || $end >= $job['created']))
                            $jobs[$job['_id']] = array(
                                's' => strtolower(Arr::path($job, 'data.44')),
                                't' => $job['created'],
                            );
                    }

                    $filter = array('$match' => array(
                        'fields' => 44,
                    ));
                    if ($range) $filter['$match']['update_time'] = $range;

                    if ($ids) $filter['$match']['job_key'] = array('$in' => $ids);

                    $result = Database_Mongo::collection('archive')->aggregate(array(
                        $filter,
                        array('$sort' => array('update_time' => 1)),
                        array('$group' => array(
                            '_id' => '$job_key',
                            'date' => array('$last' => '$update_time'),
                            'status' => array('$last' => array('$toLower' => '$data.44.new_value')),
                        )),
                    ));

                    foreach ($result['result'] as $item)
                        $jobs[$item['_id']] = array(
                            's' => $item['status'],
                            't' => $item['date'],
                        );

                    $list = array();
                    $date = false;
                    foreach ($jobs as $item) {
                        $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', $item['s']))) ? : 'Unknown';

                        switch ($separate) {
                            case 'm':
                                $date = strtotime(date('01-m-Y', $item['t']));
                                break;
                            case 'w':
                                $date = strtotime('last monday', strtotime('+1 day', strtotime('midnight', $item['t'])));
                                break;
                            case 'd':
                                $date = strtotime('midnight', $item['t']);
                                break;
                            default:
                                $date = false;
                                break;
                        }
                        if ($date)
                            $list[$date][$key] = Arr::path($list, array($date, $key), 0) + 1;
                        else
                            $list[$key] = Arr::get($list, $key, 0) + 1;
                    }
                    if ($date) ksort($list);
                } else {
                    $query = array();
                    if ($filter) $query[] = array('$match' => $filter);
                    $query[] = array('$group' => array(
                            '_id' => array('$toLower' => '$data.44'),
                            'count' => array('$sum' => 1),
                        ));
                    $result = Database_Mongo::collection('jobs')->aggregate($query);
                    $list = array();
                    foreach ($result['result'] as $item) {
                        $key = ucfirst(trim(preg_replace('/(\[.\] )?([a-z-]*)/i', '$2', $item['_id']))) ? : 'Unknown';
                        $count = $item['count'];
                        $list[$key] = Arr::get($list, $key, 0) + $count;
                    }
                }
                break;
        }
        die(json_encode($list));
    }

    public function action_mixed() {
        $query = array();

        $key = false;
        if (Arr::get($_GET, 'fda')) {
            $key = 'data.14';
            $values = $_GET['fda'];
        } elseif (Arr::get($_GET, 'fsam')) {
            $key = 'data.13';
            $values = $_GET['fsam'];
        } elseif (Arr::get($_GET, 'fsa')) {
            $key = 'data.12';
            $values = $_GET['fsa'];
        }

        if ($key) {
            if (!is_array($values))
                $values = explode(',', $values);

            $values = array_map('strval', $values);
            $query[$key] = count($values) > 1 ? array('$in' => $values) : array_shift($values);
        }

        if (Arr::get($_GET, 'region'))
            $query['region'] = $_GET['region'];

        if (Arr::get($_GET, 'company')) {
            $company = $_GET['company'];
            if (!is_array($company))
                $company = explode(',', $company);

            $company = array_map('intval', $company);

            $query['$or'] = array(
                array('companies' => count($company) > 1 ? array('$in' => $company) : array_shift($company)),
                array('ex' => count($company) > 1 ? array('$in' => $company) : array_shift($company)),
            );
        }

        $types = array();

        $result = Database_Mongo::collection('jobs')->find($query, array('data.44' => 1, 'created' => 1, 'data.190' => 1, 'data.191' => 1, 'data.192' => 1));

        $start = Arr::get($_GET, 'start', 0) ? strtotime($_GET['start']) : 0;
        $end = Arr::get($_GET, 'end', 0) ? strtotime($_GET['end']) : 0;

        $jobs = array();
        foreach ($result as $job) {
            $status = strtolower(preg_replace('/[^a-z]/i', '', Arr::path($job, 'data.44')));
            $jobs[$job['_id']] = $status;
            if ($job['created'] >= $start && (!$end || $job['created'] <= $end) && !in_array($status, array('dirty', 'heldnbn', 'deferred'), true)) {
                if (Arr::path($job, 'data.192'))
                    $types[$job['_id']] = 3;
                elseif (Arr::path($job, 'data.191'))
                    $types[$job['_id']] = 2;
                elseif (Arr::path($job, 'data.190'))
                    $types[$job['_id']] = 1;
            }
        }

        $query = array(
            'job_key' => array('$in' => array_keys($jobs)),
            'key' => array('$in' => array('data.190', 'data.191', 'data.192')),
        );

        if ($start)
            $query['update_time']['$gte'] = $start;

        if ($end)
            $query['update_time']['$lte'] = $end;

        $items = Database_Mongo::collection('submissions')->find($query)->sort(array('update_time' => 1));

        foreach ($items as $item)
            switch ($item['key']) {
                case 'data.190':
                    $types[$item['job_key']] = 1;
                    break;
                case 'data.191':
                    $types[$item['job_key']] = 2;
                    break;
                case 'data.192':
                    $types[$item['job_key']] = 3;
                    break;
            }

        $jobs = array_diff_key($jobs, $types);

        $result = array(
            'Type A' => 0,
            'Type B' => 0,
            'Type C' => 0,
            'Not Buildable' => 0,
            'Tickets Left' => 0,
        );

        foreach ($types as $type)
            switch ($type) {
                case 1: $result['Type A']++; break;
                case 2: $result['Type B']++; break;
                case 3: $result['Type C']++; break;
            }

        foreach ($jobs as $status)
            if (in_array($status, array('dirty', 'heldnbn', 'deferred'), true))
                $result['Not Buildable']++;
            elseif (in_array($status, array('scheduled', 'inprogress', 'heldcontractor'), true))
                $result['Tickets Left']++;

        header('Content-type: application/json');
        die(json_encode($result));
    }
}
