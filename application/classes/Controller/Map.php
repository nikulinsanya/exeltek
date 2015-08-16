<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Map extends Kohana_Controller {

    public function action_index() {
        $id = Arr::get($_GET, 'id');

        $result = DB::select()->from('maps')->where('map_id', '=', $id)->and_where('expire', '>=', time())->execute()->as_array();

        if (!$result) throw new HTTP_Exception_404('Not found');

        $items = array();
        foreach ($result as $item) {
            $items[] = array(
                'lng' => $item['lng'],
                'lat' => $item['lat'],
                'id' => $item['job_key'],
                'data' => json_decode($item['info'], true),
            );
        }

        header('Content-type: application/json');
        die(json_encode($items));
    }
}