<?php
/**
 * ModulteilDeskriptor.php
 * Model class for Modulteil-Deskriptoren (table mvv_modulteil_deskriptor)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class ModulteilDeskriptor extends ModuleManagementModel
{
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modulteil_deskriptor';
        
        $config['belongs_to']['modulteil'] = [
            'class_name' => 'Modulteil',
            'foreign_key' => 'modulteil_id'
        ];
        
        $config['has_many']['datafields'] = [
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->deskriptor_id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($m) {
                    return [$m];
                }
        ];
        
        $config['i18n_fields']['bezeichnung'] = true;
        $config['i18n_fields']['voraussetzung'] = true;
        $config['i18n_fields']['kommentar'] = true;
        $config['i18n_fields']['kommentar_kapazitaet'] = true;
        $config['i18n_fields']['kommentar_wl_praesenz'] = true;
        $config['i18n_fields']['kommentar_wl_bereitung'] = true;
        $config['i18n_fields']['kommentar_wl_selbst'] = true;
        $config['i18n_fields']['kommentar_wl_pruef'] = true;
        $config['i18n_fields']['pruef_vorleistung'] = true;
        $config['i18n_fields']['pruef_leistung'] = true;
        $config['i18n_fields']['kommentar_pflicht'] = true;
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Modulteil-Deskriptor');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modulteil-Deskriptor');
    }
    
    /**
     * Inherits the status of the parent modulteil.
     * 
     * @see ModuleManagementModel::getStatus()
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        $modulteil = Modulteil::find($this->modulteil_id);
        if ($modulteil) {
            return $modulteil->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
    
    /**
     * Returns the language identifier as the variant of the descriptor object.
     * 
     * @see ModuleManagementModel::getVariant()
     * @return string The language identifier.
     */
    public function getVariant()
    {
        if (self::getLanguage() == $GLOBALS['MVV_MODULTEIL_DESKRIPTOR']['SPRACHE']['default']) {
            return '';
        }
        return self::getLanguage();
    }
    
    /**
     * Deletes the translation in the given language of this descriptor.
     * 
     * @param string $language The language of the translation to delete.
     * @return int The number of deleted translated fields.
     */
    public function deleteTranslation($language)
    {
        $locale = $GLOBALS['MVV_LANGUAGES']['values'][$language]['locale'];
        return I18NString::removeAllTranslations($this->id, 'mvv_modulteil_deskriptor', $locale);
    }
}