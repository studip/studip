<? if ($materialien) : ?>
    <ul class="oer_material_overview mainlist">
        <?= $this->render_partial("oer/market/_materials.php", compact("material", "plugin")) ?>
    </ul>
<? else : ?>
    <?= MessageBox::info(_('Keine Materialien gefunden')) ?>
<? endif ?>
