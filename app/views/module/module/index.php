<?= $controller->jsUrl() ?>
<table class="default collapsable">
    <caption>
        <?= _('Module')?>
        <span class="actions"><?= sprintf(ngettext('%s Modul', '%s Module', $count), $count) ?></span>
    </caption>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('module/module/', _('Modulcode'), 'code', ['style' => 'width: 7%; white-space: nowrap;']) ?>
            <?= $controller->renderSortLink('module/module/', _('Modul'), 'bezeichnung') ?>
            <?= $controller->renderSortLink('module/module/', _('Fassung'), 'fassung_nr', ['style' => 'width: 5%;']) ?>
            <?= $controller->renderSortLink('module/module/', _('Modulteile'), 'count_modulteile', ['style' => 'width: 5%;']) ?>
            <th style="text-align: right; width: 150px;">
                <?= _('Ausgabesprachen') ?>
            </th>
            <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <?= $this->render_partial('module/module/module') ?>
    <? if ($count > MVVController::$items_per_page) : ?>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;">
                    <?
                    $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                    $pagination->clear_attributes();
                    $pagination->set_attribute('perPage', MVVController::$items_per_page);
                    $pagination->set_attribute('num_postings', $count);
                    $pagination->set_attribute('page', $page);
                    $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_module=%s';
                    $pagination->set_attribute('pagelink', $page_link);
                    echo $pagination->render('shared/pagechooser');
                    ?>
                </td>
            </tr>
        </tfoot>
    <? endif; ?>
</table>
