<?= $controller->jsUrl() ?>
<table class="default collapsable">
    <colgroup>
        <col style="width: 10%;">
        <col>
        <col span="2" style="width: 5%;">
        <col span="2" style="width: 150px;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('module/module/', _('Modulcode'), 'code') ?>
            <?= $controller->renderSortLink('module/module/', _('Modul'), 'bezeichnung') ?>
            <?= $controller->renderSortLink('module/module/', _('Fassung'), 'fassung_nr') ?>
            <?= $controller->renderSortLink('module/module/', _('Modulteile'), 'count_modulteile') ?>
            <th style="text-align: right;">
                <?= _('Ausgabesprachen') ?>
            </th>
            <th></th>
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
