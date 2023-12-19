<?php
namespace App\Services\Verify;
class IsStrongPassword{

    /**
     * Should be up to 8 AN
     */
    public static function low($pass){
        $pattern = '/^.{4,}$/';
        return preg_match($pattern,$pass) > 0 ? true:false;
    }

    /**
     * PIN mode where password 4 numbers
     */
    public static function pin($pass){
        $pattern = '/[0-9]{4}/';
        return preg_match($pattern,$pass) > 0 ? true:false;
    }

    /**
     * Should be up to 8 AN
     */
    public static function medium($pass){
        $pattern = '/^.{8,}$/';
        return preg_match($pattern,$pass) > 0 ? true:false;
    }

    /**
     * Should be up to 8 AN
     */
    public static function high($pass){
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/';
        return preg_match($pattern,$pass) > 0 ? true:false;
    }


}
?>