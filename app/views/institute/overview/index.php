<article class="studip">
    <header>
        <h1><?= _('Grunddaten') ?></h1>
    </header>
    <section>
        <dl style="margin: 0">
        <? if ($institute->strasse) : ?>
            <dt><?= _('Straße') ?></dt>
            <dd><?= htmlReady($institute->strasse) ?></dd>
        <? endif ?>

        <? if ($institute->Plz) : ?>
            <dt><?= _('Ort') ?></dt>
            <dd><?= htmlReady($institute->Plz) ?></dd>
        <? endif ?>

        <? if ($institute->telefon) : ?>
            <dt><?=_('Telefon')?></dt>
            <dd><?= htmlReady($institute->telefon) ?></dd>
        <? endif ?>

        <? if ($institute->fax) : ?>
            <dt><?= _('Fax') ?></dt>
            <dd><?= htmlReady($institute->fax) ?></dd>
        <? endif ?>

        <? if ($institute->url) : ?>
            <dt><?= _('Homepage') ?></dt>
            <dd><?= htmlReady($institute->url) ?></dd>
        <? endif ?>

        <? if ($institute->email) : ?>
            <dt><?= _('E-Mail') ?></dt>
            <dd><?= htmlReady($institute->email) ?></dd>
        <? endif ?>

        <? if ($institute->fakultaets_id) : ?>
            <dt><?= _('Fakultät') ?></dt>
            <dd><?= htmlReady($institute->faculty->name) ?></dd>
        <? endif ?>

        <? foreach ($institute->datafields->getTypedDatafield() as $entry): ?>
            <? if ($entry->isVisible() && $entry->getValue()): ?>
                <dt><?= htmlReady($entry->getName()) ?></dt>
                <dd><?= $entry->getDisplayValue() ?></dd>
            <? endif?>
        <? endforeach ?>
        </dl>
    </section>
</article>

<?= $news ?>
<?= $dates ?>
<?= $evaluations ?>
<?= $questionnaires ?>

<?
// display plugins
$plugins = PluginEngine::getPlugins('StandardPlugin', $institute_id);
$layout = $GLOBALS['template_factory']->open('shared/index_box');

foreach ($plugins as $plugin) {
    $template = $plugin->getInfoTemplate($institute_id);

    if ($template) {
        echo $template->render(NULL, $layout);
        $layout->clear_attributes();
    }
}
