<?php defined('SYSPATH') or die('No direct script access.');

class Utils {

    private static $holidays = false;

    public static function working_days($date) {
        $endDate = strtotime('midnight');
        $startDate = intval($date);

        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = abs(floor(($startDate - $endDate) / 86400)) + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
            $workingDays += $no_remaining_days;
        }

        if (self::$holidays === false)
            self::$holidays = DB::select('time')->from('holidays')->execute()->as_array(NULL, 'time');

        //We subtract the holidays
        foreach(self::$holidays as $holiday){
            //If the holiday doesn't fall in weekend
            if ($startDate <= $holiday && $holiday <= $endDate && date("N", $holiday) != 6 && date("N", $holiday) != 7)
                $workingDays--;
        }

        return $workingDays;
    }

    public static function count($array, $depth = 0) {
        if ($depth && is_array($array)) {
            $total = 0;
            foreach ($array as $sub)
                $total += self::count($sub, $depth - 1);
            return $total;
        } else return count($array);
    }

    public static function calculate_financial($job) {
        $columns = array_map(function($val) { return 'data.' . $val; }, array_keys(Columns::get_financial()));

        $submissions = Database_Mongo::collection('submissions')->find(array('job_key' => $job['_id'], 'key' => array('$in' => $columns)))->sort(array('update_time' => -1));

        $columns = Columns::get_financial();

        $data = array(
            'rev' => 0,
            'ecost' => 0,
            'acost' => 0,
        );

        $rates = array();

        $result = DB::select()->from('rates')->execute();

        foreach ($result as $row)
            $rates[$row['company_id']][$row['region_id']][$row['column_id']] = $row['rate'];

        $companies = DB::select('id', 'company_id')->from('users')->execute()->as_array('id', 'company_id');

        $client = array();
        foreach ($columns as $column => $max)
            $client[$column] = isset($rates[0][$job['region']][$column]) ? $rates[0][$job['region']][$column] : (isset($rates[0][0][$column]) ? $rates[0][0][$column] : 0);

        foreach (Columns::get_financial() as $column => $max)
            $data['rev'] += Arr::path($job, array('data', $column), 0) * $client[$column];

        $used = array();
        foreach ($submissions as $submission) {
            $company = Arr::get($companies, $submission['user_id']);
            if (!$company || !isset($rates[$company])) continue;
            $rate = $rates[$company];

            $key = substr($submission['key'], 5);
            if (isset($used[$key])) continue;
            $used[$key] = true;

            if (isset($submission['paid'])) {
                $data['acost'] += $submission['paid'] * $submission['rate'];
                $data['ecost'] += $submission['paid'] * $submission['rate'];
                continue;
            }

            $rate = isset($rate[$job['region']][$key]) ? $rate[$job['region']][$key] : (isset($rate[0][$key]) ? $rate[0][$key] : 0);

            if (!$rate) continue;

            $value = min(floatval($submission['value']), $columns[$key]);

            $data['ecost'] += $value * $rate;
        }

        $data['egp'] = $data['rev'] - $data['ecost'];
        $data['gp'] = $data['rev'] - $data['acost'];

        $result = array();
        if (Arr::get($job['data'], 258, 0) != $data['rev'])
            $result['$set']['data.258'] = $data['rev'];
        if (Arr::get($job['data'], 259, 0) != $data['ecost'])
            $result['$set']['data.259'] = $data['ecost'];
        if (Arr::get($job['data'], 260, 0) != $data['acost'])
            $result['$set']['data.260'] = $data['acost'];
        if (Arr::get($job['data'], 261, 0) != $data['egp'])
            $result['$set']['data.261'] = $data['egp'];
        if (Arr::get($job['data'], 262, 0) != $data['gp'])
            $result['$set']['data.262'] = $data['gp'];

        $target = DB::select(DB::expr('SUM(`amount`) as total'))->from('payment_jobs')->where('job_key', '=', $job['_id'])->execute()->get('total');

        if ($target) {
            if ($data['acost'] > $target) {
                if (!isset($job['partial'])) $result['$set']['partial'] = 1;
                if (isset($job['paid'])) $result['$unset']['paid'] = 1;
            } else {
                if (isset($job['partial'])) $result['$unset']['partial'] = 1;
                if (!isset($job['paid'])) $result['$set']['paid'] = 1;
            }
        } else {
            if (isset($job['partial'])) $result['$unset']['partial'] = 1;
            if (isset($job['paid'])) $result['$unset']['paid'] = 1;
        }

        if ($result)
            Database_Mongo::collection('jobs')->update(array('_id' => $job['_id']), $result);
    }

    public static function bool_icon($value) {
        return '<span class="glyphicon glyphicon-' . ($value ? 'ok' : 'remove') . ' text-' . ($value ? 'success' : 'danger') . '"></span>';
    }
}