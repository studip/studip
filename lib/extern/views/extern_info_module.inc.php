<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * extern_info_module.inc.php
 *
 *
 *
 *
 * @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
 * @access       public
 * @modulegroup  extern
 * @module       extern_info_module
 * @package      studip_extern
 */

use Studip\Button, Studip\LinkButton;

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_info_module.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

$info = ExternConfig::GetInfo($range_id, $config_id);
?>
<table class="default">
    <caption>
        <?= _('Allgemeine Daten') ?>
    </caption>
    <tr>
        <td><?= _('Modulname') ?></td>
        <td><?= htmlReady($info["module_name"]) ?></td>
    </tr>
    <tr>
        <td><?= _('Name der Konfiguration') ?></td>
        <td><?= htmlReady($info["name"]) ?></td>
    </tr>
    <tr>
        <td><?= _('Erstellt am') ?></td>
        <td><?= $info["make_date"] ?></td>
    </tr>
    <tr>
        <td><?= _('Letzte Änderung') ?></td>
        <td><?= $info["change_date"] ?></td>
    </tr>
    <tr>
        <td><?= _('Beschreibung') ?></td>
        <td><?= $EXTERN_MODULE_TYPES[$info["module_type"]]["description"] ?></td>
    </tr>
</table>
<?

if ($info['module_type'] != 0)  :?>
    <? if ($info['level'] == 1) : ?>
        <p><strong><?= _('Direkter Link') ?></strong></p>
        <blockquote><a href="<?= $info['link'] ?>" target="_blank" rel="noopener noreferrer"><?= $info['link_br'] ?></a></blockquote>
        <p><?= _('Diese Adresse können Sie in einen Link auf Ihrer Website integrieren, um auf die Ausgabe des Moduls zu verweisen.') ?></p>
    <? endif ?>

    <? if (Config::get()->EXTERN_SRI_ENABLE && sri_is_enabled(Context::getId())) : ?>
        <p><strong><?= _("Stud.IP-Remote-Include (SRI)  Schnittstelle") ?></strong></p>
        <p><?= _('Der unten aufgeführte Textblock ermöglicht Ihnen den Zugriff auf die Stud.IP-Remote-Include-Schnittstelle (SRI).') ?></p>
        <blockquote>
            <pre><?= $info['sri'] ?></pre>
        </blockquote>
        <p><?= _('Kopieren Sie dieses Code-Schnipsel in eine beliebige Stelle im HTML-Quelltext einer Seite Ihrer Website.') ?></p>
        <p><?= _('Durch eine spezielle Art des Seitenaufrufs, wird an dieser Stelle die Ausgabe des Moduls eingefügt.') ?></p>
        <p><strong><?= _('Link zur SRI-Schnittstelle') ?></strong></p>
        <blockquote>
            <?= $info['link_sri'] ?>
        </blockquote>
        <p><?= sprintf(_('Ersetzen Sie %s durch die URL der Seite, in die Sie die Ausgabe des Moduls einfügen wollen. Diese Seite muss obigen Code-Schnipsel enthalten.'), _('URL_DER_INCLUDE_SEITE')) ?></p>
    <? endif ?>
<? endif ?>


<?= LinkButton::create('<<  ' . _('Zurück'), URLHelper::getURL('', ['list' => true])); ?>
