<?php

Helpbar::get()->addPlainText(_('Info'),_("Anmeldesets legen fest, wer sich zu den zugeordneten Veranstaltungen anmelden darf."));
Helpbar::get()->addPlainText(_('Info'),_("Hier sehen Sie alle Anmeldesets, auf die Sie Zugriff haben."));

?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<?= $this->render_partial('admission/courseset/_institute_choose.php') ?>
<?php
if ($coursesets) {
?>
<form action="<?= $controller->link_for('admission/courseset/bulk') ?>" method="post">
    <table class="default nohover sortable-table" id="courseset-list">
        <colgroup>
            <col style="width: 24px">
            <col>
            <col style="width: 25%">
            <col style="width: 5%">
            <col style="width: 5%">
            <col style="width: 10%">
            <col style="width: 100px">
        </colgroup>
        <thead>
            <tr>
                <th data-sort="false">
                    <input type="checkbox"
                           data-proxyfor="#courseset-list tbody :checkbox"
                           data-activates="#courseset-list tfoot .button">
                </th>
                <th data-sort="text"><?= _('Name des Sets') ?></th>
                <th data-sort="text"><?= _('Besitzer') ?></th>
                <th data-sort="htmldata"><?= _('Privat') ?></th>
                <th data-sort="numeric"><?= _('Anzahl') ?></th>
                <th data-sort="htmldata"><?= _('Letzte Änderung') ?></th>
                <th data-sort="false" class="actions"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($coursesets as $courseset) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="<?= htmlReady($courseset->getId()) ?>">
                </td>
                <td><?= htmlReady(my_substr($courseset->getName(),0,70)) ?></td>
                <td><?= htmlReady(get_fullname($courseset->getUserId(), 'no_title_rev')) ?></td>
                <td data-sort-value="'<?= $courseset->getPrivate() ? 1 : 0 ?>'">
                    <?= $courseset->getPrivate() ? _('Ja') : _('Nein') ?>
                </td>
                <td><?= count($courseset->getCourses()) ?></td>
                <td data-sort-value="<?= $courseset->getChdate() ?>">
                    <time datetime="<?= date('Y-m-d H:i:s', $courseset->getChdate()) ?>" title="<?= strftime('%x %X', $courseset->getChdate()) ?>">
                        <?= reltime($courseset->getChdate()) ?>
                    </time>
                </td>
                <td class="actions">
                    <a class="load-in-new-row" href="<?= $controller->link_for('', ['course_set_details' => $courseset->getId()]); ?>">
                        <?= Icon::create('info', 'clickable', ['title' => _('Weitere Informationen einblenden')])->asImg() ?>
                    </a>
                    <? if ($courseset->isUserAllowedToEdit($GLOBALS['user']->id)) : ?>
                    <a href="<?= $controller->link_for('admission/courseset/copy/'.$courseset->getId()); ?>">
                            <?= Icon::create('edit+add', 'clickable', ['title' => _('Anmeldeset kopieren')])->asImg(16, ["alt" => _('Anmeldeset kopieren')]); ?>
                        </a>
                    <a href="<?= $controller->link_for('admission/courseset/configure/'.$courseset->getId()); ?>">
                            <?= Icon::create('edit')->asImg(['title' => _('Anmeldeset bearbeiten')]) ?>
                        </a>
                        <a href="<?= $controller->link_for('admission/courseset/delete/'. $courseset->getId(), ['really' => 1]) ?>"
                           data-confirm="<?= sprintf(_('Soll das Anmeldeset %s wirklich gelöscht werden?'), htmlReady($courseset->getName())) ?>">
                            <?= Icon::create('trash')->asImg(['title' => _('Anmeldeset löschen')]) ?>
                        </a>
                     <? endif ?>
                </td>
            </tr>
            <? if ($course_set_details == $courseset->getId()) : ?>
                <tr>
                    <td colspan="7">
                        <?= $courseset->toString() ?>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">
                    <?= Studip\Button::create(_('Löschen'), 'delete', [
                        'data-confirm' => _('Sollen die markierten Anmeldesets wirklich gelöscht werden?'),
                    ]) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
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
