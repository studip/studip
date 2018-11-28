<?= $controller->jsUrl() ?>
<div id="mvv-chooser" style="width:100%;">
<? foreach ($lists as $name => $list) : ?>
    <?= $this->render_partial('shared/chooser_form', compact('name', 'list')); ?>
<? endforeach; ?>
</div>
<? if ($last) : ?>
<script>
    <? if (sizeof($filter)) : ?>
        jQuery('#mvv-chooser-toggle').addClass('mvv-chooser-hidden');
    <? endif; ?>
    jQuery('#mvv-chooser-toggle').fadeIn();
</script>
<? endif; ?>