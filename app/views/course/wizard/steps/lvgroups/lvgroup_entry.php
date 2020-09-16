<?
# Lifter010: TODO
$_id = htmlReady(implode('_', (array) $area->getId()));
?>
<li id="lvgroup-tree-assigned-<?= $_id ?>" class="<?= TextHelper::cycle('odd', 'even') ?>">
<? if (!$locked) : ?>
   <?= Icon::create('trash', 'clickable')->asInput(["name" => 'lvgruppe_selection[remove]['.$_id.']', "onclick" => "return STUDIP.MVV.CourseWizard.removeLVGroup('".$_id."')", "class" => '',
       "data-id" => $_id, "data-course_id" => htmlReady($course_id)]) ?>
<? endif; ?>
    <span class="lvgruppe_selection_expand">
    <?= htmlReady($area->getDisplayName()) ?>
	<?= Icon::create('info', 'clickable')->asInput(["name" => 'lvgruppe_selection[details]['.$_id.']',
                "onclick" => "return STUDIP.MVV.CourseWizard.showDetails('".$_id."')", "class" => '',
                "data-id" => $_id, "data-course_id" => htmlReady($course_id)]) ?>

    </span>
    <? if(isset($selection_details) && key_exists($_id, $selection_details)): ?>
    <ul id="lvgruppe_selection_detail_<?= $_id ?>"><?= $selection_details[$_id] ?></ul>
    <? else: ?>
    <ul id="lvgruppe_selection_detail_<?= $_id ?>" style="display:none;"></ul>
    <? endif; ?>
    <input type="hidden" name="lvgruppe_selection[areas][]" class="lvgruppe_selection_area" value="<?= $_id ?>">
</li>
