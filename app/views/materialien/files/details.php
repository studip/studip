<td colspan="10">
    <table class="default nohover">
        <tr>
            <td><?= _('Jahr'); ?></td>
            <td><?= htmlReady($doc_year); ?></td>
        </tr>

        <tr>
            <td><?= _('Art der Datei '); ?></td>
            <td><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['TYPE']['values'][$doc_type]['name']); ?></td>
        </tr>

        <tr>
            <td><?= _('Dokumente'); ?></td>
            <td>
                <ul class="list-unstyled">
                <? foreach($documents as $key => $document): ?>
                    <li>
                        <?= Assets::img('languages/lang_' . mb_strtolower($key) . '.gif') ?>
                        <b><?= htmlReady($document->getDisplayName()); ?></b>
                        <? if($document->file_ref->isLink()): ?>
                            <a href="<?= htmlReady($document->getFilename()); ?>" target="_blank"><?= htmlReady($document->getFilename()); ?></a>
                        <? else: ?>
                            <?= htmlReady($document->getFilename()); ?>
                        <? endif; ?>
                    </li>
                <? endforeach; ?>
                </ul>
            </td>
        </tr>

        <tr>
            <td><?= _('Kategoriezuordnung'); ?></td>
            <td><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['CATEGORY']['values'][$doc_cat]['name']); ?></td>
        </tr>

        <tr>
            <td><?= _('Schlagwörter'); ?></td>
            <td>
                <? if ($doc_tags): ?>
                <ul>
                <? foreach(explode(';', $doc_tags) as $tag): ?>
                    <li><?= htmlReady($GLOBALS['MVV_DOCUMENTS']['TAG']['values'][$tag]['name']); ?></li>
                <? endforeach; ?>
                </ul>
                <? endif; ?>
            </td>
        </tr>

        <tr>
            <td><?= _('Sichtbarkeit nach außen'); ?></td>
            <td><?= $doc_extvisible?_('sichtbar'):_('unsichtbar'); ?></td>
        </tr>

        <tr>
            <td><?= _('Zuordnungen'); ?></td>
            <td>


            <? if (!sizeof($relations)) : ?>
                <?= _('Das Dokument wurde noch nicht referenziert.') ?>
            <? else : ?>
                <? foreach ($relations as $object_type => $relation) : ?>
                    <strong><?= htmlReady($object_type::getClassDisplayName()) ?></strong>
                    <ul class="default">
                    <? foreach ($relation as $rel) : ?>
                        <? $related_object = $object_type::find($rel['range_id']) ?>
                        <li>
                            <a href="<?= $this->controller->url_for('materialien/files/dispatch', mb_strtolower($object_type), $rel['range_id']) ?>">
                                <?= $related_object->getDisplayName() ?>
                            </a>
                        </li>
                    <? endforeach; ?>
                    </ul>
                <? endforeach; ?>
            <? endif; ?>

            </td>
        </tr>

    </table>
</td>
