<div class="file_select_possibilities">
<? foreach ($configure_urls as $url) : ?>
    <a href="<?= htmlReady($url['url']) ?>" data-dialog>
        <?= $url['icon']->asImg(50) ?>
        <?= htmlReady($url['name']) ?>
    </a>
<? endforeach ?>
</div>
