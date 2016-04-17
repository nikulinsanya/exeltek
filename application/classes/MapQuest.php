<?php defined('SYSPATH') or die('No direct script access.');

class MapQuest
{
    private static $config = false;

    public static function locate($address) {
        if (!self::$config)
            self::$config = Kohana::$config->load('mapquest');

        if (!is_array($address))
            $address = array($address);

        $address = array_map(function($value) { return trim(preg_replace('/^.*unit [0-9]+,/i', '', str_replace("\n", ', ', $value))); }, $address);

        reset($address);
        $list = array();
        $response = array();
        while ($item = current($address)) {
            $list[$item] = key($address);

            $state = substr($item, strrpos($item, ',') + 1);

            $item = array('country' => 'AU', 'street' => $item);
            if ($state) $item['state'] = $state;

            $data[] = $item;

            next($address);

            if ((!key($address) && $list) || count($list) >= self::$config->get('batch_size')) {

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $url = self::$config->get('service_url') . '?key=' . self::$config->get('api_key');
                curl_setopt($curl, CURLOPT_URL, $url);

                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('locations' => $data)));

                $result = curl_exec($curl);

                curl_close($curl);

                $result = json_decode($result, true);

                try {
                    foreach ($result['results'] as $item) {
                        $key = Arr::get($list, Arr::path($item, 'providedLocation.street'));
                        if ($key === NULL) continue;
                        $location = array_shift($item['locations']);
                        $response[$key] = Arr::get($location, 'latLng');
                    }
                } catch (Exception $e) {
                }

                $list = array();
                $data = array();
            }
        }

        if (count($address) == 1 && isset($address[0]))
            return $response[0];
        else
            return $response;
    }

    public static function parse($address) {
        if (preg_match('/^((unit|whse|lot|shop|ant|bldg|shed|site|hall|dupl|tncy|se|offc|fcty|room[ a-z]*|hse) [0-9a-z-]+,? )*(\d[-0-9a-z]* )?([a-z- \']+),?(\r\n|\n)([a-z ]+), ([a-z]+)( \d+)?$/msi', trim($address), $matches)) {
            $matches = array_map('trim', $matches);
            $address = array(
                'state' => $matches[7],
                'city' => $matches[6],
                'street' => $matches[4],
                'number' => intval($matches[3]),
                'house' => $matches[3],
                'extra' => $matches[1],
            );
        } elseif (preg_match('/^([0-9a-z-]+) ((the |rue )?[a-z]+) ([a-z ]+), ([a-z]+)$/msi', trim($address), $matches)) {
            $matches = array_map('trim', $matches);
            $address = array(
                'state' => $matches[5],
                'city' => $matches[4],
                'street' => $matches[2],
                'number' => intval($matches[1]),
                'house' => $matches[1],
            );
        }
        return $address;
    }
}
