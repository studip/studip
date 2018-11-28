<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;
?>
<? if (isset($error_msg)): ?>
    <?= MessageBox::error($error_msg) ?>
<? endif ?>

<form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <? if($edit_rubric): ?>
                <?= _('Neue Rubrik anlegen') ?>
            <? else : ?>
                <?= _('Neue Seite anlegen') ?>
            <? endif ?>
        </legend>

        <? if($edit_rubric): ?>
            <label>
                <?= _('Titel der Rubrik') ?>
                <input type="text" name="rubric_name" id="rubric_name">
            </label>
        <? else: ?>
            <label>
                <?= _('Rubrik-Zuordnung') ?>
                <select name="rubric_id">
                    <? foreach ($rubrics as $option) : ?>
                    <option value="<?= $option['rubric_id'] ?>"<? if($currentrubric==$option['rubric_id']){echo " selected";} ?>><?= htmlReady(language_filter($option['name'])) ?></option>
                    <? endforeach ?>
                </select>
            </label>

            <label>
                <?= _('Seitentitel') ?>
                <input style="width: 90%;" type="text" name="detail_name" id="detail_name">
            </label>

            <label>
                <?= _('Seiteninhalt') ?>
                <textarea style="width: 90%;height: 15em;" name="content" id="content"></textarea><br>
            </label>
        <? endif ?>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Abschicken')) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('siteinfo/show/'.$currentrubric)) ?>
    </footer>
</form>

<? if(!$edit_rubric): ?>
    <?= $this->render_partial('siteinfo/help') ?>
<? endif ?>
