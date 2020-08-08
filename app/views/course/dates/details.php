<table class="default nohover">
    <colgroup>
        <col width="25%">
        <col width="75%">
    </colgroup>
    <tbody>
    <? if (count($date->topics) > 0): ?>
        <tr>
            <td><strong><?= _('Thema') ?></strong></td>
            <td>
                <ul class="themen_list">
                <? foreach ($date->topics as $topic) : ?>
                    <?= $this->render_partial('course/dates/_topic_li', compact('topic')) ?>
                <? endforeach ?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
        <tr>
            <td><strong><?= _("Art des Termins") ?></strong></td>
            <td>
                <?= htmlReady($GLOBALS['TERMIN_TYP'][$date['date_typ']]['name']) ?>
            </td>
        </tr>
    <? if (count($date->dozenten) > 0): ?>
        <tr>
            <td><strong><?= _('Durchführende Lehrende') ?></strong></td>
            <td>
                <ul class="dozenten_list clean">
                <? foreach ($date->dozenten as $teacher): ?>
                    <li>
                        <a href="<?= $controller->link_for('profile?username=' . $teacher->username) ?>">
                            <?= Avatar::getAvatar($teacher->user_id)->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($teacher->getFullname()) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
    <? if (count($date->statusgruppen) > 0): ?>
        <tr>
            <td><strong><?= _('Beteiligte Gruppen') ?></strong></td>
            <td>
                <ul>
                <? foreach ($date->statusgruppen as $group): ?>
                    <li><?= htmlReady($group->name) ?></li>
                <? endforeach ;?>
                </ul>
            </td>
        </tr>
    <? endif; ?>
    </tbody>
</table>

<? extract($date->getAccessibleFolderFiles($GLOBALS['user']->id))?>
<? if (count($files) > 0): ?>
    <? $one_folder = current($folders); ?>
    <form method="post" action="<?= $controller->link_for('file/bulk/' . $one_folder->id) ?>">
        <?= CSRFProtection::tokenTag() ?>
        <article class="studip">
            <header>
                <h1><?= _('Dateien') ?></h1>
            </header>
            <section>
                <table id="course_date_files" class="default sortable-table documents" data-sortlist="[[2, 0]]">
                    <?= $this->render_partial('files/_files_thead', ['show_bulk_checkboxes' => true]) ?>
                    <? foreach($files as $file): ?>
                        <? if ($file->isVisible($GLOBALS['user']->id)) : ?>
                            <?= $this->render_partial('files/_fileref_tr', [
                                'file'       => $file,
                                'current_folder' => $folders[$file->getFolderType()->getId()],
                                'last_visitdate' => time(),
                                'show_bulk_checkboxes' => true
                            ]) ?>
                        <? endif ?>
                    <? endforeach ?>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <span class="multibuttons">
                                <?= Studip\Button::create(_('Herunterladen'), 'download', [
                                    'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
                                ]) ?>
                                <?= Studip\Button::create(_('Kopieren'), 'copy', ['data-dialog' => '']) ?>
                                 </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </section>
        </article>
    </form>
    <script>
        STUDIP.Table.enhanceSortableTable($('#course_date_files'));
    </script>
<? endif; ?>
