<section class="contentbox">
    <header>
        <h1>
            <?= _('Globale Systemkonfigurationen') ?>
            <? if ($needle): ?>
                / <?= _('Suchbegriff:') ?> "<em><?= htmlReady($needle) ?></em>"
            <? endif ?>
        </h1>
    </header>
    <? foreach ($sections as $section => $configs): ?>
        <?php $id = md5(uniqid($section));?>
        <article id="<?= $id?>" <? if ($open_section === $section ||  $only_section || $needle) echo 'class="open"'; ?>>
            <header>
                <h1>
                    <a href="<?= URLHelper::getURL("?#{$id}")?>">
                        <?= $section ?: '- ' . _(' Ohne Kategorie ') . ' -' ?>
                        (<?= count($configs) ?>)
                    </a>
                </h1>
            </header>
            <section>
                <table class="default">
                    <?= $this->render_partial('admin/configuration/table-header.php') ?>
                    <? foreach ($configs as $config): ?>
                        <?= $this->render_partial('admin/configuration/table-row.php', $config) ?>
                    <? endforeach ?>
                </table>
            </section>
        </article>
    <? endforeach ?>
</section>
