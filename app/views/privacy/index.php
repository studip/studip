<? if (empty($plugin_data) || empty(reset($plugin_data))): ?>
    <?= MessageBox::info(_('In dieser Kategorie sind keine Daten vorhanden.')) ?>
<? endif; ?>

<? foreach ($plugin_data as $label => $tabledata) : ?>
    <? if ($tabledata['table_content']) : ?>
        <article class="studip toggle <?  if (!Request::isDialog() && $section) echo 'open'; ?>">
            <header>
                <h1>
                    <a>
                        <?= htmlReady($label) ?>,
                        <?= sprintf(_('%u Einträge'), count($tabledata['table_content'])) ?>
                    </a>
                </h1>
            <? if (Request::isDialog()) : ?>
                <a href="<?= $controller->link_for("privacy/export2csv/{$tabledata['table_name']}/{$user_id}") ?>">
                    <strong><?= htmlReady($label) ?> CSV</strong>
                </a>
            <? endif; ?>
            </header>
            <section>
                <table class="default">
                    <thead>
                        <tr>
                        <? foreach (array_keys($tabledata['table_content'][0]) as $caption) : ?>
                            <th><?= htmlReady($caption) ?></th>
                        <? endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <? foreach ($tabledata['table_content'] as $row) : ?>
                        <tr>
                        <? foreach ($row as $key => $value): ?>
                            <td>
                                <?= htmlReady($value) ?>
                            </td>
                        <? endforeach; ?>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </section>
        </article>

    <? endif; ?>
<? endforeach; ?>

<div data-dialog-button>
<? if (Request::isDialog()): ?>
    <?= Studip\LinkButton::create(_('Zurück'), $controller->url_for("privacy/landing/{$user_id}"), ['data-dialog' => 'size=medium']); ?>
<? else: ?>
    <?= Studip\LinkButton::create(_('Zurück'), $controller->url_for("privacy/landing/{$user_id}")); ?>
<? endif; ?>
</div>
