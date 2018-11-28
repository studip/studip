<?
$_id = htmlReady(implode('_', (array) $area->getId()));
?>
<li id="lvgruppe_selection_<?= $_id ?>">
<? if (!$locked) : ?>
    <input title="<?= _('Zuordnung entfernen') ?>"
        data-id="<?= $_id ?>" data-course_id="<?= htmlReady($course_id) ?>"
        style="vertical-align: text-top;"
        type="image"
        name="lvgruppe_selection[remove][<?= $_id ?>]"
        src="<?= Icon::create('trash', 'clickable')->asImagePath(); ?>">
<? endif; ?>
    <span class="lvgruppe_selection_expand">
    <?= htmlReady($area->getDisplayName()) ?>
        <input title="<?= _('Alle Zuordnungen anzeigen') ?>"
            data-id="<?= $_id ?>" data-course_id="<?= htmlReady($course_id) ?>"
            style="vertical-align: text-top;"
            type="image"
            name="lvgruppe_selection[details][<?= $_id ?>]"
            src="<?= Icon::create('info', 'clickable')->asImagePath() ?>">
    </span>
    <ul>
    <? if (Request::isXhr()) : ?>
    <?= $this->render_partial('course/lvgselector/entry_trails') ?>
    <? endif; ?>
    </ul>
    <input type="hidden" name="lvgruppe_selection[areas][]" class="lvgruppe_selection_area" value="<?= $_id ?>">
</li>
