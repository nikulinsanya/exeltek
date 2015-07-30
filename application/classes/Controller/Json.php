<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Json extends Controller {

    public function before()
    {
        if (!$this->request->is_ajax() && Kohana::$environment != Kohana::DEVELOPMENT)
            throw new HTTP_Exception_403('Forbidden');
    }

    public function action_fsa() {
        $query = array();
        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company')) {
                $company = is_array($_GET['company']) ? $_GET['company'] : explode(',', $_GET['company']);
                $company = array_map('intval', $company);
                if (count($company) == 1) $company = array_shift($company);
                $query['$or'] = array(
                    array('companies' => is_array($company) ? array('$in' => $company) : $company),
                    array('ex' => is_array($company) ? array('$in' => $company) : $company),
                );
            }
            if (Arr::get($_GET, 'region'))
                $query['region'] = strval($_GET['region']);
        }

        $list = Database_Mongo::collection('jobs')->distinct('data.12', $query ? : NULL);

        sort($list);

        die(json_encode($list));
    }

    public function action_fsam()
    {
        $query = array();
        if (!Group::current('allow_assign')) {
            $query['$or'] = array(
                array('companies' => intval(User::current('company_id'))),
                array('ex' => intval(User::current('company_id'))),
            );
        } else {
            if (Arr::get($_GET, 'company')) {
                $company = is_array($_GET['company']) ? $_GET['company'] : explode(',', $_GET['company']);
                $company = array_map('intval', $company);
                if (count($company) == 1) $company = array_shift($company);
                $query['$or'] = array(
                    array('companies' => is_array($company) ? array('$in' => $company) : $company),
                    array('ex' => is_array($company) ? array('$in' => $company) : $company),
                );
            }
            if (Arr::get($_GET, 'region'))
                $query['region'] = strval($_GET['region']);
        }

        if (Arr::get($_GET, 'fsa')) {
            $fsa = is_array($_GET['fsa']) ? array_map('strval', $_GET['fsa']) : explode(',', $_GET['fsa']);
            $query['data.12'] = count($fsa) == 1 ? array_shift($fsa) : array('$in' => $fsa);
        }

        $list = Database_Mongo::collection('jobs')->distinct('data.13', $query ?: NULL);

        sort($list);

        die(json_encode($list));
    }
}
