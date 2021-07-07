<?
if ($best_nine_tags && count($best_nine_tags) > 0) {
    $tags = [];
    foreach ($best_nine_tags as $tag) {
        $tags[] = [
            'tag_hash' => $tag['tag_hash'],
            'name' => $tag['name']
        ];
    }
}
if ($materialien !== null) {
    $material_data = [];
    foreach ($materialien as $material) {
        $data = $material->toRawArray();

        $data['tags'] = array_map(function($tag) {
            return $tag['name'];
        }, $material->getTopics());

        $data['logo_url'] = $material->getLogoURL();
        $data['download_url'] = $material->getDownloadUrl();
        $material_data[] = $data;
    }
}
?>
<form class="oer_search"
      action="<?= $controller->link_for("oer/market/search") ?>"
      method="GET"
      data-searchresults="<?= htmlReady(json_encode($material_data)) ?>"
      data-filteredtag="<?= htmlReady(Request::get("tag")) ?>"
      data-filteredcategory="<?= htmlReady(Request::get("category")) ?>"
      data-tags="<?= htmlReady(json_encode($tags)) ?>"
      data-material_select_url_template="<?= htmlReady($controller->url_for('oer/market/details/__material_id__')) ?>">
    <?= $this->render_partial("oer/market/_searchform") ?>
</form>


<? if ($new_ones) : ?>
    <div id="new_ones">
        <h2><?= _('Neuste Materialien') ?></h2>
        <ul class="oer_material_overview">
            <?= $this->render_partial("oer/market/_materials.php", ['materialien' => $new_ones]) ?>
        </ul>
    </div>
<? endif ?>






<?
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions = new ActionsWidget();
    $actions->addLink(
        _('Eigenes Lernmaterial hochladen'),
        $controller->url_for("oer/mymaterial/edit"),
        Icon::create("add", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "1"]
    );
    $actions->addLink(
        $abo ? _('Neuigkeiten abbestellen') : _('Neuigkeiten abonnieren'),
        $controller->url_for("oer/market/abo"),
        $abo ? Icon::create("rss+decline", Icon::ROLE_CLICKABLE) : Icon::create("rss", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "size=small"]
    );
    Sidebar::Get()->addWidget($actions);
}
