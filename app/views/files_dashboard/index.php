<h1 class="sr-only">
    <?= _('Dateien')?>
</h1>

<form action="<?= URLHelper::getURL('dispatch.php/files_dashboard/search') ?>"
      method="post"
      novalidate="novalidate"
      class="default search">

    <label>
        <?= $this->render_partial('files_dashboard/_input-group-search', ['query' => '']) ?>
    </label>
</form>

<?= $container->render() ?>
