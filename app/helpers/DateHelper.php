<?php

class DateHelper
{
    public static function format($date, $format = 'd M Y H:i')
    {
        return date($format, strtotime($date));
    }

    public static function age($birthdate)
    {
        $from = new DateTime($birthdate);
        $to = new DateTime('today');
        return $from->diff($to)->y;
    }

    public static function diffHuman($date)
    {
        $diff = time() - strtotime($date);
        if ($diff < 60) return $diff . ' detik lalu';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        return floor($diff / 86400) . ' hari lalu';
    }

    public static function toMysql($date)
    {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public static function isWeekend($date)
    {
        $day = date('N', strtotime($date));
        return ($day == 6 || $day == 7);
    }
}
