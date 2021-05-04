<? if (empty($materialien)) : ?>
    <?= MessageBox::info(_('Es wurden noch keine Materialien bereitgestellt.')) ?>
<? else : ?>
    <?= $this->render_partial("oer/mymaterial/_material_list.php") ?>
<? endif ?>
