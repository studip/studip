<? foreach ($materialien as $material) : ?>
    <?= $this->render_partial("oer/market/_material_short.php", compact("material", "plugin")) ?>
<? endforeach ?>
