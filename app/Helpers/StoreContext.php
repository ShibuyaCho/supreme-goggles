<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class StoreContext
{
    public static function id(): string
    {
        try {
            $sid = request()->header('X-Store-ID');
            if (!$sid) {
                $ck = request()->cookie('cpos_store_id');
                if ($ck) $sid = $ck;
            }
            $sid = is_string($sid) ? trim($sid) : '';
            $sid = strtolower($sid);
            $sid = preg_replace('/\s+/', '', $sid);
            $sid = preg_replace('/[^a-z0-9_.-]/', '', $sid);
            if ($sid === 'defaultstore' || $sid === '') $sid = 'default';
            return $sid ?: 'default';
        } catch (\Throwable $e) {
            return 'default';
        }
    }

    public static function name(): string
    {
        try {
            $sname = request()->header('X-Store-Name');
            return is_string($sname) ? trim($sname) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }
}
