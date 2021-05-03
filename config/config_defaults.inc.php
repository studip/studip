<?php
/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the basic system settings. You shouldn't have to touch much of them...
please note the CONFIG.INC.PHP for the indivual settings of your installation!*/

/*settings for database access
----------------------------------------------------------------
please fill in your database connection settings.
*/

// default Stud.IP database (DB_Seminar)
$DB_STUDIP_HOST = "localhost";
$DB_STUDIP_USER = "";
$DB_STUDIP_PASSWORD = "";
$DB_STUDIP_DATABASE = "studip";

/*
// optional Stud.IP slave database
$DB_STUDIP_SLAVE_HOST = "localhost";
$DB_STUDIP_SLAVE_USER = "";
$DB_STUDIP_SLAVE_PASSWORD = "";
$DB_STUDIP_SLAVE_DATABASE = "studip-slave";
*/

#####    ##   ##### #    #  ####
#    #  #  #    #   #    # #
#    # #    #   #   ######  ####
#####  ######   #   #    #      #
#      #    #   #   #    # #    #
#      #    #   #   #    #  ####


//ABSOLUTE_PATH_STUDIP should end with a '/'
//$ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH . '/public/';
//$CANONICAL_RELATIVE_PATH_STUDIP
//$ABSOLUTE_URI_STUDIP
//$ASSETS_URL


// absolute filesystem path to the plugin packages
$PLUGINS_PATH = $STUDIP_BASE_PATH . '/public/plugins_packages';

// absolute filesystem path to the plugin assets
$PLUGIN_ASSETS_PATH = $STUDIP_BASE_PATH . '/data/assets_cache';

// path to uploaded documents (wwwrun needs write-perm there)
$UPLOAD_PATH = $STUDIP_BASE_PATH . "/data/upload_doc";

// path to Stud.IP archive (wwwrun needs write-perm there)
$ARCHIV_PATH = $STUDIP_BASE_PATH . "/data/archiv";

// path to OERs in Stud.IP (wwwrun needs write-perm there)
$OER_PATH = $STUDIP_BASE_PATH . "/data/oer";
$OER_LOGOS_PATH = $STUDIP_BASE_PATH . "/data/oer_logos";

// path and url for dynamically generated static content like smilies..
$DYNAMIC_CONTENT_PATH = $STUDIP_BASE_PATH . "/public/pictures";
$DYNAMIC_CONTENT_URL  = $ABSOLUTE_URI_STUDIP  . "pictures";


//path to the temporary folder
$TMP_PATH ="/tmp";                                  //the system temp path

// media proxy settings
$MEDIA_CACHE_PATH = $STUDIP_BASE_PATH . '/data/media_cache';

//caching
$CACHING_ENABLE = true;
$CACHING_FILECACHE_PATH = $TMP_PATH . '/studip_cache';
$CACHE_IS_SESSION_STORAGE = false;                 //store session data in cache

/*Stud.IP modules
----------------------------------------------------------------
enable or disable the Stud.IP internal modules, set and basic settings*/

$FOP_SH_CALL = "/usr/bin/fop";                        //path to fop

$EXTERN_SERVER_NAME = "";                               //define name, if you use special setup

$ELEARNING_INTERFACE_MODULES = [
    "ilias5" => [
        "name" => "ILIAS 5",
        "ABSOLUTE_PATH_ELEARNINGMODULES" => "http://<your Ilias installation>/",
        "ABSOLUTE_PATH_SOAP" => "http://<your Ilias installation>/webservice/soap/server.php?wsdl",
        "CLASS_PREFIX" => "Ilias5",
        "auth_necessary" => true,
        "USER_AUTO_CREATE" => true,
        "USER_PREFIX" => "",
        "target_file" => "studip_referrer.php",
        "logo_file" => "assets/images/logos/ilias_logo.png",
        "soap_data" => [
                        "username" => "<username>",     //this credentials are used to communicate with your Ilias 3 installation over SOAP
                        "password" => "<password>",
                        "client" => "<ilias client id>"],
        "types" => [
                   "webr" => ["name" => "ILIAS-Link", "icon" => "learnmodule"],
                   "htlm" => ["name" => "HTML-Lerneinheit", "icon" => "learnmodule"],
                   "sahs" => ["name" => "SCORM/AICC-Lerneinheit", "icon" => "learnmodule"],
                   "lm" => ["name" => "ILIAS-Lerneinheit", "icon" => "learnmodule"],
                   "glo" => ["name" => "ILIAS-Glossar", "icon" => "learnmodule"],
                   "tst" => ["name" => "ILIAS-Test", "icon" => "learnmodule"],
                   "svy" => ["name" => "ILIAS-Umfrage", "icon" => "learnmodule"],
                   "exc" => ["name" => "ILIAS-Übung", "icon" => "learnmodule"]
                   ],
        "global_roles" => [4,5,14], // put here the ilias role-ids for User, Guest and Anonymous
        "roles" =>  [
                        "autor" => "4",
                        "tutor" => "4",
                        "dozent" => "4",
                        "admin" => "4",
                        "root" => "2"
                        ],
        "crs_roles" =>  [
                        "autor" => "member",
                        "tutor" => "tutor",
                        "dozent" => "admin",
                        "admin" => "admin",
                        "root" => "admin"
                        ]
        ]
    ];

// example entry for wikifarm as server for elearning modules
// remember to activate studip-webservices with WEBSERVICES_ENABLE and to set STUDIP_INSTALLATION_ID

$ELEARNING_INTERFACE_MODULES["pmwiki-farm"] =   [
                        "name" => "Wikifarm",
                        "ABSOLUTE_PATH_ELEARNINGMODULES" => "http://<your PmWiki farm server>/<path to wiki fields>/",

                        "WEBSERVICE_CLASS" => "xml_rpc_webserviceclient",
                        "ABSOLUTE_PATH_SOAP" => "http://<your PmWiki farm server>/<path to PmWiki farm>/pmwiki.php",  // url to farm webservices
                        "URL_PARAMS" => "action=xmlrpc",

                        "CLASS_PREFIX" => "PmWiki",
                        "auth_necessary" => false,

                        "field_script" => "field.php",
                        "logo_file" => $ASSETS_URL."/images/logos/pmwiki-32.gif",

                        "soap_data" => [
              "api-key" => "<api-key for wiki webservices>",
            ],
                        "types" =>  [
              "wiki" => ["name" => "PmWiki-Lernmodul", "icon" => "learnmodule"],
            ]
];

$ELEARNING_INTERFACE_MODULES["loncapa"] =
[
    "name" => "LonCapa",
    "ABSOLUTE_PATH_ELEARNINGMODULES" => "http://127.0.0.1/loncapa",
    "CLASS_PREFIX" => "LonCapa",
    "auth_necessary" => false,
    "logo_file" => "assets/images/logos/lon-capa.gif",
    "types" =>  [
        "loncapa" => ["name" => "LonCapa-Lernmodul",
                           "icon" => "learnmodule"],
        ]
];

$PLUGINS_UPLOAD_ENABLE = TRUE;      //Upload of Plugins is enabled

$PLUGIN_REPOSITORIES = [
    'http://plugins.studip.de/plugins.xml',
];

/*domain name and path translation
----------------------------------------------------------------
to translate internal links (within Stud.IP) to the different
domain names. To activate this feature uncomment these lines
and add all used domain names. Below, some examples are given.
*/

//server-root is stud.ip root dir, or virtual server for stud.ip
//$STUDIP_DOMAINS[1] = "<your.server.name>";
//$STUDIP_DOMAINS[2] = "<your.server.ip>";
//$STUDIP_DOMAINS[3] = "<your.virtual.server.name>";
//
// or
//
//stud.ip root is a normal directory
//$STUDIP_DOMAINS[1] = "<your.server.name/studip>";
//$STUDIP_DOMAINS[2] = "<your.server.ip/studip>";


/*mail settings
----------------------------------------------------------------
possible settings for $MAIL_TRANSPORT:
smtp      use smtp to deliver to $MAIL_HOST_NAME
php       use php's mail() function
sendmail  use local sendmail script
qmail     use local Qmail MTA
debug     mails are only written to a file in $TMP_PATH
*/
$MAIL_TRANSPORT = "smtp";

/*smtp settings
----------------------------------------------------------------
leave blank or try 127.0.0.1 if localhost is also the mailserver
ignore if you don't use smtp as transport*/
$MAIL_HOST_NAME = "";                               //which mailserver should we use? (must allow mail-relaying from $MAIL_LOCALHOST, defaults to SERVER_NAME)
$MAIL_SMTP_OPTIONS = [
    'port' => 25,
    'user' => '',
    'password' => '',
    'authentication_mechanism' => '',
    'ssl' => 0,
    'start_tls' => 0
    ];

$MAIL_LOCALHOST = "";                               //name of the mail sending machine (the web server) defaults to SERVER_NAME
$MAIL_CHARSET = "";                                 //character set of mail body, defaults to WINDOWS-1252
$MAIL_ENV_FROM = "";                                //sender mail adress, defaults to wwwrun @ $MAIL_LOCALHOST
$MAIL_FROM = "";                                    //name of sender, defaults to "Stud.IP"
$MAIL_ABUSE = "";                                   //mail adress to reply to in case of abuse, defaults to abuse @  $MAIL_LOCALHOST

$MAIL_BULK_DELIVERY = FALSE;                        //try to improve the message queueing rate (experimental, does not work for php transport)

$MAIL_VALIDATE_HOST = TRUE;                             //check for valid mail host when user enters email adress
$MAIL_VALIDATE_BOX = TRUE;                              //check for valid mail account when user enters email adress; set to false if the webserver got no valid MX record

$MESSAGING_FORWARD_AS_EMAIL = TRUE;                         //enable to forward every internal message to the user-mail (the user is able to deactivate this function in his personal settings)
$MESSAGING_FORWARD_DEFAULT = 1;                             //the default setting: if 1, the user has to switch it on; if 2, every message will be forwarded; if 3 every message will be forwarded on request of the sender
$MESSAGING_FORWARD_USE_REPLYTO = FALSE;                     //send forwarded messages as system user and add reply-to header

$ENABLE_EMAIL_TO_STATUSGROUP = TRUE;                                // enable to send messages to whole status groups

$ENABLE_EMAIL_ATTACHMENTS = TRUE;                               // enable attachment functions for internal and external messages
$MAIL_ATTACHMENTS_MAX_SIZE = 10;                             //maximum size of attachments in MB

/*language settings
----------------------------------------------------------------*/

$INSTALLED_LANGUAGES["de_DE"] =  ["path"=>"de", "picture"=>"lang_de.gif", "name"=>"Deutsch"];
$INSTALLED_LANGUAGES["en_GB"] =  ["path"=>"en", "picture"=>"lang_en.gif", "name"=>"English"];
$CONTENT_LANGUAGES['de_DE'] = ['picture' => 'lang_de.gif', 'name' => 'Deutsch'];
//$CONTENT_LANGUAGES['en_GB'] = array('picture' => 'lang_en.gif', 'name' => 'English');

$_language_domain = "studip";  // the name of the language file. Should not be changed except in cases of individual translations or special terms.


/*authentication plugins
----------------------------------------------------------------
the following plugins are available:
Standard        authentication using the local Stud.IP database
StandardExtern      authentication using an alternative Stud.IP database, e.g. another installation
Ldap            authentication using an LDAP server, this plugin uses anonymous bind against LDAP to retrieve the user dn,
            then it uses the submitted password to authenticate with this user dn
LdapReader      authentication using an LDAP server, this plugin binds to the server using a given dn and a password,
            this account must have read access to gather the attributes for the user who tries to authenticate.
CAS         authentication using a central authentication server (CAS)
Shib            authentication using a Shibboleth identity provider (IdP)

If you write your own plugin put it in studip-htdocs/lib/classes/auth_plugins
and enable it here. The name of the plugin is the classname excluding "StudipAuth".

You could also place your configuration here, name it $STUDIP_AUTH_CONFIG_<plugin name>,
all uppercase each item of the configuration array will become a member of your plugin class.*/

//$STUDIP_AUTH_PLUGIN[] = "LdapReadAndBind";
//$STUDIP_AUTH_PLUGIN[] = "Ldap";
//$STUDIP_AUTH_PLUGIN[] = "StandardExtern";
$STUDIP_AUTH_PLUGIN[] = "Standard";
// $STUDIP_AUTH_PLUGIN[] = "CAS";
// $STUDIP_AUTH_PLUGIN[] = "LTI";
// $STUDIP_AUTH_PLUGIN[] = "Shib";
// $STUDIP_AUTH_PLUGIN[] = "IP";

$STUDIP_AUTH_CONFIG_STANDARD = ["error_head" => "intern"];
/*
$STUDIP_AUTH_CONFIG_LDAPREADANDBIND = array("host" => "localhost",
                                        "base_dn" => "dc=studip,dc=de",
                                        "protocol_version" => 3,
                                        "start_tls" => false,
                                        "send_utf8_credentials" => true,
                                        "decode_utf8_values" => true,
                                        "bad_char_regex" => '/[^0-9_a-zA-Z-]/',
                                        "username_case_insensitiv" => true,
                                        "username_attribute" => "uid",
                                        "user_password_attribute" => "userpassword",
                                        "reader_dn" => "uid=reader,dc=studip,dc=de",
                                        "reader_password" => "<password>",
                                        "error_head" => "LDAP read-and-bind plugin",
                                        "user_data_mapping" =>
                                        array(  "auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
                                                "auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
                                                "auth_user_md5.Email" => array("callback" => "doLdapMap", "map_args" => "email"),
                                                "auth_user_md5.Nachname" => array("callback" => "doLdapMap", "map_args" => "sn"),
                                                "auth_user_md5.Vorname" => array("callback" => "doLdapMap", "map_args" => "givenname")
                                                )
                                        );

$STUDIP_AUTH_CONFIG_LDAP = array(       "host" => "localhost",
                                        "base_dn" => "dc=data-quest,dc=de",
                                        "protocol_version" => 3,
                                        "start_tls" => false,
                                        "send_utf8_credentials" => true,
                                        "decode_utf8_values" => true,
                                        "bad_char_regex" => '/[^0-9_a-zA-Z-]/',
                                        "username_case_insensitiv" => true,
                                        "username_attribute" => "uid",
                                        "anonymous_bind" => true,
                                        "error_head" => "LDAP plugin",
                                        "user_data_mapping" =>
                                        array(  "auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
                                                "auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
                                                "auth_user_md5.Email" => array("callback" => "doLdapMap", "map_args" => "email"),
                                                "auth_user_md5.Nachname" => array("callback" => "doLdapMap", "map_args" => "sn"),
                                                "auth_user_md5.Vorname" => array("callback" => "doLdapMap", "map_args" => "givenname")
                                                )
                                        );

// create a config for your own user data mapping class
$CASAbstractUserDataMapping_CONFIG = array();
$STUDIP_AUTH_CONFIG_CAS = array("host" => "cas.studip.de",
                                        "port" => 8443,
                                        "uri"  => "cas",
                                        "proxy"  => false,
                                        "cacert" => "/path/to/server/cert",
                                        "user_data_mapping_class" => "CASAbstractUserDataMapping",
                                        "user_data_mapping" => // map_args are dependent on your own data mapping class
                                                array(  "auth_user_md5.username" => array("callback" => "getUserData", "map_args" => "username"),
                                                        "auth_user_md5.Vorname" => array("callback" => "getUserData", "map_args" => "givenname"),
                                                        "auth_user_md5.Nachname" => array("callback" => "getUserData", "map_args" => "surname"),
                                                        "auth_user_md5.Email" => array("callback" => "getUserData", "map_args" => "email"),
                                                        "auth_user_md5.perms" => array("callback" => "getUserData", "map_args" => "status")));

$STUDIP_AUTH_CONFIG_LTI = [
    'consumer_keys' => [
        // 'domain' is optional, default is value of consumer_key
        'studip.de' => ['consumer_secret' => 'secret', 'domain' => 'studip.de']
    ],
    'user_data_mapping' => [
        // see http://www.imsglobal.org/specs/ltiv1p1/implementation-guide for lauch data item names
        'auth_user_md5.username' => ['callback' => 'dummy', 'map_args' => ''],
        'auth_user_md5.password' => ['callback' => 'dummy', 'map_args' => ''],
        'auth_user_md5.Vorname'  => ['callback' => 'getUserData', 'map_args' => 'lis_person_name_given'],
        'auth_user_md5.Nachname' => ['callback' => 'getUserData', 'map_args' => 'lis_person_name_family'],
        'auth_user_md5.Email'    => ['callback' => 'getUserData', 'map_args' => 'lis_person_contact_email_primary']
    ]
];

$STUDIP_AUTH_CONFIG_SHIB = array("session_initiator" => "https://sp.studip.de/Shibboleth.sso/WAYF/DEMO",
                                        "validate_url" => "https://sp.studip.de/auth/studip-sp.php",
                                        "local_domain" => "studip.de",
                                        "user_data_mapping" =>
                                                array(  "auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
                                                        "auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
                                                        "auth_user_md5.Vorname" => array("callback" => "getUserData", "map_args" => "givenname"),
                                                        "auth_user_md5.Nachname" => array("callback" => "getUserData", "map_args" => "surname"),
                                                        "auth_user_md5.Email" => array("callback" => "getUserData", "map_args" => "email")));

$STUDIP_AUTH_CONFIG_IP = array('allowed_users' =>
    array ('root' => array('127.0.0.1', '::1')));
*/

//some additional authification-settings
//NOTE: you MUST enable Standard authentication-plugin for this settings to take effect!

// Login ip range check
$ENABLE_ADMIN_IP_CHECK = false;
$ENABLE_ROOT_IP_CHECK = false;
$LOGIN_IP_RANGES =
    [
        'V4' => [
            ['start' => '', 'end' => ''],
        ]
        ,
        'V6' => [
            ['start' => '', 'end' => ''],
        ]
    ];


///////////////////////
//Library configuration
///////////////////////


$LIBRARY_STYLESHEET_ID = 'din-1505-2';


/**
 * LIBRARY_CATALOGS contains the list of catalogs that are configured.
 * Entries in this array have the following structure:
 *
 * [
 *     'name'          => The name of the catalog.
 *     'class_name'    => The class that handles the search in that catalog.
 *                         It must be an implementation of the
 *                         LibrarySearch interface.
 *     'base_url'      => The base URL to the search page where requests can
 *                        be sent to.
 *     'settings'      => Catalog specific settings. This is an optional
 *                        associative array.
 *     'local_catalog' => Whether the catalog is a local catalog that is only
 *                        used to enrich search results with additional data
 *                        and download possibilities (true) or the catalog is
 *                        a "normal" catalog that is used for the
 *                        library search (false).
 * ]
 */
$LIBRARY_CATALOGS = [];


/**
 * LIBRARY_VARIABLE_TYPES is a list containing the variable types that can be
 * used for library variables.
 */
$LIBRARY_VARIABLE_TYPES = [
    'date',
    'name',
    'number',
    'standard'
];


/**
 * LIBRARY_VARIABLES is a list with all variables that can be used for
 * library documents.
 */
$LIBRARY_VARIABLES = [
    [
        'name' => 'abstract',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Inhaltsangabe',
            'en_GB' => 'Abstract'
        ]
    ],
    [
        'name' => 'accessed',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Zugriffsdatum',
            'en_GB' => 'Accessed'
        ]
    ],
    [
        'name' => 'annote',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Annotation',
            'en_GB' => 'Annote'
        ]
    ],
    [
        'name' => 'archive',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Archiv',
            'en_GB' => 'Archive'
        ]
    ],
    [
        'name' => 'archive_location',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Speicherort im Archiv',
            'en_GB' => 'Archive location'
        ]
    ],
    [
        'name' => 'archive-place',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Archivort',
            'en_GB' => 'Archive place'
        ]
    ],
    [
        'name' => 'author',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Autorin, Autor',
            'en_GB' => 'Author'
        ],
        'required' => true
    ],
    [
        'name' => 'authority',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Genehmigungsinstanz',
            'en_GB' => 'Authority'
        ]
    ],
    [
        'name' => 'call-number',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Signatur',
            'en_GB' => 'Call-number'
        ]
    ],
    [
        'name' => 'chapter-number',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Kapitelnummer',
            'en_GB' => 'Chapter-number'
        ]
    ],
    [
        'name' => 'citation-label',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Zitierbezeichnung',
            'en_GB' => 'Citation-label'
        ]
    ],
    [
        'name' => 'citation-number',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Zitiernummer',
            'en_GB' => 'Citation-number'
        ]
    ],
    [
        'name' => 'collection-editor',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Sammlungseditor',
            'en_GB' => 'Collection-editor'
        ]
    ],
    [
        'name' => 'collection-number',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Sammlungsnummer',
            'en_GB' => 'Collection-number'
        ]
    ],
    [
        'name' => 'collection-title',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Sammlungstitel',
            'en_GB' => 'Collection-title'
        ]
    ],
    [
        'name' => 'composer',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Komponist*in, Verfasser*in',
            'en_GB' => 'Composer'
        ]
    ],
    [
        'name' => 'container',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Sammlung',
            'en_GB' => 'Container'
        ]
    ],
    [
        'name' => 'container-author',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Autor*in',
            'en_GB' => 'Container-author'
        ]
    ],
    [
        'name' => 'container-title',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Titel der Sammlung',
            'en_GB' => 'Container-title'
        ]
    ],
    [
        'name' => 'container-title-short',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Kurztitel der Sammlung',
            'en_GB' => 'Container-title-short'
        ]
    ],
    [
        'name' => 'dimensions',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Umfang',
            'en_GB' => 'Dimension'
        ]
    ],
    [
        'name' => 'director',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Regisseur*in',
            'en_GB' => 'Director'
        ]
    ],
    [
        'name' => 'DOI',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Digitale Objektkennung',
            'en_GB' => 'Digital Object Identifier'
        ]
    ],
    [
        'name' => 'edition',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Auflage',
            'en_GB' => 'Edition'
        ]
    ],
    [
        'name' => 'editor',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Verfasser*in',
            'en_GB' => 'Editor'
        ]
    ],
    [
        'name' => 'editorial-director',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Herausgeber*in',
            'en_GB' => 'Editorial-director'
        ]
    ],
    [
        'name' => 'event',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Veranstaltung',
            'en_GB' => 'Event'
        ]
    ],
    [
        'name' => 'event-date',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Datum der Veranstaltung',
            'en_GB' => 'Event-date'
        ]
    ],
    [
        'name' => 'event-place',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Veranstaltungsort',
            'en_GB' => 'Event-place'
        ]
    ],
    [
        'name' => 'first-reference-note-number',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Erste Referenznummer zu vorherigem Eintrag',
            'en_GB' => 'First-reference-note-number'
        ]
    ],
    [
        'name' => 'genre',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Gattung',
            'en_GB' => 'Genre'
        ]
    ],
    [
        'name' => 'illustrator',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Illustrator*in',
            'en_GB' => 'Illustrator'
        ]
    ],
    [
        'name' => 'interviewer',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Interviewer*in',
            'en_GB' => 'Interviewer'
        ]
    ],
    [
        'name' => 'ISBN',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'ISBN',
            'en_GB' => 'ISBN'
        ]
    ],
    [
        'name' => 'ISSN',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'ISSN',
            'en_GB' => 'ISSN'
        ]
    ],
    [
        'name' => 'issue',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Ausgabe',
            'en_GB' => 'Issue'
        ]
    ],
    [
        'name' => 'issued',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Datum der Veröffentlichung der Ausgabe',
            'en_GB' => 'Issued'
        ]
    ],
    [
        'name' => 'jurisdiction',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Gerichtsstand',
            'en_GB' => 'Jurisdiction'
        ]
    ],
    [
        'name' => 'keyword',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Schlagwort',
            'en_GB' => 'Keyword'
        ]
    ],
    [
        'name' => 'language',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Sprache',
            'en_GB' => 'Language'
        ]
    ],
    [
        'name' => 'locator',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Stelle',
            'en_GB' => 'Locator'
        ]
    ],
    [
        'name' => 'medium',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Medium',
            'en_GB' => 'Medium'
        ]
    ],
    [
        'name' => 'note',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'zusätzliche Informationen',
            'en_GB' => 'Additional note'
        ]
    ],
    [
        'name' => 'number',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Identifikationsnummer',
            'en_GB' => 'Number (identity of object)'
        ]
    ],
    [
        'name' => 'number-of-pages',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Seitenanzahl',
            'en_GB' => 'Number-of-pages'
        ]
    ],
    [
        'name' => 'number-of-volumes',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Bandanzahl',
            'en_GB' => 'Number-of-volumes'
        ]
    ],
    [
        'name' => 'original-author',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Autor des Originals',
            'en_GB' => 'Original-author'
        ]
    ],
    [
        'name' => 'original-date',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Datum der Originalversion',
            'en_GB' => 'Original-date'
        ]
    ],
    [
        'name' => 'original-publisher',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Originaler Verlag',
            'en_GB' => 'Original-publisher'
        ]
    ],
    [
        'name' => 'original-publisher-place',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Verlagsort des Originals',
            'en_GB' => 'Original-publisher-place'
        ]
    ],
    [
        'name' => 'original-title',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Titel der originalen Version',
            'en_GB' => 'Original-title'
        ]
    ],
    [
        'name' => 'page',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Seite von bis',
            'en_GB' => 'Page (range)'
        ]
    ],
    [
        'name' => 'page-first',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Erste Seite des Seitenbereichs',
            'en_GB' => 'Page-first'
        ]
    ],
    [
        'name' => 'PMCID',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'PubMed Central reference number',
            'en_GB' => 'PubMed Central reference number'
        ]
    ],
    [
        'name' => 'PMID',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'PubMedID',
            'en_GB' => 'PubMed Identifier'
        ]
    ],
    [
        'name' => 'publisher',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Verlag',
            'en_GB' => 'Publisher'
        ]
    ],
    [
        'name' => 'publisher-place',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Verlagsort',
            'en_GB' => 'Publisher-place'
        ]
    ],
    [
        'name' => 'recipient',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Empfänger',
            'en_GB' => 'Recipient'
        ]
    ],
    [
        'name' => 'references',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Referenzen',
            'en_GB' => 'References'
        ]
    ],
    [
        'name' => 'reviewed-author',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Autor des rezensierten Werks',
            'en_GB' => 'Reviewed-author'
        ]
    ],
    [
        'name' => 'reviewed-title',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Titel des rezensierten Werks',
            'en_GB' => 'Reviewed-title'
        ]
    ],
    [
        'name' => 'scale',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Größe, Ausmaß',
            'en_GB' => 'Scale'
        ]
    ],
    [
        'name' => 'section',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Abschnitt',
            'en_GB' => 'Section'
        ]
    ],
    [
        'name' => 'source',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Quelle',
            'en_GB' => 'Source'
        ]
    ],
    [
        'name' => 'status',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Status',
            'en_GB' => 'Status'
        ]
    ],
    [
        'name' => 'submitted',
        'type' => 'date',
        'display_name' => [
            'de_DE' => 'Einreichungsdatum',
            'en_GB' => 'Submitted'
        ]
    ],
    [
        'name' => 'title',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Titel',
            'en_GB' => 'Title'
        ],
        'required' => true
    ],
    [
        'name' => 'title-short',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Kurztitel',
            'en_GB' => 'Title-short'
        ]
    ],
    [
        'name' => 'translator',
        'type' => 'name',
        'display_name' => [
            'de_DE' => 'Übersetzer',
            'en_GB' => 'Translator'
        ]
    ],
    [
        'name' => 'URL',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'URL',
            'en_GB' => 'URL'
        ]
    ],
    [
        'name' => 'version',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Version',
            'en_GB' => 'Version'
        ]
    ],
    [
        'Name' => 'volume',
        'type' => 'number',
        'display_name' => [
            'de_DE' => 'Band',
            'en_GB' => 'Volume'
        ]
    ],
    [
        'name' => 'year-suffix',
        'type' => 'standard',
        'display_name' => [
            'de_DE' => 'Jahrsuffix',
            'en_GB' => 'Year-suffix'
        ]
    ]
];


$LIBRARY_DOCUMENT_TYPES = [
    [
        'name' => 'article-journal',
        'display_name' => [
            'de_DE' => 'Artikel Fachzeitschrift',
            'en_GB' => 'Journal article'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'DOI',
            'ISSN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-title',
            'container-title',
            'container-title-short',
            'editor',
            'issue',
            'issued',
            'language',
            'note',
            'page',
            'reviewed-author',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ],
    [
        'name' => 'article-magazine',
        'display_name' => [
            'de_DE' => 'Zeitschriftenartikel',
            'en_GB' => 'Magazine article'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'ISSN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'container-title',
            'issue',
            'issued',
            'language',
            'note',
            'page',
            'reviewed-author',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ],
    [
        'name' => 'article-newspaper',
        'display_name' => [
            'de_DE' => 'Zeitungsartikel',
            'en_GB' => 'Newspaper article'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'ISSN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'container-title',
            'issued',
            'language',
            'note',
            'page',
            'publisher-place',
            'reviewed-author',
            'section',
            'source',
            'title',
            'title-short',
            'translator',
            'medium'
        ]
    ],
    [
        'name' => 'article',
        'display_name' => [
            'de_DE' => 'Artikel',
            'en_GB' => 'Article'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'editor',
            'issued',
            'language',
            'note',
            'publisher',
            'reviewed-author',
            'source',
            'title',
            'title-short',
            'translator',
            'medium'
        ]
    ],
    [
        'name' => 'book',
        'display_name' => [
            'de_DE' => 'Buch',
            'en_GB' => 'Book'
        ],
        'icon' => 'literature',
        'properties' => [
            'ISBN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-editor',
            'collection-number',
            'collection-title',
            'edition',
            'editor',
            'event-place',
            'issued',
            'language',
            'note',
            'number-of-pages',
            'number-of-volumes',
            'publisher',
            'publisher-place',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ],
    [
        'name' => 'paper-conference',
        'display_name' => [
            'de_DE' => 'Konferenzpapier',
            'en_GB' => 'Conference paper'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'DOI',
            'ISBN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-editor',
            'collection-title',
            'container-title',
            'editor',
            'event',
            'issued',
            'language',
            'note',
            'page',
            'publisher',
            'publisher-place',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ],
    [
        'name' => 'report',
        'display_name' => [
            'de_DE' => 'Bericht',
            'en_GB' => 'Report'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-editor',
            'collection-title',
            'issued',
            'language',
            'note',
            'page',
            'publisher',
            'publisher-place',
            'source',
            'title',
            'title-short',
            'translator',
            'medium'
        ]
    ],
    [
        'name' => 'entry-dictionary',
        'display_name' => [
            'de_DE' => 'Wörterbucheintrag',
            'en_GB' => 'Dictionary entry'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'ISBN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-editor',
            'collection-number',
            'collection-title',
            'container-title',
            'edition',
            'editor',
            'event-place',
            'issued',
            'language',
            'note',
            'number-of-volumes',
            'page',
            'publisher',
            'publisher-place',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ],
    [
        'name' => 'entry-encyclopedia',
        'display_name' => [
            'de_DE' => 'Lexikoneintrag',
            'en_GB' => 'Encyclopedia entry'
        ],
        'icon' => 'bib-text',
        'properties' => [
            'ISBN',
            'URL',
            'abstract',
            'accessed',
            'archive',
            'archive_location',
            'author',
            'call-number',
            'collection-editor',
            'collection-number',
            'collection-title',
            'container-title',
            'edition',
            'editor',
            'event-place',
            'issued',
            'language',
            'note',
            'number-of-volumes',
            'page',
            'publisher',
            'publisher-place',
            'source',
            'title',
            'title-short',
            'translator',
            'volume',
            'medium'
        ]
    ]
];
