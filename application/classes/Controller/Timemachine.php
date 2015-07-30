<?php defined('SYSPATH') or die('No direct script access.');

class Controller_TimeMachine extends Controller
{
    public function before() {
        if (!Group::current('time_machine')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $id = strval(Arr::get($_GET, 'id'));

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $id), array('data' => 1));

        if (!$job) throw new HTTP_Exception_404('Not found');

        $point = strval(Arr::get($_GET, 'point'));

        $archive = Database_Mongo::collection('archive')->findOne(array('_id' => new MongoId($point)), array('update_time' => 1));

        if (!$archive) throw new HTTP_Exception_404('Not found');

        $result = Database_Mongo::collection('archive')->find(array('job_key' => $id, 'update_time' => array('$gt' => $archive['update_time'])))->sort(array('update_time' => -1));

        $ids = array();

        $values = array();

        foreach ($result as $item) {
            $ids[] = $item['_id'];
            foreach (Arr::get($item, 'data', array()) as $key => $data)
                $job['data'][$key] = $values[$key] = $data['old_value'];

        }

        foreach ($values as $key => $value)
                if ($value)
                    $new['$set']['data.' . $key] = $value;
                else
                    $new['$unset']['data.' . $key] = 1;
            }
        }

        if ($new) $new['$set']['last_update'] = $archive['update_time'];

        $submissions = array();
        $result = Database_Mongo::collection('submissions')->find(array('job_key' => $id, 'process_time' => array('$gt' => $archive['update_time'])));

        foreach ($result as $item) if (Arr::path($job, $item['key'], '') != $item['value'])
            $submissions[] = $item['_id'];

        if ($new) {
            Database_Mongo::collection('submissions')->update(array('_id' => array('$in' => $submissions)), array('$unset' => array('process_time' => 1), '$set' => array('active' => 1)));
            Database_Mongo::collection('jobs')->update(array('_id' => $id), $new);
            Database_Mongo::collection('archive')->remove(array('_id' => array('$in' => $ids)));
        }

        $message = '';
        if (count($ids)) $message .= count($ids) . ' changes were removed. ';
        if (count($submissions)) $message .= count($submissions) . ' submissions were unapproved. ';
        Messages::save('Ticket ' . $id . ' successfully rolled back to ' . date('d-m-Y H:i', $archive['update_time']) . '. ' . $message);

        die(json_encode(array('success' => true)));
    }
}