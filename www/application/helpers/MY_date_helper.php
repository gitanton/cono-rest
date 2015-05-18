<?php
function server_datetime($date, $time) {
    if(get_user()->timezone) {
        date_default_timezone_set(get_user()->timezone);
    }
    $datetime = new DateTime($date." ".$time);
    $datetime->setTimezone(new DateTimeZone(getenv('TZ')));
    return $datetime;
}

function localize_datetime($date, $time, $user ='') {
    if(!$user) {
        $user = get_user();
    }
    date_default_timezone_set(getenv('TZ'));
    $datetime = new DateTime($date." ".$time);
    if(isset($user->timezone)) {
        $datetime->setTimezone(new DateTimeZone($user->timezone));
    }
    return $datetime;
}

function current_time($timezone, $daylight_saving)
{
    $time = time();
    $gmt = local_to_gmt($time);
    $local = gmt_to_local($gmt, $timezone, $daylight_saving);
    return standard_date("DATE_RFC822", $local);
}

function pretty_date_time($time)
{
    if (!$time || $time == '0000-00-00' || $time == '0000-00-00 00:00:00') {
        return "";
    }
    return date('F j, Y H:i:s A T', strtotime($time));
}

function pretty_date_time_short($time)
{
    if (!$time || $time == '0000-00-00' || $time == '0000-00-00 00:00:00') {
        return "";
    }
    return date('m/d/Y h:i A T', strtotime($time));
}

function pretty_date_time_shorter($time)
{
    if (!$time || $time == '0000-00-00' || $time == '0000-00-00 00:00:00') {
        return "";
    }
    return date('m/d/Y g:i A', strtotime($time));
}

function pretty_date_short($time)
{
    if (!$time || $time == '0000-00-00' || $time == '0000-00-00 00:00:00') {
        return "";
    }
    return date('m/d/Y', strtotime($time));
}

function pretty_date($time)
{
    if (!$time || $time == '0000-00-00' || $time == '0000-00-00 00:00:00') {
        return "";
    }
    return date('F j, Y', strtotime($time));
}

function mysql_date($date)
{
    if (!$date || strtotime($date) === FALSE) {
        return "";
    }
    $date = date('Y-m-d', strtotime($date));

    if ($date == '0000-00-00' || $date == '1969-12-31' || $date == '0000-00-00 00:00:00')
        return "";

    return $date;
}

function mysql_time($time)
{
    if (!$time || strtotime($time) === FALSE) {
        return "";
    }
    $time = date('h:i:s', strtotime($time));

    if ($time == '00:00:00' || $time == '' || $time == '0000-00-00 00:00:00')
        return "";

    return $time;
}

function form_date($date)
{
    if (!$date || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return "";
    }
    return date('m/d/Y', strtotime($date));
}

function pretty_time($time)
{
    if (!$time) {
        return "";
    }
    return date('h:i A', strtotime($time));

}

function pretty_time_short($time)
{
    if (!$time) {
        return "";
    }
    return date('h:i', strtotime($time));

}

function add_day($days_to_add, $date = '')
{
    if ($date) {
        $date = strtotime($date);
    } else {
        $date = time();
    }
    $newdate = strtotime($days_to_add . ' day', $date);
    //echo $newdate."<br/>";
    //echo date("Y-m-d", $newdate)."<br/>";
    return $newdate;
}

function add_date($date_to_add)
{
    $newdate = strtotime($date_to_add, time());
    return $newdate;
}

function between_dates($start, $end)
{
    return (strtotime($start) < time() && strtotime($end) >= time());
}

function diff_date($start, $end)
{
    $diff = abs(strtotime($end) - strtotime($start));
    $years = floor($diff / (365 * 60 * 60 * 24));
    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
    //$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
    $days = floor($diff / (60 * 60 * 24));
    return $days;
}

function mysqldatetime_to_timestamp($datetime = "")
{
    // function is only applicable for valid MySQL DATETIME (19 characters) and DATE (10 characters)
    $l = strlen($datetime);
    if (!($l == 10 || $l == 19))
        return 0;

    //
    $date = $datetime;
    $hours = 0;
    $minutes = 0;
    $seconds = 0;

    // DATETIME only
    if ($l == 19) {
        list($date, $time) = explode(" ", $datetime);
        list($hours, $minutes, $seconds) = explode(":", $time);
    }

    list($year, $month, $day) = explode("-", $date);

    return mktime($hours, $minutes, $seconds, $month, $day, $year);
}

function mysqldatetime_to_date($datetime = "", $format = "d.m.Y, H:i:s")
{
    return date($format, mysqldatetime_to_timestamp($datetime));
}

function timestamp_to_mysqldatetime($timestamp = "", $datetime = TRUE)
{
    if (empty($timestamp) || !is_numeric($timestamp)) $timestamp = time();

    return ($datetime) ? date("Y-m-d H:i:s", $timestamp) : date("Y-m-d", $timestamp);
}

function timestamp_to_mysqldate($timestamp = "", $datetime = TRUE)
{
    if (empty($timestamp) || !is_numeric($timestamp)) $timestamp = time();

    return ($datetime) ? date("Y-m-d", $timestamp) : date("Y-m-d", $timestamp);
}

function timestamp_to_mysqltime($timestamp = "", $datetime = TRUE)
{
    if (empty($timestamp) || !is_numeric($timestamp)) $timestamp = time();

    return ($datetime) ? date("H:i:s", $timestamp) : date("Y-m-d", $timestamp);
}



function pretty_twitter_date($timestamp)
{
// Current timestamp
    $now = time();

// Difference from given timestamp
    $difference = $now - $timestamp;

// If less than one hour (59 minutes and 30 seconds, to be precise), we count minutes
    if($difference < 3570)
    {
        $output = round($difference / 60).' minutes';
    }
// If less than 23 hours 59 minutes and 30 seconds, we count hours
    elseif ($difference < 86370)
    {
        $output = round($difference / 3600).' hours';
    }
// If less than 6 days 23 hours 59 minutes and 30 seconds, we count days
    elseif ($difference < 604770)
    {
        $output = round($difference / 86400).' days';
    }
// If less than 164 days 23 hours 59 minutes and 30 seconds, we count weeks
    elseif ($difference < 31535970)
    {
        $output = round($difference / 604770).' weeks';
    }
// Else we count years
    else
    {
        $output = round($difference / 31536000).' years';
    }

    return $output;
}

function get_month_occurrence_day($date = '')
{
    if (!$date)
        $date = now();

    $month = date("m", $date);
    $temp_month = date("m", $date);
    $i = 0;
    do {
        $i++;
        $new_date = strtotime('-' . $i . ' week', $date);
        $temp_month = date("m", $new_date);
    } while ($month == $temp_month);

    return $i;
}

?>