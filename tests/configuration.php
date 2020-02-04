<?php

return (function ($filename) {
    if (file_exists($filename)) {
        require_once $filename;

        return compact('DB_STUDIP_HOST', 'DB_STUDIP_USER', 'DB_STUDIP_PASSWORD', 'DB_STUDIP_DATABASE');
    }

    return [];
})(dirname(__DIR__).'/config/config_local.inc.php');
