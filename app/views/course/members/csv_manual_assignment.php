<? use Studip\Button, Studip\LinkButton; ?>
<h1><?=_('Manuelle Zuordnung')?></h1>
<?= (isset($flash['error'])) ? MessageBox::error($flash['error']) : '' ?>
<?= (isset($flash['success'])) ? MessageBox::success($flash['success']) : '' ?>
<?= (isset($flash['info'])) ? MessageBox::info($flash['info']) : '' ?>
<form action="<?= $controller->url_for('course/members/set_autor_csv')?>" method="post" name="user" class="default">
<?= CSRFProtection::tokenTag() ?>
<table class="default">
    <thead>
        <tr>
            <th class="topic" colspan="2"><?=sprintf(_('Folgende %s konnten <b>nicht eindeutig</b> zugewiesen werden. Bitte wählen Sie aus der jeweiligen Trefferliste:'), htmlReady($status_groups['autor']))?></th>
        </tr>
    </thead>

    <tbody>
        <? foreach($flash['csv_mult_founds'] as $name => $csv_mult_found) : ?>
        <tr>
            <td style="width: 40%"><?=htmlReady(mila($name, 50));?></td>
            <td>
                <select name="selected_users[]">
                    <option value="---">--<?=_('bitte auswählen')?> --</option>
                    <? foreach ($csv_mult_found as $csv_found) : ?>
                        <? if ($csv_found['is_present']) : ?>
                            <? continue ?>
                        <? endif?>
                        <option value="<?=$csv_found['username']?>"><?=htmlReady(my_substr($csv_found['fullname'], 0, 50))?> (<?=$csv_found['username']?>) - <?=$csv_found['perms']?></option>
                    <? endforeach ?>
                </select>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="2">
                <?= Button::createAccept(_('Eintragen'))?>
            </td>
        </tr>
    </tfoot>
</table>
</form>
