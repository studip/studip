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

    /*literature search plugins
    ----------------------------------------------------------------
    */

    //standard plugin, searches in Stud.IP Database (table lit_catalog), you should leave this one enabled !
    $_lit_search_plugins[] = ['name' => "Studip",'display_name' =>'Katalog der Stud.IP Datenbank', 'link' => ''];

    //Plugins derived from Z3950Abstract, used for querying Z39.50 Servers
    //only activate these plugins, if your Version of PHP supports the YAZ extension!

    /* Gemeinsamer Verbundkatalog - GVK */
    $_lit_search_plugins[] = ['name' => "Gvk",'display_name' =>'Gemeinsamer Verbundkatalog', 'link' => 'http://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'];

    /* Niedersächsische Staats- und Universitätsbibliothek Göttingen, OPAC */
    $_lit_search_plugins[] = ['name' => "SUBGoeOpac",'display_name' => "Opac der SUB Göttingen" , 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'];

    /* Göttinger Gesamtkatalog (Regionalkatalog Göttingen) */
    $_lit_search_plugins[] = ['name' => 'Rkgoe', 'display_name' =>'Regionalkatalog Göttingen', 'link' => 'http://gso.gbv.de/DB=2.90/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'];



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
}
