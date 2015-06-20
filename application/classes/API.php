<?php defined('SYSPATH') or die('No direct script access.');

class API {

    const LIFETIME = 86400;
    const GC_FACTOR = 100;

    private static $user_id = false;

    static public function user() {
        return self::check(Arr::get($_REQUEST, 'token'));
    }

    static public function check($token) {
        if (!$token) return false;

        if (!self::$user_id) {

            if (rand(0, self::GC_FACTOR) === 0) {
                DB::delete('api_tokens')->where('last_active', '<', time() - self::LIFETIME)->execute();
            }

            self::$user_id = DB::select('user_id')->from('api_tokens')->where('token', '=', $token)->and_where('last_active', '>=', time() - self::LIFETIME)->execute()->get('user_id');

            if (self::$user_id)
                DB::update('api_tokens')->set(array('last_active' => time()))->where('token', '=', $token)->execute();
        }

        return self::$user_id;
    }

    static public function start($user_id) {
        $fl = 10;
        while ($fl) {
            try {
                $token = Text::random('alnum', 32);
                DB::insert('api_tokens', array('user_id', 'token', 'last_active'))->values(array($user_id, $token, time()))->execute();
                return $token;
            } catch (Exception $e) {
                $fl--;
            }
        }
        return false;
    }
}