<?php
if (!function_exists('checkRights')) {
    function checkRights($rights)
    {
        include_once base_path('app/Secrets/' . session('user_id') . '.php');
        return defined($rights);
    }
}

if (!function_exists('getSecretFileData')) {
    function getSecretFileData($name)
    {
        if (session('user_id')) {
            include_once base_path('app/Secrets/' . session('user_id') . '.php');
            if (defined($name)) {
                return constant($name);
            }
            return null;
        }
    }
}
