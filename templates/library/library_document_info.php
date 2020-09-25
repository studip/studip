<?
$authors = $document->getAuthorNames();
$issue_date = $document->getIssueDate(true);
$identifiers = $document->getIdentifiers();
$url = ($document->download_url ?: $document->document_url);
$is_search = !$document->csl_data;
?>
<? if ($is_search) : ?>
    <?
    $description_fields = $document->getSearchDescription();
    ?>
    <h3><?= _('Suche in der Bibliothek') ?></h3>
    <ul class="default">
        <? foreach ($description_fields as $field) : ?>
            <li><?= htmlReady($field) ?></li>
        <? endforeach ?>
    </ul>
<? else : ?>
    <?= $document->toHtml() ?>
    <? if ($format === 'full') : ?>
        <dl>
            <dt><?= _('Titel') ?></dt>
            <dd><?= htmlReady($document->getTitle()) ?></dd>
            <dt><?= _('Typ') ?></dt>
            <dd><?= htmlReady($document->getType('display_name')) ?></dd>
            <? if ($document->csl_data['issued'] || $document->csl_data['publisher']) : ?>
                <dt><?= _('Veröffentlicht') ?></dt>
                <dd><?= htmlReady($document->csl_data['publisher'] . ' ' . $document->getIssueDate(true)) ?></dd>
            <? endif ?>
            <? if (!empty($document->csl_data['medium'])) : ?>
                <dt><?= _('Medium') ?></dt>
                <dd><?= htmlReady($document->csl_data['medium']) ?></dd>
            <? endif ?>
            <? if ($document->csl_data['author']) : ?>
                <dt><?= _('Erstellt von') ?></dt>
                <dd><?= htmlReady($document->getAuthorNames()) ?></dd>
            <? endif ?>
            <? if ($document->catalog) : ?>
                <dt><?= _('Katalog') ?></dt>
                <dd><?= htmlReady($document->catalog) ?></dd>
            <? endif ?>
        </dl>
    <? endif ?>
<? endif ?>
