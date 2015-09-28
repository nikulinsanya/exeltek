<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Security_Rates extends Controller {

    public function before() {
        parent::before();
        
        if (!Group::current('is_admin'))
            throw new HTTP_Exception_403('Forbidden');
    }
    
    public function action_index()
    {
        $companies = DB::select('id', 'name')->from('companies')->execute()->as_array('id', 'name');
        $columns = DB::select('id', 'name')->from('job_columns')->where('financial', '>', 0)->execute()->as_array('id', 'name');
        $regions = DB::select('id', 'name')->from('regions')->execute()->as_array('id', 'name');

        $rates = array();

        $company = intval(Arr::get($_GET, 'company', -1));
        $region = intval(Arr::get($_GET, 'region'));

        if ($company >= 0)
            $rates = DB::select('column_id', 'rate')->from('rates')->where('company_id', '=', $company)->and_where('region_id', '=', $region)->execute()->as_array('column_id', 'rate');

        $view = View::factory('Security/Rates')
            ->bind('companies', $companies)
            ->bind('regions', $regions)
            ->bind('columns', $columns)
            ->bind('rates', $rates);
            
        $this->response->body($view);
    }
    
    public function action_save() {
        $id = Arr::get($_GET, 'id');
        if (!DB::select('id')->from('job_columns')->where('id', '=', $id)->and_where('financial', '>', 0)->execute()->get('id'))
            throw new HTTP_Exception_403();
            
        $company = Arr::get($_GET, 'company');
        if ($company !== '0' || ($company && !DB::select('id')->from('companies')->where('id', '=', $company)->execute()->get('id')))
            throw new HTTP_Exception_403();

        $region = intval(Arr::get($_GET, 'region'));
        $rate = floatval(Arr::get($_GET, 'rate'));
        if ($rate) {
            DB::query(Database::INSERT,
                DB::expr("INSERT INTO `rates` (`column_id`, `region_id`, `company_id`, `rate`) VALUES (:id, :region, :company, :rate) ON DUPLICATE KEY UPDATE `rate` = :rate",
                    array(
                        ':id' => $id,
                        ':region' => $region,
                        ':company' => $company,
                        ':rate' => $rate,
                    )
                )->compile())->execute();
        } else {
            DB::delete('rates')->where('column_id', '=', $id)->and_where('company_id', '=', $company)->and_where('region_id', '=', $region)->execute();
        }
        
        die(json_encode(array('success' => true)));
    }
    
}
