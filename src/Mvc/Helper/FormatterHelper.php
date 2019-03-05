<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-04
 * Time: 11:47 AM
 */

namespace Compose\Mvc\Helper;


class FormatterHelper {

    /**
     * Format currency.
     *
     * @access public
     * @param mixed $amount
     * @return string
     */
    public function currency($amount) : string
    {
        return money_format('%.2n', $amount);
    }


    /**
     * Format number.
     *
     * @access public
     * @param mixed $number
     * @return string
     */
    public function number($number, $decimals = 2)  :string
    {
        return number_format($number, $decimals);
    }

    /**
     * dateFormat function.
     *
     * @access public
     * @param mixed $date
     * @param string $format
     * @param string $default
     * @return string
     */
    public function date($date, string $format = null, string $default = 'n/a') : string
    {
        if(!$date) return $default;

        if(is_string($date)) {
            $date = strtotime($date);
        }

        if($date < 0) {
            return $default;
        }

        if(!$format) {
            $format = 'Y-m-d';
        }

        return date($format, $date);
    }

    public function camelToSpace($camel, $glue = ' ')
    {
        return preg_replace( '/([a-z0-9])([A-Z])/', "$1$glue$2", $camel );
    }


    /**
     *
     * @param int $ptime
     * @return string
     */
    public function timeToString(int $ptime) : string
    {
        $etime = time() - $ptime;
        if ($etime < 1) {
            return '0 seconds';
        }
        $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            7  * 24 * 60 * 60       => 'week',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );
        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '');
            }
        }
    }

    /**
     * Convert time to MySQL data/time string format
     * @param int|string $time
     * @return string
     */
    public function toMySqlDateTime($time) : string
    {
        if(!is_int($time)) $time = strtotime($time);
        return date("Y-m-d H:i:s", $time);
    }

    /**
     * @param $size
     * @param int $precision
     * @return string
     */
    function fileSize($size, $precision = 2) {
        $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).$units[$i];
    }
}