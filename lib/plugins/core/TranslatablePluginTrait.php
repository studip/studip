<?php
/**
 * Trait used to allow plugins to be translated in a generic way.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since Stud.IP 5.0
 */
trait TranslatablePluginTrait
{
    protected $translation_domain = null;

    /**
     * Initializes the translation for the plugin.
     *
     * @param string $domain
     */
    protected function initializeTranslation($domain)
    {
        bindtextdomain($domain, $this->getPluginPath() . '/locale');
        bind_textdomain_codeset($domain, 'UTF-8');
    }

    /**
     * Returns the defined translation domain from plugin manifest. If none
     * is set, false is returned.
     *
     * @return false|string The translation domain from manifest, if set
     */
    protected function getTranslationDomain()
    {
        if ($this->translation_domain === null) {
            $manifest = $this->getMetadata();
            $this->translation_domain = $manifest['localedomain'] ?? false;

            if ($this->translation_domain !== false) {
                $this->initializeTranslation($this->translation_domain);
            }
        }
        return $this->translation_domain;
    }

    /**
     * Returns whether the plugin has a translation defined or not.
     *
     * @return bool
     */
    public function hasTranslation()
    {
        return $this->getTranslationDomain() !== false;
    }

    /**
     * Plugin localization for a single string.
     *
     * @param string $string String to translate
     * @return string
     */
    public function _($string)
    {
        $domain = $this->getTranslationDomain();
        if (!$domain) {
            return $string;
        }

        $result = dgettext($domain, $string);

        // Fallback to possible translations from core system
        if ($result === $string) {
            $result = _($string);
        }

        return $result;
    }

    /**
     * Plugin localization for plural strings.
     *
     * @param string $string0 String to translate (singular)
     * @param string $string1 String to translate (plural)
     * @param mixed  $n       Quantity factor (may be an array or array-like)
     * @return string
     */
    public function _n($string0, $string1, $n)
    {
        if (is_array($n)) {
            $n = count($n);
        }

        $domain = $this->getTranslationDomain();
        if (!$domain) {
            return $n == 1 ? $string0 : $string1;
        }

        $result = dngettext($domain, $string0, $string1, $n);

        // Fallback to possible translations from core system
        if ($result === $string0 || $result === $string1) {
            $result = ngettext($string0, $string1, $n);
        }

        return $result;
    }
}
