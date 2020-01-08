<?php
/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the basic system settings. You shouldn't have to touch much of them...
please note the CONFIG.INC.PHP for the indivual settings of your installation!*/

namespace Studip {
    const ENV = 'development';
}

namespace {
    /*settings for database access
    ----------------------------------------------------------------
    please fill in your database connection settings.
    */

    // default Stud.IP database (DB_Seminar)
    $DB_STUDIP_HOST = "127.0.0.1";
    $DB_STUDIP_USER = "studip";
    $DB_STUDIP_PASSWORD = "studip";
    $DB_STUDIP_DATABASE = "studip";

    /*URL
    ----------------------------------------------------------------
    customize if automatic detection fails, e.g. when installation is hidden
    behind a proxy
    */
    //$CANONICAL_RELATIVE_PATH_STUDIP = '/';
    //$ABSOLUTE_URI_STUDIP = 'https://www.studip.de/';
    //$ASSETS_URL = 'https://www.studip.de/assets/';
}
