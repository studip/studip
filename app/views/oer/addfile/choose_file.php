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
?>
<form class="oer_search"
      action="<?= $controller->link_for("oer/market/search") ?>"
      method="GET"
      data-searchresults="<?= htmlReady(json_encode($material_data)) ?>"
      data-filteredtag="<?= htmlReady(Request::get("tag")) ?>"
      data-filteredcategory="<?= htmlReady(Request::get("category")) ?>"
      data-tags="<?= htmlReady(json_encode($tags)) ?>"
      data-material_select_url_template="<?= htmlReady($controller->url_for("oer/addfile/choose_file", ['material_id' => "__material_id__", 'to_plugin' => Request::get("to_plugin"), 'to_folder_id' => Request::get("to_folder_id")])) ?>">
    <input type="hidden" name="to_plugin" value="<?= htmlReady(Request::get("to_plugin")) ?>">
    <input type="hidden" name="to_folder_id" value="<?= htmlReady(Request::get("to_folder_id")) ?>">
    <?= $this->render_partial("oer/market/_searchform") ?>
</form>
