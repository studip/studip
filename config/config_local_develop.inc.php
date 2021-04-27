<?php
/*settings for database access
----------------------------------------------------------------
please fill in your database connection settings.
*/
namespace Studip {
    const ENV = 'development';
}

namespace {
    // default Stud.IP database (DB_Seminar)
    $DB_STUDIP_HOST = "localhost";
    $DB_STUDIP_USER = "";
    $DB_STUDIP_PASSWORD = "";
    $DB_STUDIP_DATABASE = "studip";
    @include "dbpass.inc";


    //ABSOLUTE_PATH_STUDIP should end with a '/'
    //$ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH . '/public/';
    //$CANONICAL_RELATIVE_PATH_STUDIP
    if (!$ABSOLUTE_URI_STUDIP) $ABSOLUTE_URI_STUDIP = 'https://develop.studip.de/studip/';

    //path to the temporary folder
    $TMP_PATH ="/tmp/studip";                                   //the system temp path
    if (!is_dir($TMP_PATH)) mkdir($TMP_PATH,0777);

    //caching
    $CACHING_ENABLE = true;
    $CACHING_FILECACHE_PATH = $TMP_PATH . '/studip_cache';
    $CACHE_IS_SESSION_STORAGE = false;                 //store session data in cache

    /*Stud.IP modules
    ----------------------------------------------------------------
    enable or disable the Stud.IP internal modules, set and basic settings*/

    $FOP_SH_CALL = "/usr/bin/fop";                       //path to fop


    /*domain name and path translation
    ----------------------------------------------------------------
    */
    $STUDIP_DOMAINS[1] = "test.studip.de/studip";
    $STUDIP_DOMAINS[2] = "develop.studip.de/studip";


    /*mail settings
    ----------------------------------------------------------------
    */
    $MAIL_TRANSPORT = "smtp";

    /*smtp settings
    ----------------------------------------------------------------
    leave blank or try 127.0.0.1 if localhost is also the mailserver
    ignore if you don't use smtp as transport*/
    $MAIL_HOST_NAME = "127.0.0.1";                               //which mailserver should we use? (must allow mail-relaying from $MAIL_LOCALHOST, defaults to SERVER_NAME)

    $MAIL_LOCALHOST = "develop.studip.de";                               //name of the mail sending machine (the web server) defaults to SERVER_NAME
    $MAIL_ENV_FROM = "develop-noreply@studip.de";                                //sender mail adress, defaults to wwwrun @ $MAIL_LOCALHOST
    $MAIL_ABUSE = "abuse@studip.de";                                   //mail adress to reply to in case of abuse, defaults to abuse @  $MAIL_LOCALHOST

    $MAIL_BULK_DELIVERY = TRUE;                        //try to improve the message queueing rate (experimental, does not work for php transport)

    $MAIL_VALIDATE_HOST = TRUE;                             //check for valid mail host when user enters email adress
    $MAIL_VALIDATE_BOX = FALSE;                              //check for valid mail account when user enters email adress; set to false if the webserver got no valid MX record
    $MESSAGING_FORWARD_DEFAULT = 3;                             //the default functions for internal and external messages
    $MAIL_ATTACHMENTS_MAX_SIZE = 10;                             //maximum size of attachments in MB


    /*language settings
    ----------------------------------------------------------------*/

    $CONTENT_LANGUAGES['en_GB'] = ['picture' => 'lang_en.gif', 'name' => 'English'];


    /*authentication plugins
    ----------------------------------------------------------------
    */

    $STUDIP_AUTH_PLUGIN[] = "Standard";
    $STUDIP_AUTH_PLUGIN[] = "Shib";

    $STUDIP_AUTH_CONFIG_STANDARD = ["error_head" => "intern"];

    $STUDIP_AUTH_CONFIG_SHIB = [
        // SessionInitator URL for remote SP
        'session_initiator' => 'https://shib-sp.uni-osnabrueck.de/secure/studip-sp.php',
        // validation URL for remote SP
        'validate_url'      => 'https://shib-sp.uni-osnabrueck.de/auth/studip-sp.php',
        // standard user data mapping
        'user_data_mapping' => [
            'auth_user_md5.username' => ['callback' => 'dummy', 'map_args' => ''],
            'auth_user_md5.password' => ['callback' => 'dummy', 'map_args' => ''],
            'auth_user_md5.Vorname' => ['callback' => 'getUserData', 'map_args' => 'givenname'],
            'auth_user_md5.Nachname' => ['callback' => 'getUserData', 'map_args' => 'sn'],
            'auth_user_md5.Email' => ['callback' => 'getUserData', 'map_args' => 'mail']
        ]
    ];
    $PHPASS_USE_PORTABLE_HASH = true;

    $LIBRARY_CATALOGS = [
        [
            'name' => 'K10Plus Zentral',
            'class_name' => 'K10PlusZentralLibrarySearch',
            //'base_url' => 'http://findex.gbv.de/index/dataquest/select'
            'base_url' => 'https://server5.data-quest.de/search-k10plus'
        ],
        [
            'name' => 'BASE',
            'class_name' => 'BASELibrarySearch',
            //'base_url' => 'https://api.base-search.net/cgi-bin/BaseHttpSearchInterface.fcgi',
            'base_url' => 'https://server5.data-quest.de/search-base',
            'settings' => [
                'collection' => 'de'
            ]
        ],
        [
            'name' => 'TIB Portal',
            'class_name' => 'SRULibrarySearch',
            'base_url' => 'https://www.tib.eu/sru/tibkat',
            'settings' => [
                'sru_version' => '1.2',
                'query_format' => 'cql'
            ],
            'opac_link_template' => 'https://www.tib.eu/de/suchen/id/TIBKAT%3A{opac_document_id}',
            'local_catalog' => true
        ]
    ];
}
