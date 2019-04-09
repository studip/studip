<?php
/**
 * ModulDeskriptor.php
 * Model class for Moduldeskriptoren (table mvv_modul_deskriptor)
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

class ModulDeskriptor extends ModuleManagementModel
{
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modul_deskriptor';
    
        $config['belongs_to']['modul'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'modul_id'
        ];
        
        $config['has_many']['datafields'] = [
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($m) {
                    return [$m];
                }
        ];
        
        $config['i18n_fields']['verantwortlich'] = true;
        $config['i18n_fields']['bezeichnung'] = true;
        $config['i18n_fields']['voraussetzung'] = true;
        $config['i18n_fields']['kompetenzziele'] = true;
        $config['i18n_fields']['inhalte'] = true;
        $config['i18n_fields']['literatur'] = true;
        $config['i18n_fields']['links'] = true;
        $config['i18n_fields']['kommentar'] = true;
        $config['i18n_fields']['turnus'] = true;
        $config['i18n_fields']['kommentar_kapazitaet'] = true;
        $config['i18n_fields']['kommentar_sws'] = true;
        $config['i18n_fields']['kommentar_wl_selbst'] = true;
        $config['i18n_fields']['kommentar_wl_pruef'] = true;
        $config['i18n_fields']['kommentar_note'] = true;
        $config['i18n_fields']['pruef_vorleistung'] = true;
        $config['i18n_fields']['pruef_leistung'] = true;
        $config['i18n_fields']['pruef_wiederholung'] = true;
        $config['i18n_fields']['ersatztext'] = true;
        
        parent::configure($config);
    }
    
    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Modul-Deskriptor');
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Modul-Deskriptor');
    }
    
    /**
     * Inherits the status of the parent module.
     * 
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        
        $modul = Modul::find($this->modul_id);
        if ($modul) {
            return $modul->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
    
    public function getResponsibleInstitutes()
    {
        $institutes = [];
        $modul_insts = ModulInst::findByModul($this->modul_id, 'hauptverantwortlich');
        foreach ($modul_insts as $modul_inst) {
            $institute = Institute::find($modul_inst->institut_id);
            if ($institute) {
                $institutes[] = $institute;
            }
        }
        return $institutes;
    }
    
    /**
     * Returns the language identifier as the variant of the descriptor object.
     * 
     * @see ModuleManagementModel::getVariant()
     * @return string The language identifier.
     */
    public function getVariant()
    {
        if (self::getLanguage() == $GLOBALS['MVV_MODUL_DESKRIPTOR']['SPRACHE']['default']) {
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
        return I18NString::removeAllTranslations($this->id, 'mvv_modul_deskriptor', $locale);
    }
}