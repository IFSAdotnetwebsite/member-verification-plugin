<?php

class IFSAUtility
{
    /**
     * Return a sanitized post variable or default
     * @param $var
     * @param string $default
     * @return mixed
     */
    static function get_post_var($var, $default=""){
        if(isset($_POST[$var]) && !empty($_POST[$var])) {
            return sanitize_text_field($_POST[$var]);
        } else {
                return $default;
        }
    }

}