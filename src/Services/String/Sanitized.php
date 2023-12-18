<?php
namespace App\Services\String;

class Sanitized {
    /**
     * Remove all space before and after string
     */
    public static function stringValue($input) {
        return strlen(trim($input)) > 0 ? $input : null;
    }
}
?>