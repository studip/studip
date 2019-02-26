<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <? if ($edit_rubric): ?>
                <?= _('Rubrik bearbeiten') ?>
            <? else : ?>
                <?= _('Seite bearbeiten') ?>
            <? endif ?>
        </legend>

        <? if ($edit_rubric): ?>
            <input type="hidden" name="rubric_id" value="<?= htmlReady($rubric_id) ?>">
            <label>
                <?= _('Titel der Rubrik')?>
                <input type="text" name="rubric_name" id="rubric_name" value="<?= htmlReady($rubric_name) ?>">
            </label>
        <? else: ?>
            <label>
                <?= _('Rubrik-Zuordnung')?>
                <select name="rubric_id">

                <? foreach ($rubrics as $option): ?>
                    <option value="<?= htmlReady($option['rubric_id']) ?>" <? if ($currentrubric == $option['rubric_id']) echo 'selected'; ?>>
                        <?= htmlReady(language_filter($option['name'])) ?>
                    </option>
                <? endforeach; ?>
                </select>
            </label>

            <label>
                <?= _('Seitentitel')?>
                <input type="text" name="detail_name" id="detail_name" value="<?= htmlReady($detail_name) ?>">
            </label>

            <label>
                <?= _('Seiteninhalt')?>
                <textarea style="height: 15em;" name="content" id="content" class="add_toolbar size-l wysiwyg"><?= wysiwygReady($content) ?></textarea>
            </label>

            <input type="hidden" name="detail_id" value="<?= $currentdetail?>">
        <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Abschicken')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('siteinfo/show/'.$currentrubric.'/'.$currentdetail)) ?>
    </footer>
</form>

 <? if (!$edit_rubric): ?>
    <?= $this->render_partial('siteinfo/help.php') ?>
<? endif; ?>
