<?php

namespace App\Core;

class FlashMessage {
    public static function warning(string $text = null) {
        $_SESSION['flash_message'] = '<div class="alert alert-warning toast">'.$text.'</div>';
    }
    public static function success(string $text = null) {
        $_SESSION['flash_message'] = '<div class="alert alert-success toast">'.$text.'</div>';
    }
    public static function info(string $text = null) {
        $_SESSION['flash_message'] = '<div class="alert alert-info toast">'.$text.'</div>';
    }
    public static function error(string $text = null) {
        $_SESSION['flash_message'] = '<div class="alert alert-danger toast">'.$text.'</div>';
    }

    public static function clear(){
        unset($_SESSION['flash_message']);
    }
}