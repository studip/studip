<?
# Lifter002: DONE - not applicable
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: DONE - not applicable
/**
* language functions
*
* helper functions for handling I18N system
*
*
* @author       Stefan Suchi <suchi@data-quest.de>
* @access       public
* @modulegroup  library
* @module       language.inc
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// language.inc.php
// helper functions for handling I18N system
// Copyright (c) 2003 Stefan Suchi <suchi@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


/**
* This function tries to find the preferred language
*
* This function tries to find the preferred language.
* It returns the first accepted language from browser settings, which is installed.
*
* @access   public
* @return       string  preferred user language, given in "en_GB"-style
*
*/
function get_accepted_languages() {
    global $INSTALLED_LANGUAGES;

    $_language = Config::get()->DEFAULT_LANGUAGE;
    $accepted_languages = explode(",", getenv("HTTP_ACCEPT_LANGUAGE"));
    if (is_array($accepted_languages) && count($accepted_languages)) {
        foreach ($accepted_languages as $temp_accepted_language) {
            foreach ($INSTALLED_LANGUAGES as $temp_language => $temp_language_settings) {
                if (mb_substr(trim($temp_accepted_language), 0, 2) == mb_substr($temp_language, 0, 2)) {
                    $_language = $temp_language;
                    break 2;
                }
            }
        }
    }
    return $_language;
}


/**
* This function starts output via i18n system in the given language
*
* This function starts output via i18n system in the given language.
* It returns the path to the choosen language.
*
* @access   public
* @param        string  the language to use for output, given in "en_GB"-style
* @return       string  the path to the language file, given in "en"-style
*
*/
function init_i18n($_language) {
    global $_language_domain, $INSTALLED_LANGUAGES;

    if (isset($_language_domain) && isset($_language)) {
        $_language_path = $INSTALLED_LANGUAGES[$_language]["path"];
        setLocaleEnv($_language, $_language_domain);
    }
    return $_language_path;
}


/**
 * retrieves preferred language of user from database, falls back to default
 * language
 *
 * @access   public
 * @param    string  the user_id of the user in question
 * @return   string  the preferred language of the user or the default language
 */
function getUserLanguage($uid)
{
    // try to get preferred language from user, fallback to default
    $query = "SELECT preferred_language FROM user_info WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$uid]);
    $language = $statement->fetchColumn() ?: Config::get()->DEFAULT_LANGUAGE;

    return $language;
}

/**
 * Retrieves the path for the preferred language of a user which is specified
 * by his/her ID.
 *
 * This method can be used for sending language specific mails to other users.
 *
 * @param string  the user_id of the recipient (function will try to get
 *                preferred language from database)
 * @return string the path to the language files, given in "en"-style
 */
function getUserLanguagePath($uid)
{
    // First we get the language code in the format language_Country, e.g. de_DE
    $lang_code = getUserLanguage($uid);

    // Now we test if a directory with that language code exists in the locale
    // directory
    if (is_dir("{$GLOBALS['STUDIP_BASE_PATH']}/locale/{$lang_code}")) {
        return $lang_code;
    }

    // There is no directory containing country specific translations for the
    // language. Now we have to check if a general translation exists for the
    // language
    $lang = explode('_', $lang_code)[0];
    if (is_dir("{$GLOBALS['STUDIP_BASE_PATH']}/locale/{$lang}")) {
        //A general translation exists:
        return $lang;
    }

    // No directory exists that has a translation for the language.
    // Our last resort is to use the path index in $INSTALLED_LANUGAGES:
    return $GLOBALS['INSTALLED_LANGUAGES'][$lang_code]['path'];
}

/**
* switch i18n to different language
*
* This function switches i18n system to a different language.
* Should be called before writing strings to other users into database.
* Use restoreLanguage() to switch back.
*
* @access   public
* @param        string  the user_id of the recipient (function will try to get preferred language from database)
* @param        string  explicit temporary language (set $uid to FALSE to switch to this language)
*/
function setTempLanguage ($uid = FALSE, $temp_language = "") {
    global $_language_domain;

    if ($uid) {
        $temp_language = getUserLanguage($uid);
    }

    if ($temp_language == "") {
        // we got no arguments, best we can do is to set system default
        $temp_language = Config::get()->DEFAULT_LANGUAGE;
    }

    setLocaleEnv($temp_language, $_language_domain);
}


/**
* switch i18n back to original language
*
* This function switches i18n system back to the original language.
* Should be called after writing strings to other users via setTempLanguage().
*
* @access   public
*/
function restoreLanguage() {
    global $_language_domain;
    setLocaleEnv($_SESSION['_language'] ?: Config::get()->DEFAULT_LANGUAGE, $_language_domain);
}

/**
* set locale to a given language and select translation domain
*
* This function tries to set the appropriate environment variables and
* locale settings for the given language and also (optionally) sets the
* translation domain.
*
* @access   public
*/
function setLocaleEnv($language, $language_domain = ''){
    putenv('LANGUAGE'); //unset language preference 
    $ret = setlocale(LC_ALL, $language . '.UTF-8');
    setlocale(LC_NUMERIC, 'C');
    if ($language_domain) {
        bindtextdomain($language_domain, $GLOBALS['STUDIP_BASE_PATH'] . "/locale");
        textdomain($language_domain);
        bind_textdomain_codeset($language_domain, 'utf-8');
    }
    return $ret;
}
