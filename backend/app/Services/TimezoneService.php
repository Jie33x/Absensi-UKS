<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    public const TIMEZONE = 'Asia/Makassar'; // WITA (UTC+8)

    /**
     * Dapatkan tanggal dan waktu server resmi dalam zona waktu Asia/Makassar
     */
    public static function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    public static function getTanggal(): string
    {
        return self::now()->format('Y-m-d');
    }

    public static function getWaktu(): string
    {
        return self::now()->format('H:i:s');
    }

    public static function getFormattedIndoDate(?string $dateStr = null): string
    {
        $dt = $dateStr ? Carbon::parse($dateStr, self::TIMEZONE) : self::now();
        $dt->locale('id');
        return $dt->translatedFormat('l, j F Y');
    }
}
