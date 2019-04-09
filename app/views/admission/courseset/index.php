<?php

Helpbar::get()->addPlainText(_('Info'),_("Anmeldesets legen fest, wer sich zu den zugeordneten Veranstaltungen anmelden darf."));
Helpbar::get()->addPlainText(_('Info'),_("Hier sehen Sie alle Anmeldesets, auf die Sie Zugriff haben."));

?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<?= $this->render_partial('admission/courseset/_institute_choose.php') ?>
<?php
if ($coursesets) {
?>
<table class="default nohover">
    <tr>
        <th width="60%"><?= _('Name des Sets') ?></th>
        <th width="25%"><?= _('Besitzer') ?></th>
        <th width="5%"><?= _('Privat')?></th>
        <th width="5%"><?= _('Anzahl')?></th>
        <th style="text-align:center"><?= _('Aktionen') ?></th>
    </tr>
    <? foreach ($coursesets as $courseset) : ?>
    <tr>
        <td><?= htmlReady(my_substr($courseset->getName(),0,70)) ?></td>
        <td><?= htmlReady(get_fullname($courseset->getUserId(), 'no_title_rev')) ?></td>
        <td><?= $courseset->getPrivate() ? _('Ja') : _('Nein') ?></td>
        <td><?= count($courseset->getCourses()) ?></td>
        <td>
        <div style="width:100px;text-align:right;white-space: nowrap">
            <a class="load-in-new-row" href="<?= $controller->link_for('', ['course_set_details' => $courseset->getId()]); ?>">
                <?= Icon::create('info', 'clickable', ['title' => _('Weitere Informationen einblenden')])->asImg() ?>
            </a>
            <? if ($courseset->isUserAllowedToEdit($GLOBALS['user']->id)) : ?>
            <a href="<?= $controller->link_for('admission/courseset/copy/'.$courseset->getId()); ?>">
                    <?= Icon::create('edit+add', 'clickable', ['title' => _('Anmeldeset kopieren')])->asImg(16, ["alt" => _('Anmeldeset kopieren')]); ?>
                </a>
            <a href="<?= $controller->link_for('admission/courseset/configure/'.$courseset->getId()); ?>">
                    <?= Icon::create('edit', 'clickable', ['title' => _('Anmeldeset bearbeiten')])->asImg(16, ["alt" => _('Anmeldeset bearbeiten')]); ?>
                </a>
                <a href="<?= $controller->link_for('admission/courseset/delete/'.
                    $courseset->getId()) ?>"
                    onclick="return STUDIP.Dialogs.showConfirmDialog('<?=
                        sprintf(_('Soll das Anmeldeset %s wirklich gelöscht werden?'), htmlReady($courseset->getName())) ?>', '<?=
                        URLHelper::getURL('dispatch.php/admission/courseset/delete/'.
                        $courseset->getId(), ['really' => 1]) ?>')">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Anmeldeset löschen')])->asImg(16, ["alt" => _('Anmeldeset löschen')]); ?>
                </a>
             <? endif ?>
        </div>
        </td>
    </tr>
    <? if ($course_set_details == $courseset->getId()) : ?>
        <tr>
            <td colspan="5">
                <?= $courseset->toString() ?>
            </td>
        </tr>
    <? endif ?>
    <? endforeach ?>
</table>
<?php
} else {
?>
<?= MessageBox::info(sprintf(_('Es wurden keine Anmeldesets gefunden. Sie können ein '.
    'neues %sAnmeldeset anlegen%s.'), '<a href="'.
    $controller->url_for('admission/courseset/configure').'">',
    '</a>')); ?>
<?php
}
?>
