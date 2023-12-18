<?php
namespace App\Services\Verify;
class IsValid{

    /**
     * Should be up to 8 AN
     */
    public static function phoneNumber($num){
        $pattern = '/[0-9]{9,}/';
        return preg_match($pattern,$num) > 0 ? true:false;
    }
}
?>