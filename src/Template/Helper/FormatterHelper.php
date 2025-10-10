<?php

namespace Compose\Template\Helper;

class FormatterHelper
{
    public function __invoke(...$args)
    {
        return $this;
    }

    public function currency(float $amount, string $locale = 'en_US', string $currency = 'USD'): string
    {
        if (class_exists('NumberFormatter')) {
            $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            return $fmt->formatCurrency($amount, $currency);
        }
        return number_format($amount, 2) . ' ' . $currency;
    }

    public function number(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals);
    }

    public function date($date, string $format = 'Y-m-d', string $default = 'n/a'): string
    {
        if (!$date) {
            return $default;
        }
        try {
            if ($date instanceof \DateTimeInterface) {
                return $date->format($format);
            }
            if (is_numeric($date)) {
                $dt = (new \DateTimeImmutable())->setTimestamp((int)$date);
            } else {
                $dt = new \DateTimeImmutable($date);
            }
            return $dt->format($format);
        } catch (\Exception $e) {
            return $default;
        }
    }

    public function camelToSpace(string $camel, string $glue = ' '): string
    {
        return preg_replace('/([a-z0-9])([A-Z])/', "$1$glue$2", $camel);
    }

    public function timeToString(int $ptime): string
    {
        $etime = time() - $ptime;
        if ($etime < 1) {
            return '0 seconds';
        }

        $units = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60       => 'month',
            7  * 24 * 60 * 60       => 'week',
            24 * 60 * 60            => 'day',
            60 * 60                 => 'hour',
            60                      => 'minute',
            1                       => 'second',
        ];

        foreach ($units as $secs => $label) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . $label . ($r > 1 ? 's' : '');
            }
        }

        return '0 seconds';
    }

    public function fileSize($size, int $precision = 2): string
    {
        if (!is_numeric($size) || $size < 0) {
            return '0 B';
        }
        $units = ['B','KB','MB','GB','TB','PB','EB','ZB','YB'];
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9 && $i < count($units) - 1) {
            $size /= $step;
            $i++;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }
}
