<?php defined('SYSPATH') or die('No direct script access.');

class MapQuest
{
    private static $config = false;

    public static function locate($address) {
        if (!self::$config)
            self::$config = Kohana::$config->load('mapquest');

        if (!is_array($address))
            $address = array($address);

        $address = array_flip(array_map(function($value) { return str_replace("\n", ', ', $value) . ', Australia'; }, $address));

        reset($address);
        $list = array();
        $response = array();
        while (key($address)) {
            $list[key($address)] = current($address);

            next($address);

            if ((!current($address) && $list) || count($list) >= self::$config->get('batch_size')) {
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $url = self::$config->get('service_url') . '?key=' . self::$config->get('api_key') . '&location=' . implode('&location=', array_map('urlencode', array_keys($list)));
                curl_setopt($curl, CURLOPT_URL, $url);

                $result = curl_exec($curl);

                curl_close($curl);

                $result = json_decode($result, true);

                $response = array();

                try {
                    foreach ($result['results'] as $item) {
                        $key = Arr::get($list, Arr::path($item, 'providedLocation.location'));
                        if ($key === NULL) continue;
                        $location = array_shift($item['locations']);
                        $response[$key] = Arr::get($location, 'latLng');
                    }
                } catch (Exception $e) {
                }

                $list = array();
            }
        }

        if (count($address) == 1 && isset($address[0]))
            return $response[0];
        else
            return $response;
    }
}
