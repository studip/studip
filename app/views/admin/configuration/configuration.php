<table class="default collapsable">
    <caption>
        <?= _('Verwaltung von Systemkonfigurationen') ?>
    <? if ($needle): ?>
        / <?= _('Suchbegriff:') ?> "<em><?= htmlReady($needle) ?></em>"
    <? endif; ?>
    </caption>
    <?= $this->render_partial('admin/configuration/table-header.php') ?>
<? foreach ($sections as $section => $configs): ?>
    <tbody <? if ($open_section !== $section && !($only_section || $needle)) echo 'class="collapsed"'; ?>>
        <tr class="table_header header-row">
            <th class="toggle-indicator" colspan="4">
                <a class="toggler" href="<?= $controller->url_for('admin/configuration/configuration/' . $section) ?>">
                    <?= $section ?: '- '._(' Ohne Kategorie ').' -' ?>
                    (<?= count($configs) ?>)
                </a>
            </th>
        </tr>
    <? foreach ($configs as $config): ?>
        <?= $this->render_partial('admin/configuration/table-row.php', $config) ?>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
