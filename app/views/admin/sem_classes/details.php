<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

?>
<form action="<?= URLHelper::getLink($overview_url) ?>" method="post" class="default attribute_table collapsable">
    <input type="hidden" id="sem_class_id" value="<?= Request::int("id") ?>">
    <fieldset>
        <legend>
            <?= _('Veranstaltungskategorie bearbeiten') ?>
        </legend>

        <label class="sem_class_name">
            <span><?= _("Name der Veranstaltungskategorie") ?></span>
            <div>
                <span class="name"><?= htmlReady($sem_class['name']) ?></span>
                <a href="#" class="sem_class_edit" onClick="jQuery(this).closest('label').children().toggle().find('input:visible').focus(); return false;"><?= Icon::create('edit', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
            </div>

            <div class="name_input" style="display: none;">
                <input id="sem_class_name" type="text" value="<?= htmlReady($sem_class['name']) ?>" onBlur="jQuery(this).closest('label').children().toggle().find('.name').text(this.value);">
            </div>
        </label>


        <label class="sem_class_name">
            <span><?= _('Beschreibungstext für die Suche') ?></span>

            <div>
                <span class="description"><?= htmlReady($sem_class['description']) ?></span>
                <a href="#" class="sem_class_edit" onClick="jQuery(this).closest('label').children().toggle().find('input:visible').focus(); return false;">
                    <?= Icon::create('edit', 'clickable')->asImg(['class' => 'text-bottom']) ?></a>
            </div>
            <div class="description_input" style="display: none;">
                <input id="sem_class_description" type="text" value="<?= htmlReady($sem_class['description']) ?>" onBlur="jQuery(this).closest('label.sem_class_name').children().toggle().find('.description').text(this.value);" style="width: 80%;">
            </div>
        </label>

        <section class="sem_type_list">
            <span><?= _("Veranstaltungstypen") ?></span>

            <ul id="sem_type_list">
                <? foreach ($sem_class->getSemTypes() as $id => $sem_type) : ?>
                <?= $this->render_partial("admin/sem_classes/_sem_type.php", ['sem_type' => $sem_type]) ?>
                <? endforeach ?>
            </ul>
            <div class="add">
                <div style="display: none; margin-left: 37px;">
                    <input type="text" id="new_sem_type" onBlur="if (!this.value) jQuery(this).closest('.add').children().toggle();">
                    <a href="" onClick="STUDIP.admin_sem_class.add_sem_type(); return false;"><?= Icon::create('arr_2up', 'sort')->asImg(['class' => "text-bottom", "title" => _("hinzufügen")]) ?></a>
                </div>
                <div style="margin-left: 21px;">
                    <a href="#" onClick="jQuery(this).closest('.add').children().toggle(); jQuery('#new_sem_type').focus(); return false;">
                        <?= Icon::create('add', 'clickable')->asImg(['class' => "text-bottom", "title" => _("Veranstaltungstyp hinzufügen")]) ?>
                    </a>
                </div>
            </div>
        </section>



        <? foreach (["dozent","tutor","autor"] as $role) : ?>
        <section>
            <?= sprintf(_("Titel der %s"), $GLOBALS['DEFAULT_TITLE_FOR_STATUS'][$role][1]) ?>

            <label>
                <input type="radio" id="title_<?= $role ?>_isnull" name="title_<?= $role ?>_isnull" value="1"<?= !$sem_class['title_'.$role] && !$sem_class['title_'.$role.'_plural'] ? " checked" : ""?>>
                <?= sprintf(_("Systemdefault (%s)"), htmlReady(implode("/", $GLOBALS['DEFAULT_TITLE_FOR_STATUS'][$role]))) ?>
            </label>

            <div class="hgroup">
                <label>
                    <input type="radio" name="title_<?= $role ?>_isnull" value="0"<?= $sem_class['title_'.$role] || $sem_class['title_'.$role.'_plural'] ? " checked" : ""?>>
                    <input placeholder="<?= htmlReady($GLOBALS['DEFAULT_TITLE_FOR_STATUS'][$role][0]) ?>" title="<?= _("Singular") ?>" type="text" id="title_<?= $role ?>" name="title_<?= $role ?>" value="<?= htmlReady($sem_class['title_'.$role]) ?>">
                    <input placeholder="<?= htmlReady($GLOBALS['DEFAULT_TITLE_FOR_STATUS'][$role][1]) ?>" title="<?= _("Plural") ?>" type="text" id="title_<?= $role ?>_plural" name="title_<?= $role ?>_plural" value="<?= htmlReady($sem_class['title_'.$role.'_plural']) ?>">
                </label>
            </div>
        </section>
        <? endforeach ?>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Voreinstellungen beim Anlegen einer Veranstaltung") ?>
        </legend>

        <label>
            <?= _("Lesbar für Nutzer") ?>
            <select id="default_read_level">
                <option value="0"<?= $sem_class['default_read_level'] == 0 ? " selected" : "" ?>><?= _("Unangemeldet an Veranstaltung") ?></option>
                <option value="1"<?= $sem_class['default_read_level'] == 1 ? " selected" : "" ?>><?= _("Angemeldet an Veranstaltung") ?></option>
            </select>
        </label>

        <label>
            <?= _("Schreibbar für Nutzer") ?>
            <select id="default_write_level">
                <option value="0"<?= $sem_class['default_write_level'] == 0 ? " selected" : "" ?>><?= _("Unangemeldet an Veranstaltung") ?></option>
                <option value="1"<?= $sem_class['default_write_level'] == 1 ? " selected" : "" ?>><?= _("Angemeldet an Veranstaltung") ?></option>
            </select>
        </label>

        <label>
            <?= _("Anmeldemodus") ?>
            <select id="admission_prelim_default">
                <option value="0"<?= $sem_class['admission_prelim_default'] == 0 ? " selected" : "" ?>><?= _("direkter Eintrag") ?></option>
                <option value="1"<?= $sem_class['admission_prelim_default'] == 1 ? " selected" : "" ?>><?= _("vorläufiger Eintrag") ?></option>
            </select>
        </label>

        <label>
            <?= _("Anmeldung gesperrt") ?>
            <select id="admission_type_default">
                <option value="0"<?= $sem_class['admission_type_default'] == 0 ? " selected" : "" ?>><?= _("Nein") ?></option>
                <option value="3"<?= $sem_class['admission_type_default'] == 3 ? " selected" : "" ?>><?= _("Ja") ?></option>
            </select>
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Forum") ?>
        </legend>

        <label>
            <input type="checkbox" id="topic_create_autor" value="1"<?= $sem_class['topic_create_autor'] ? " checked" : "" ?>>
            <?= _("Autoren dürfen Themen anlegen.") ?>
        </label>

        <label>
            <input type="checkbox" id="write_access_nobody" value="1"<?= $sem_class['write_access_nobody'] ? " checked" : "" ?>>
            <?= _("Unangemeldete Nutzer (nobody) dürfen posten.") ?>
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Anzeige") ?>
        </legend>

        <label>
            <input type="checkbox" id="visible" value="1"<?= $sem_class['visible'] ? " checked" : "" ?>>
            <?= _("Sichtbar") ?>
        </label>

        <label>
            <input type="checkbox" id="show_browse" value="1"<?= $sem_class['show_browse'] ? " checked" : "" ?>>
            <?= _("Zeige im Veranstaltungsbaum an.") ?>
        </label>

        <label>
            <input type="checkbox" id="show_raumzeit" value="1"<?= $sem_class['show_raumzeit'] ? " checked" : "" ?>>
            <?= _("Zeige Raum-Zeit-Seite an.") ?>
        </label>
    </fieldset>

    <fieldset>
        <legend>
            <?= _("Sonstiges") ?>
        </legend>

        <label>
            <input type="checkbox" id="studygroup_mode" value="1"<?= $sem_class['studygroup_mode'] ? " checked" : "" ?>>
            <?= _("Studentische Arbeitsgruppe") ?>
        </label>

        <label>
            <input type="checkbox" id="only_inst_user" value="1"<?= $sem_class['only_inst_user'] ? " checked" : "" ?>>
            <?= _("Nur Nutzer der Einrichtungen sind erlaubt.") ?>
        </label>

        <label>
            <input type="checkbox" id="bereiche" value="1"<?= $sem_class['bereiche'] ? " checked" : "" ?>>
            <?= _("Muss Studienbereiche haben (falls nein, darf es keine haben)") ?>
        </label>

        <label>
            <input type="checkbox" id="module" value="1"<?= $sem_class['module'] ? " checked" : "" ?>>
            <?= _("Kann Modulen zugeordnet werden.") ?>
        </label>

        <label>
            <input type="checkbox" id="course_creation_forbidden" value="1"<?= $sem_class['course_creation_forbidden'] ? " checked" : "" ?>>
            <?= _("Anlegeassistent für diesen Typ sperren.") ?>
        </label>

        <label>
            <?= _("Kurzer Beschreibungstext zum Anlegen einer Veranstaltung") ?>
            <textarea id="create_description" maxlength="200" style="width: 100%"><?= htmlReady($sem_class['create_description']) ?></textarea>
        </label>

        <label>
            <input type="checkbox" id="is_group" value="1"<?= $sem_class['is_group'] ? " checked" : "" ?>>
            <?= _("Kann Unterveranstaltungen haben") ?>
        </label>
    </fieldset>

    <fieldset class="collapsed attribute_table">
        <legend>
            <?= _("Inhaltselemente") ?>
        </legend>
        <? $container = [
            'overview' => ['name' => _("Übersicht")],
            'admin' => ['name' => _("Verwaltung")],
            'forum' => ['name' => _("Forum")],
            'participants' => ['name' => _("Teilnehmendenseite")],
            'documents' => ['name' => _("Dateibereich")],
            'schedule' => ['name' => _("Terminseite")],
            'literature' => ['name' => _("Literaturübersicht")],
            'scm' => ['name' => _("Freie Informationen")],
            'wiki' => ['name' => _("Wiki")],
            'resources' => ['name' => _("Ressourcen")],
            'calendar' => ['name' => _("Kalender")],
            'elearning_interface' => ['name' => _("Lernmodule")]
        ];
        ?>
        <? foreach ($container as $container_id => $container_attributes) : ?>

        <div container="<?= $container_id ?>" class="core_module_slot">
            <h2><?= htmlReady($container_attributes['name']) ?></h2>
            <div class="droparea limited<?= $sem_class->getSlotModule($container_id) !== null ? " full" : "" ?>">
                <? if ($sem_class->getSlotModule($container_id) !== null) : ?>
                    <?= $this->render_partial("admin/sem_classes/content_plugin.php",
                        [
                            'plugin' => $modules[$sem_class->getSlotModule($container_id)],
                            'sem_class' => $sem_class,
                            'plugin_id' => $sem_class->getSlotModule($container_id),
                            'activated' => $sem_class['modules'][$sem_class->getSlotModule($container_id)]['activated'],
                            'sticky' => $sem_class['modules'][$sem_class->getSlotModule($container_id)]['sticky']
                        ]
                    )?>
                <? unset($modules[$sem_class->getSlotModule($container_id)]) ?>
                <? endif ?>
            </div>
        </div>
        <? endforeach ?>
        <br>
        <div container="plugins" id="plugins">
            <h2 title="<?= _("Diese Plugins sind standardmäßig bei den Veranstaltungen dieser Klasse aktiviert.") ?>"><?= _("Plugins") ?></h2>
            <div class="droparea">
                <? foreach ($modules as $module_name => $module_info) : ?>
                <? $module_attribute = $sem_class->getModuleMetadata($module_name); ?>
                <? if (is_numeric($module_info['id'])) : ?>
                    <?= $this->render_partial("admin/sem_classes/content_plugin.php",
                        [
                            'plugin' => $module_info,
                            'sem_class' => $sem_class,
                            'plugin_id' => $module_name,
                            'activated' => $sem_class['modules'][$module_name]['activated'],
                            'sticky' => $sem_class['modules'][$module_name]['sticky']
                        ]
                    )?>
                <? endif ?>
                <? endforeach ?>
            </div>
        </div>
        <hr>
        <div container="deactivated" id="deactivated_modules">
            <h2 title="<?= _("Diese Module sind standardmäßig nicht aktiviert.") ?>"><?= _("Nichtaktivierte Inhaltselemente") ?></h2>
            <div class="droparea">
                <? foreach ($modules as $module_name => $module_info) {
                    $module_id = $module_info['id'];
                    if (!is_numeric($module_id) && !$sem_class['modules'][$module_id]['activated']) {
                        echo $this->render_partial("admin/sem_classes/content_plugin.php",
                            [
                                'plugin' => $module_info,
                                'sem_class' => $sem_class,
                                'plugin_id' => $module_id,
                                'activated' => $sem_class['modules'][$module_id]['activated'],
                                'sticky' => $sem_class['modules'][$module_id]['sticky']
                            ]
                        );
                    }
                } ?>
            </div>
        </div>
    </fieldset>

    <footer>
        <div id="message_below"></div>

        <?= Studip\Button::create(_("Speichern"), "save", ['onClick' => "STUDIP.admin_sem_class.saveData(); return false;"])?>
        <? if ($sem_class->countSeminars() === 0) : ?>
            <input type="hidden" name="delete_sem_class" value="<?= Request::int("id") ?>">
            <?= Studip\Button::create(_("Löschen"), "delete", ['onClick' => "return window.confirm('"._("Wirklich löschen?")."');"])?>
        <? endif ?>
    </footer>
</form>

<div id="sem_type_delete_question_title" style="display: none;"><?= _("Sicherheitsabfrage") ?></div>
<div id="sem_type_delete_question" style="display: none;">
    <p class="info"><?= _("Wirklich den Veranstaltungstyp löschen?") ?></p>
    <input type="hidden" id="sem_type_for_deletion">
    <?= Studip\LinkButton::create(_("Löschen"), ['onclick' => "STUDIP.admin_sem_class.delete_sem_type(); return false;"]) ?>
    <?= Studip\LinkButton::create(_("Abbrechen"), ['onclick' => "jQuery(this).closest('#sem_type_delete_question').dialog('close'); return false;"]) ?>
</div>



<?
$sidebar = Sidebar::Get();
$sidebar->setTitle(PageLayout::getTitle());
$sidebar->setImage('sidebar/plugin-sidebar.png');
