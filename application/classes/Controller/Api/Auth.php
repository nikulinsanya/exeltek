<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Auth extends Kohana_Controller {

    public function action_index() {
        $login = Arr::get($_REQUEST, 'login');
        $passw = Arr::get($_REQUEST, 'passw');

        $passw = Auth::instance()->hash($passw);

        $user_id = DB::select('id')->from('users')->where('login', '=', $login)->and_where('passw', '=', $passw)->execute()->get('id');

        if ($user_id) {
            $token = API::start($user_id);
            die(json_encode(array(
                'success' => true,
                'token' => $token,
            )));
        } else
            die(json_encode(array('success' => false, 'error' => 'forbidden')));
    }

    public function action_check() {
        $token = Arr::get($_REQUEST, 'token');

        if (API::check($token))
            die(json_encode(array('success' => 'true')));
        else
            die(json_encode(array('success' => false, 'error' => 'forbidden')));
    }
}