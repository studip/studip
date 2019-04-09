<? use Studip\Button, Studip\LinkButton; ?>
<form action="<?= $controller->edit($banner) ?>" method="post" enctype="multipart/form-data" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <tbody>
            <tr>
                <td class="nohover">
                    <? if ($banner['banner_path']): ?>
                        <?= $banner->toImg() ?>
                    <? else: ?>
                        <?= _('Noch kein Bild hochgeladen') ?>
                    <? endif; ?><br>

                    <label class="file-upload">
                        <?= _('Bilddatei auswählen') ?>
                        <input id="imgfile" name="imgfile" type="file" accept="image/*"><br>
                        <input type="hidden" name="banner_path" value="<?= $banner['banner_path'] ?>"><br>
                    </label>
                </td>

                <td class="nohover" style="vertical-align: top">
                    <fieldset>
                        <legend>
                            <?= _('Banner editieren') ?>
                        </legend>

                        <label>
                            <?= _('Beschreibung:') ?>
                            <input type="text" id="description" name="description"
                                   value="<?= htmlReady($banner['description']) ?>"
                                   size="40" maxlen="254">
                        </label>

                        <label>
                            <?= _('Alternativtext:') ?>

                            <input type="text" id="alttext" name="alttext"
                                   value="<?= htmlReady($banner['alttext']) ?>"
                                   size="40" maxlen="254">
                        </label>

                        <label>
                            <?= _("Verweis-Typ:") ?>

                            <input name="target_type" type="hidden" size="8" value="<?=$banner['target_type']?>">
                            <select name="target_type" disabled="disabled">
                            <? foreach ($target_types as $key => $label): ?>
                                <option value="<?= $key ?>" <? if ($banner['target_type'] == $key) echo 'selected'; ?>>
                                    <?= $label ?>
                                </option>
                            <? endforeach; ?>
                            </select>
                        </label>

                        <label>
                            <?= _("Verweis-Ziel:") ?>

                            <? if (in_array($banner['target_type'], words('none url'))): ?>
                                <input type="text" name="target" size="40" maxlen="254" value="<?= htmlReady($banner['target']) ?>">
                            <? elseif ($banner['target_type'] == "seminar") :?>
                                <?= $seminar ?>
                            <? elseif ($banner['target_type'] == "inst") :?>
                                <?= $institut ?>
                            <? else: ?>
                                <?= $user ?>
                            <? endif; ?>
                        </label>

                        <label>
                            <?= _('Anzeigen ab:') ?>

                            <?= $this->render_partial('admin/banner/datetime-picker', [
                                    'prefix'    => 'start_',
                                    'timestamp' => $banner['startdate']]) ?>
                        </label>

                        <label>
                            <?= _('Anzeigen bis:') ?>

                            <?= $this->render_partial('admin/banner/datetime-picker', [
                                    'prefix'    => 'end_',
                                    'timestamp' => $banner['enddate']]) ?>
                        </label>

                        <label>
                            <?= _('Priorität:')?>

                            <select id="priority" name="priority">
                            <? foreach ($priorities as $key => $label): ?>
                                <option value="<?= $key ?>" <? if ($banner['priority'] == $key) echo 'selected'; ?>>
                                    <?= $label ?>
                                </option>
                            <? endforeach; ?>
                            </select>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>

    <footer data-dialog-button>
        <?= Studip\Button::create(_('Speichern'), 'speichern') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->index("#banner-{$banner->id}")
        ) ?>
    </footer>
</form>
