<form id="info_search" action="<?= $action ?>" method="post" role="search">
    <script>
        var submitInfoSearch = function () {
            jQuery('#info_search').submit();
        };
    </script>
    <?= $search ?>
    <? if ($reset) : ?>
    <a href="<?= $reset ?>"><?= Icon::create('refresh', 'clickable', ['title' => _('Suche zurücksetzen')])->asImg(); ?></a>
    <? endif; ?>
</form>
