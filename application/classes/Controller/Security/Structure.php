<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Enums extends Controller
{

    public function before()
    {
        parent::before();

        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_index()
    {
    }
}