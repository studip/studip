<form class="default" action="<?= $action_url ?>" method="post" name="jump_to">
    <input type="hidden" name="action" value="<?= $action ?>">

    <section class="hgroup">
        <?= _('Gehe zu:') ?>
        <input size="10" style="width: 16em;" type="text" id="jmp_date" name="jmp_date" type="text" value="<?= strftime('%x', $atime)?>">
        <?= Icon::create('accept', 'clickable')->asInput(['class' => 'text-top']) ?>
    </section>
</form>

<script>
    jQuery('#jmp_date').datepicker();
</script>
