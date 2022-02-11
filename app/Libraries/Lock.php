<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lock
{

    private static $lockFolder = '.locks';
    private static $disk = 'public_html';

    public static function setDisk($disk)
    {
        self::$disk = $disk;
    }

    public static function isLocked($lockFile)
    {
        self::checkLockFolder();
        return Storage::disk(self::$disk)->exists(self::$lockFolder . '/' . $lockFile.'.lock');
    }

    public static function lock($lockFile, $content = 'locked')
    {
        self::checkLockFolder();
        Storage::disk(self::$disk)->put(self::$lockFolder . '/' . $lockFile.'.lock', $content);
    }

    public static function getLockContent($lockfile)
    {
        self::checkLockFolder();
        return trim(Storage::disk(self::$disk)->get(self::$lockFolder . '/' . $lockfile.'.lock'));
    }

    public static function unlock($lockFile)
    {
        self::checkLockFolder();
        return Storage::disk(self::$disk)->delete(self::$lockFolder . '/' . $lockFile.'.lock');
    }

    protected static function checkLockFolder()
    {
        if (!Storage::disk(self::$disk)->exists(self::$lockFolder)) {
            Storage::disk(self::$disk)->makeDirectory(self::$lockFolder);
        }
    }

}