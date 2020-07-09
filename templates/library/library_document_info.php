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
    <? if ($document->catalog) : ?>
        <div>
            <?= _('Katalog') ?>:
            <?= htmlReady($document->catalog) ?>
        </div>
    <? endif ?>
<? endif ?>
