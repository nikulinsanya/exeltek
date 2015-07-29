<?php defined('SYSPATH') or die('No direct script access.');

class Controller_TimeMachine extends Controller
{
    public function before() {
        if (!Group::current('time_machine')) throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index() {
        $id = strval(Arr::get($_GET, 'id'));

        $job = Database_Mongo::collection('jobs')->findOne(array('_id' => $id), array('_id' => 1));

        if (!$job) throw new HTTP_Exception_404('Not found');

        $point = strval(Arr::get($_GET, 'point'));

        $archive = Database_Mongo::collection('archive')->findOne(array('_id' => new MongoId($point)), array('update_time' => 1));

        if (!$archive) throw new HTTP_Exception_404('Not found');

        $result = Database_Mongo::collection('archive')->find(array('job_key' => $id, 'update_time' => array('$gt' => $archive['update_time'])))->sort(array('update_time' => -1));

        $new = array();

        $ids = array();

        foreach ($result as $item) {
            $ids[] = $item['_id'];
            foreach (Arr::get($item, 'data', array()) as $key => $data) {
                $job['data'][$key] = $data['old_value'];

                if ($data['old_value'])
                    $new['$set']['data.' . $key] = $data['old_value'];
                else
                    $new['$unset']['data.' . $key] = 1;
            }
        }

        if ($new) $new['$set']['last_update'] = $archive['update_time'];

        Database_Mongo::collection('jobs')->update(array('_id' => $id), $new);
        Database_Mongo::collection('archive')->remove(array('_id' => array('$in' => $ids)));

        Messages::save('Ticket ' . $id . ' successfully rolled back to ' . date('d-m-Y H:i', $archive['update_time']) . '. ' . count($ids) . ' changes were removed.');

        die(json_encode(array('success' => true)));
    }
}