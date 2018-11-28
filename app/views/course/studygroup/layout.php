<? $this->set_layout($GLOBALS['template_factory']->open('layouts/base')); ?>
<? if ($deactivate_modules_names) : ?>
    <?= $this->render_partial("course/studygroup/edit_dialogue") ?>
<? endif ?>
<?= $content_for_layout ?>

