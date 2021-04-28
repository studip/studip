<? if ($waiting_list) : ?>
    <?= $this->render_partial('my_courses/waiting_list.php', compact('waiting_list')) ?>
<? endif ?>

<div class="my-courses-vue-app">
    <my-courses />
</div>

<? if (count($my_bosses) > 0) : ?>
    <?= $this->render_partial('my_courses/_deputy_bosses'); ?>
<? endif ?>
