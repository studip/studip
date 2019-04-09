<?
# Lifter010: TODO
$_id = htmlReady(implode('_', (array) $area->getId()));
?>
<li id="lvgruppe_search_<?= $_id ?>" class="<?= TextHelper::cycle('odd', 'even') ?>">

<? if (!$inlist) : ?>
   <?= Icon::create('arr_2left', 'sort')->asInput(["name" => 'assign['.$_id.']', "onclick" => "return MVV.CourseWizard.assignNode('".$_id."')",
       "class" => in_array($_id,$values['studyareas']?:[])?'hidden-no-js':'']) ?>
<? endif; ?>
   <?/*  <span class="lvgruppe_selection_expand">*/?>
    <?= htmlReady($area->getDisplayName()) ?>
 	<?= Icon::create('info', 'clickable')->asInput(["name" => 'lvgruppe_search[details]['.$_id.']',
                "onclick" => "return MVV.CourseWizard.showSearchDetails('".$_id."')", "class" => '',
                "data-id" => $_id, "data-course_id" => htmlReady($course_id)]) ?>
 <?/*
    </span>*/?>

</li>
