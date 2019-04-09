<?php
/**
 * DataFieldI18NEntry.php
 * Provides functionality for datafields with i18n support.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 *
 */
abstract class DataFieldI18NEntry extends DataFieldEntry
{

    /**
     * Constructs this datafield
     *
     * @param DataField $datafield Underlying model
     * @param mixed $range_id Range id (or array with range id and secondary
     *                        range id)
     * @param mixed     $value     Value
     */
    public function __construct(DataField $datafield = null, $rangeID = '', $value = null)
    {
        $this->model = $datafield;

        if (is_array($rangeID)) {
            $object_id = [$datafield->id, $rangeID[0], $rangeID[1]];
        } else {
            $object_id = [$datafield->id, $rangeID, ''];
        }
        $value = I18NStringDatafield::load($object_id, null, null);

        $this->rangeID = $rangeID;
        $this->value   = isset($value) ? $value : $datafield->default_value;
    }

    /**
     * Sets the prefered content language if this is an i18n datafield.
     *
     * @param string $language The prefered display language
     */
    public function setContentLanguage($language)
    {
        if ($language && $language == reset(array_keys(Config::get()->CONTENT_LANGUAGES))) {
            $language = '';
        }
        if ($language && !Config::get()->CONTENT_LANGUAGES[$language]) {
            throw new InvalidArgumentException('Language not configured.');
        }

        $this->language = $language;
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return htmlReady((string) $this->getValue(), true, true);
        }

        return (string) $this->getValue();
    }

    /**
     * Returns the input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = [])
    {
        return parent::getHTML($name, $variables + [
            'locale_names' => $this->getLocaleNames($name)
        ]);
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        $metadata = [
            'object_id' => [
                $this->model->id,
                (string) $this->getRangeID(),
                (string) $this->getSecondRangeID()
            ],
            'table' => null,
            'field' => null
        ];
        $translations = $submitted_value;
        $base = $submitted_value['base'];
        unset($translations['base']);
        $i18n_entry = new I18NStringDatafield($base);
        $i18n_entry->setMetadata($metadata);
        $i18n_entry->setTranslations($translations);
        $i18n_entry->setOriginal($base);
        parent::setValueFromSubmit($i18n_entry);
    }

    /**
     * Stores this datafield entry
     *
     * @return int representing the number of changed entries
     */
    public function store()
    {
        $id = [
            $this->model->id,
            (string) $this->getRangeID(),
            (string) $this->getSecondRangeID(),
            ''
        ];
        $entry = new DatafieldEntryModelI18N($id);

        $old_value = I18NStringDatafield::load([$entry->datafield_id,
            $entry->range_id, $entry->sec_range_id], null, null);
        $entry->content = $this->getValue();
        if ($entry->content->original() == $this->model->default_value
                && count($entry->content->toArray()) == 0) {
            $result = $entry->delete();
        } else {
            $result = $entry->store();
        }

        if ($result) {
            NotificationCenter::postNotification('DatafieldDidUpdate', $this, [
                'changed'   => $result,
                'old_value' => $old_value,
            ]);
        }

        return $result;
    }

    /**
     * Returns an array containing the names for the html element by locale.
     *
     * @param string $name Base name of the element
     * @return array
     */
    protected function getLocaleNames($name)
    {
        $locale_names = [];
        foreach (array_keys($GLOBALS['CONTENT_LANGUAGES']) as $index => $locale) {
            $locale_names[$locale] = sprintf(
                '%s[%s][%s]',
                $name,
                $this->model->id,
                $index ? $locale : 'base'
            );
        }
        return $locale_names;
    }
}
