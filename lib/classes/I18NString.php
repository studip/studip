<?php

/**
 * I18NString class
 */
class I18NString
{

    /**
     * Text in default content language.
     *
     * @var string
     */
    protected $base;

    /**
     * Text in additional languages.
     *
     * @var array|null
     */
    protected $lang;

    /**
     * Database info for id, table, field.
     *
     * @var array
     */
    protected $metadata;

    /**
     * Holds the language the content is translated into.
     *
     * @var string
     */
    protected static $content_language = null;

    /**
     * Holds the language the content is translated into by default.
     *
     * @var string
     */
    protected static $default_language = null;

    /**
     * Initialize a new I18NString instance.
     *
     * @param string    $base   Text in default content language.
     * @param array     $lang   Text in additional languages.
     * @param array     $metadata Database info for id, table, field.
     */
    public function __construct($base, $lang = null, $metadata = [])
    {
        $this->base = $base;
        $this->lang = $lang;
        $this->metadata = $metadata;
    }

    /**
     * Return the text representation of this i18n field in selected language.
     * The language is selected by self::content_language (with precendence)
     * or by $_SESSION['_language'].
     *
     * @returns string
     */
    public function __toString()
    {
        if (self::$content_language) {
            return $this->localized(self::$content_language) ?? (string) $this->base;
        } else {
            if (isset($_SESSION['_language'])
                && $_SESSION['_language'] != self::getDefaultLanguage()
                && $this->translation($_SESSION['_language'])) {
                    return $this->translation($_SESSION['_language']);
            }
        }

        return (string) $this->base;
    }

    /**
     * Sets the language the content is translated into.
     *
     * @param string $language
     */
    public static function setContentLanguage($language)
    {
        self::$content_language = $language;
    }

    /**
     * Returns the language the contnet is translated into.
     *
     * @return string The language the content is translated into.
     */
    public static function getContentLanguage()
    {
        return self::$content_language ?: self::getDefaultLanguage();
    }

    /**
     * Sets the default language the content is translated into. The default is
     * normally defined by the first entry in $GLOBALS['CONTENT_LANGUAGES'] (see
     * config_defaults.inc.php).
     *
     * @param string $language
     */
    public static function setDefaultLanguage($language = null)
    {
        self::$default_language = $language ?: key($GLOBALS['CONTENT_LANGUAGES']);
    }

    /**
     * Returns the language all values are translated into by default. The
     * language ist normally defined in $GLOBALS['CONTENT_LANGUAGES'] (see
     * config_defaults.inc.php).
     *
     * @return string The default language all values are translated into.
     */
    public static function getDefaultLanguage()
    {
        return self::$default_language ?: key($GLOBALS['CONTENT_LANGUAGES']);
    }

    /**
     * Sets the original (untranslated) value of this i18n field.
     *
     * @param string $text The original value.
     * @return string The original value.
     */
    public function setOriginal($text)
    {
        return $this->base = $text;
    }

    /**
     * Sets all translations of this i18n field.
     *
     * @param array $lang An array with languages as keys and translations
     * as values.
     * @return array The array with translations.
     */
    public function setTranslations($lang)
    {
        return $this->lang = $lang;
    }

    /**
     * Sets the metadata (database info for id, table, field) of this i18n field.
     *
     * @param array $metadata Database info for id, table, field.
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Return the string in the default content language.
     *
     * @return string String in default content language.
     */
    public function original()
    {
        return $this->base;
    }

    /**
     * Return the string in the specified additional language.
     *
     * @param string The additional language.
     * @return string The translated value.
     */
    public function translation($lang)
    {
        return $this->toArray()[$lang];
    }

    /**
     * Returns the string in the specified language (additional languages and
     * default languages).
     *
     * @param string $lang Additional language or default language.
     * @return string The localized string.
     */
    public function localized($lang)
    {
        if ($lang == self::getDefaultLanguage()) {
            return $this->base;
        }

        return $this->translation($lang);
    }

    /**
     * Sets the translation for the given language. If the given language is
     * the default language, sets the original.
     *
     * @param type $text The translated or original value.
     * @param type $lang The additional or default language.
     * @return string The translated or original value.
     * @throws InvalidArgumentException
     */
    public function setLocalized($text, $lang)
    {
        if ($lang == self::getDefaultLanguage()) {
            return $this->setOriginal($text);
        }

        if (!Config::get()->CONTENT_LANGUAGES[$lang]) {
            throw new InvalidArgumentException('Language not configured.');
        }

        return $this->lang[$lang] = $text;
    }

    /**
     * Return an array containing the text in all additional languages.
     *
     * @return array The array with translations.
     */
    public function toArray()
    {
        if (is_null($this->lang)) {
            $object_id = $this->metadata['object_id'];
            $table = $this->metadata['table'];
            $field =  $this->metadata['field'];
            if (!$table || !$field) {
                throw new RuntimeException('fetching translations not possible, metadata is missing');
            }
            $this->lang = self::fetchDataForField($object_id, $table, $field);
        }
        return $this->lang;
    }

    /**
     * Trim all language strings
     *
     * @param string $symbols All symbols to trim.
     * @return I18NString Returns this.
     */
    public function trim($symbols = " \t\n\r\0\x0B")
    {
        foreach ($this->lang as &$lang) {
            $lang = trim($lang, $symbols);
        }
        $this->base = trim($this->base, $symbols);
        return $this;
    }

    /**
     * Stores the i18n String manually in the database
     *
     */
    public function storeTranslations()
    {
        if (is_array($this->lang)) {
            $db = DBManager::get();
            $object_id = $this->metadata['object_id'];
            $table = $this->metadata['table'];
            $field = $this->metadata['field'];
            if (!$object_id || !$table || !$field) {
                throw new RuntimeException('store not possible, metadata is missing');
            }
            /* Replace translations */
            $deleted = $db->execute("DELETE FROM i18n WHERE object_id = ? AND `table` = ? AND field = ?", [$object_id, $table, $field]);
            $i18nSQL = $db->prepare("INSERT INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) VALUES (?,?,?,?,?)");
            foreach ($this->lang as $lang => $value) {
                if (mb_strlen($value)) {
                    $i18nSQL->execute([$object_id, $table, $field, $lang, (string) $value]);
                }
            }
        }
    }

    /**
     * Removes all translations for this I18NString object.
     *
     */
    public function removeTranslations()
    {
        $this->lang = [];
        $this->storeTranslations();
    }

    /**
     * Returns an I18NString object by given object_id, table and field.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @param string $field The name of the i18n field.
     * @param string $base Sets the original value or retrieve it from database
     * if null.
     * @return I18NString The I18NString object.
     */
    public static function load($object_id, $table, $field, $base = null)
    {
        $db = DBManager::get();
        if (is_null($base)) {
            // Find pk
            SimpleORMap::tableScheme($table);
            if (count(SimpleORMap::$schemes[$table]['pk']) > 1) {
                throw new RuntimeException(sprintf('table %s has multiple primary key, not implemented yet', $table));
            } else {
                $pk = SimpleORMap::$schemes[$table]['pk'][0];
            }
            $base = $db->fetchColumn("SELECT `$field` FROM `$table` WHERE `$pk` = ?", [$object_id]);
        }
        return new self($base, self::fetchDataForField($object_id, $table, $field), compact('object_id', 'table', 'field'));
    }

    /**
     * Retrieves all translations of one field.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @param string $field The name of the i18n field.
     * @return array An array with language as key and translation as value.
     */
    public static function fetchDataForField($object_id, $table, $field)
    {
        $db = DBManager::get();
        $values = $db->fetchPairs("SELECT `lang`, `value` FROM `i18n` WHERE `object_id` = ? AND `table` = ? AND `field` = ?", [$object_id, $table, $field]);
        $data = [];
        foreach (array_keys(Config::get()->CONTENT_LANGUAGES) as $lang) {
            if ($lang != self::getDefaultLanguage()) {
                $data[$lang] = mb_strlen($values[$lang]) ? $values[$lang] : null;
            }
        }
        return $data;
    }

    /**
     * Retrieves all translations of all fields for given object (by id) and
     * table.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @return array An array with all translations of all fields grouped by
     * field.
     */
    public static function fetchDataForRow($object_id, $table)
    {
        $db = DBManager::get();
        return $db->fetchGrouped("SELECT `field`, `lang`, `value` FROM `i18n` WHERE `object_id` = ? AND `table` = ?", [$object_id, $table]);
    }

    /**
     * Removes all translations by given object id and table name. Accepts the
     * language as third parameter to remove only translations to this language.
     *
     * @param string $object_id The id of the sorm object.
     * @param string $table The table name.
     * @param string $lang Optional name of language.
     * @return int The number of deleted translations.
     */
    public static function removeAllTranslations($object_id, $table, $lang = null)
    {
        $db = DBManager::get();
        if ($lang) {
            return $db->execute('DELETE FROM `i18n` '
                    . 'WHERE `object_id` = ? AND `table` = ? AND `lang` = ?',
                    [$object_id, $table, $lang]);
        }
        return $db->execute('DELETE FROM `i18n` '
                . 'WHERE `object_id` = ? AND `table` = ?',
                [$object_id, $table]);
    }
}
