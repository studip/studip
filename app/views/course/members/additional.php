<? if (!empty($aux['rows'])) : ?>
    <form method="post" action="<?= $controller->url_for('course/members/store_additional')?>">
        <?= CSRFProtection::tokenTag()?>
        <table class="default hide_datafield_title">
            <caption><?= _('Zusatzangaben bearbeiten') ?></caption>
            <thead>
            <tr>
                <? foreach ($aux['head'] as $head): ?>
                    <th><?= $head ?></th>
                <? endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <? foreach ($aux['rows'] as $entry): ?>
                <tr>
                    <? foreach ($aux['head'] as $key => $value): ?>
                        <td><?= $key === 'name' ? htmlReady($entry[$key]) : $entry[$key] ?></td>
                    <? endforeach; ?>
                </tr>
            <? endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?= count($aux['head']) ?>">
                    <?= \Studip\Button::create(_('Speichern'), 'save') ?>
                </td>
            </tr>
            </tfoot>

        </table>
    </form>
<? else : ?>
    <?= MessageBox::info(_('Keine Zusatzangaben oder Teilnehmende vorhanden.')) ?>
<? endif ?>
