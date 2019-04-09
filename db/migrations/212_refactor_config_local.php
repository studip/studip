<?php

class RefactorConfigLocal extends Migration
{

    public function description()
    {
        return 'Inserts some entries from the config_local.inc.php and config.inc.php into database.';
    }

    public function up()
    {
        $global_boolean_parameter = [
            "UNI_NAME_CLEAN" => [
                'description' => "Name der Stud.IP-Installation bzw. Hochschule.",
                'type' => "string",
                'default' => "Stud.IP"
            ],
            "STUDIP_INSTALLATION_ID" => [
                'description' => "Unique identifier for installation",
                'type' => "string",
                'default' => "demo-installation"
            ],
            "MEDIA_CACHE_MAX_LENGTH" => [
                'description' => "Maximale Größe von Dateien, die im Media-Cache gecached werden (in Bytes)?",
                'type' => "integer",
                'default' => 1000000
            ],
            "MEDIA_CACHE_LIFETIME" => [
                'description' => "Wieviele Sekunden soll gecached werden?",
                'type' => "integer",
                'default' => 86400
            ],
            "MEDIA_CACHE_MAX_FILES" => [
                'description' => "Wieviele Dateien sollen maximal gecached werden?",
                'type' => "integer",
                'default' => 3000
            ],
            "XSLT_ENABLE" => [
                'description' => "Soll Export mit XSLT angeschaltet sein?",
                'type' => "boolean",
                'default' => 1
            ],
            "FOP_ENABLE" => [
                'description' => "Soll Export mit FOP erlaubt sein?",
                'type' => "boolean",
                'default' => 1
            ],
            "EXTERN_SRI_ENABLE" => [
                'description' => "Allow the usage of SRI-interface (Stud.IP Remote Include)",
                'type' => "boolean",
                'default' => 1
            ],
            "EXTERN_SRI_ENABLE_BY_ROOT" => [
                'description' => "Only root allows the usage of SRI-interface for specific institutes",
                'type' => "boolean",
                'default' => 0
            ],
            "EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG" => [
                'description' => "Free access to external pages (without the need of a configuration), independent of SRI settings above",
                'type' => "boolean",
                'default' => 0
            ],
            "SOAP_ENABLE" => [
                'description' => "Schaltet die SOAP-Schnittstelle an.",
                'type' => "boolean",
                'default' => 0
            ],
            "SOAP_USE_PHP5" => [
                'description' => "Sollen PHP-Bibliotheken für SOAP verwendet werden?",
                'type' => "boolean",
                'default' => 0
            ],
            "ALLOW_SELFASSIGN_STUDYCOURSE" => [
                'description' => "If true, students are allowed to set or change their studycourse (studiengang)",
                'type' => "boolean",
                'default' => 1
            ],
            "SHOW_TERMS_ON_FIRST_LOGIN" => [
                'description' => "If true, the user has to accept the terms on his first login (this feature makes only sense, if you use disable ENABLE_SELF_REGISTRATION).",
                'type' => "boolean",
                'default' => 0
            ],
            "CONVERT_IDNA_URL" => [
                'description' => "If true, urls with german \"umlauts\" are converted",
                'type' => "boolean",
                'default' => 1
            ],
            "USER_VISIBILITY_CHECK" => [
                'description' => "Enable presentation of visibility decision texts for users after first login. see lib/include/header.php and lib/user_visible.inc.php for further info",
                'type' => "boolean",
                'default' => 0
            ],
            "USERNAME_REGULAR_EXPRESSION" => [
                'description' => "Regex for allowed characters in usernames",
                'type' => "string",
                'default' => '/^([a-zA-Z0-9_@.-]{4,})$/'
            ],
            "DEFAULT_TIMEZONE" => [
                'description' => "What timezone should be used (default: Europe/Berlin)?",
                'type' => "string",
                'default' => 'Europe/Berlin'
            ],
            "DEFAULT_LANGUAGE" => [
                'description' => "Which language should we use if we can gather no information from user?",
                'type' => "string",
                'default' => 'de_DE'
            ],
            "ALLOW_CHANGE_USERNAME" => [
                'description' => "If true, users are allowed to change their username",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "ALLOW_CHANGE_EMAIL" => [
                'description' => "If true, users are allowed to change their email",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "ALLOW_CHANGE_NAME" => [
                'description' => "If true, users are allowed to change their name",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "ALLOW_CHANGE_TITLE" => [
                'description' => "If true, users are allowed to change their titles",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "ENABLE_SELF_REGISTRATION" => [
                'description' => "Should it be possible for an user to register himself",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "ENABLE_REQUEST_NEW_PASSWORD_BY_USER" => [
                'description' => "If true, users are able to request a new password themselves",
                'type' => "boolean",
                'section' => "permissions",
                'default' => 1
            ],
            "PHPASS_USE_PORTABLE_HASH" => [
                'description' => "PHPASS_USE_PORTABLE_HASH",
                'type' => "boolean",
                'default' => 0
            ],
            "WEBSERVICES_ENABLE" => [
                'description' => "Schaltet die Webservice-Schnittstelle an.",
                'type' => "boolean",
                'default' => 0
            ],
            "ENABLE_FREE_ACCESS" => [
                'description' => "If true, courses with public access are available",
                'type' => "boolean",
                'default' => 1
            ],
        ];

        $stmt_value = DBManager::get()->prepare("
            INSERT IGNORE INTO config
                (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
            VALUES
                (:config_id, :name, :value, '0', :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
        ");
        $stmt_default = DBManager::get()->prepare("
            INSERT IGNORE INTO config
                (config_id, field, value, is_default, type, `range`, section, mkdate, chdate, description)
            VALUES
                (:config_id, :name, :value, '1', :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
        ");

        foreach ($global_boolean_parameter as $name => $data) {
            $option = [
                'config_id'   => md5($name),
                'name'        => $name,
                'type'        => $data['type'],
                'value'       => $data['default'],
                'range'       => 'global',
                'section'     => $data['section'] ?: 'global',
                'description' => $data['description']
            ];
            $stmt_default->execute($option);
            if (isset($GLOBALS[$name]) && $GLOBALS[$name] != $option['value']) {
                $option['config_id'] = md5($name . '___VALUE');
                $option['value'] = $GLOBALS[$name];
                $stmt_value->execute($option);
            }
        }
    }
}
