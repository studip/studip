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

/*literature search plugins
----------------------------------------------------------------
If you write your own plugin put it in studip-htdocs/lib/classes/lit_search_plugins
and enable it here. The name of the plugin is the classname excluding "StudipLitSearchPlugin".
If the catalog your plugin is designed for offers the possibility to create a link to an entry, you
could provide the link here. Place templates for the needed attributes in curly braces. (see examples below)*/

//standard plugin, searches in Stud.IP Database (table lit_catalog), you should leave this one enabled !
$_lit_search_plugins[] = ['name' => "Studip",'display_name' =>'Katalog der Stud.IP Datenbank', 'link' => ''];

//Plugins derived from Z3950Abstract, used for querying Z39.50 Servers
//only activate these plugins, if your Version of PHP supports the YAZ extension!

/* Gemeinsamer Verbundkatalog - GVK */
//$_lit_search_plugins[] = array('name' => "Gvk",'display_name' =>'Gemeinsamer Verbundkatalog', 'link' => 'http://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Niedersächsische Staats- und Universitätsbibliothek Göttingen, OPAC */
//$_lit_search_plugins[] = array('name' => "SUBGoeOpac",'display_name' => "Opac der SUB Göttingen" , 'link' => 'http://opac.sub.uni-goettingen.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Göttinger Gesamtkatalog (Regionalkatalog Göttingen) */
//$_lit_search_plugins[] = array('name' => 'Rkgoe', 'display_name' =>'Regionalkatalog Göttingen', 'link' => 'http://gso.gbv.de/DB=2.90/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Technische Informationsbibliothek / Universitätsbibliothek Hannover, OPAC */
//$_lit_search_plugins[] = array('name' => 'TIBUBOpac', 'display_name' =>'Technische Informationsbibliothek / Universitätsbibliothek Hannover', 'link' => 'http://opac.tib.uni-hannover.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}', 'display_name' => "UB Katalog");

/* Hannover Gesamtkatalog (Regionalkatalog Hannover) */
//$_lit_search_plugins[] = array('name' => 'Rkhan', 'display_name' =>'Regionalkatalog Hannover', 'link' => 'http://gso.gbv.de/DB=2.92/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}', 'display_name' => "Gesamtkatalog Hannover");

/* Bibliotheken der Fachhochschule Hildesheim/Holzminden/Göttingen */
//$_lit_search_plugins[] = array('name' => 'FHHIOpac', 'display_name' =>'Bibliotheken der FH Hildesheim/Holzminden/Göttingen', 'link' => 'http://opac.lbs-hildesheim.gbv.de/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Thüringer Universitäts- und Landesbibliothek Jena */
//$_lit_search_plugins[] = array('name' => 'ThULB_Jena', 'display_name' =>'Thüringer Universitäts- und Landesbibliothek Jena', 'link' => 'http://kataloge.thulb.uni-jena.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Jena */
//$_lit_search_plugins[] = array('name' => 'FH_Jena', 'display_name' =>'Bibliothek der FH Jena', 'link' => 'http://kataloge.thulb.uni-jena.de/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universitätsbibliothek der Bauhaus-Universität Weimar */
//$_lit_search_plugins[] = array('name' => 'UB_Weimar', 'display_name' =>'Universitätsbibliothek der Bauhaus-Universität Weimar', 'link' => 'http://opac.ub.uni-weimar.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Herzogin Anna Amalia Bibliothek Weimar */
//$_lit_search_plugins[] = array('name' => 'HAAB_Weimar', 'display_name' =>'Herzogin Anna Amalia Bibliothek Weimar', 'link' => 'http://opac.ub.uni-weimar.de/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Hochschule für Musik Franz Liszt Weimar */
//$_lit_search_plugins[] = array('name' => 'HSfMFL_Weimar', 'display_name' =>'Bibliothek der Hochschule für Musik Franz Liszt Weimar', 'link' => 'http://opac.ub.uni-weimar.de/DB=3/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universitätsbibliothek Erfurt */
//$_lit_search_plugins[] = array('name' => 'UB_Erfurt', 'display_name' =>'Universitätsbibliothek Erfurt', 'link' => 'http://opac.uni-erfurt.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Erfurt */
//$_lit_search_plugins[] = array('name' => 'FH_Erfurt', 'display_name' =>'Bibliothek der FH Erfurt', 'link' => 'http://opac.uni-erfurt.de/DB=4/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Nordhausen */
//$_lit_search_plugins[] = array('name' => 'FH_Nordhausen', 'display_name' =>'Bibliothek der FH Nordhausen', 'link' => 'http://opac.uni-erfurt.de/DB=5/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universitätsbibliothek Ilmenau */
//$_lit_search_plugins[] = array('name' => 'UB_Ilmenau', 'display_name' =>'Universitätsbibliothek Ilmenau', 'link' => 'http://opac.lbs-ilmenau.gbv.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Schmalkalden */
//$_lit_search_plugins[] = array('name' => 'FH_Schmalkalden', 'display_name' =>'Bibliothek der FH Schmalkalden', 'link' => 'http://opac.lbs-ilmenau.gbv.de/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universitäts- und Landesbibliothek Sachsen-Anhalt Halle */
//$_lit_search_plugins[] = array('name' => "Ulb", 'display_name' =>'Universitäts- und Landesbibliothek Sachsen-Anhalt Halle', 'link' => 'http://opac.bibliothek.uni-halle.de/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* FB Technik ULB Halle und FH Merseburg  */
//$_lit_search_plugins[] = array('name' => "FBTechnik", 'display_name' =>'Hochschule Merseburg', 'link' => 'http://opac.bibliothek.uni-halle.de/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universitätsbibliothek Trier */
//$_lit_search_plugins[] = array('name' => 'UB_Trier', 'display_name' =>'BIB-KAT Universität Trier', 'link' => 'http://bibkat.uni-trier.de/F/?func=find-c&local_base=tri01&ccl_term={accession_number}');

/* Südwestdeutscher Bibliotheksverbund SWB Online */
//$_lit_search_plugins[] = array('name' => "Swb", 'display_name' => "SWB Online Katalog", 'link' => 'http://swb.bsz-bw.de/DB=2.1/SET=1/TTL=2/CLK?IKT=12&TRM={accession_number}');

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

/*path generation
-----------------------------------------------------------------
(end of user defined settings)*/
