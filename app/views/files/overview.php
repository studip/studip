<? if ($current_view == 'overview') : ?>
    <?= $this->render_partial('files/_overview') ?>
<? else : ?>
    <?= $this->render_partial('files/flat') ?>
<? endif ?>
