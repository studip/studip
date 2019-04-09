<? use Studip\Button, Studip\LinkButton; ?>
<div style="padding:2em; text-align:center; vertical-align: middle; background-color: #e7ebf1; margin-bottom: 20px;">
    <form action="<?= $controller->link_for('/index') ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <input type="text" style="width:50%;" name="sterm" value="<?= htmlReady($input_search) ?>">
        <input type="hidden" name="do_search" value="1">
        <?= Button::create('Suche') ?>
        <? if ($sterm) : ?>
            <?= LinkButton::create('Zurücksetzen', $controller->url_for('search/module/reset'), ['title' => _('Suche zurücksetzen')]); ?>
        <? endif; ?>
    </form>
</div>
<!-- Trefferset -->
<table class="default collapsable">
    <caption>
        <? if ($count) : ?>
        <?= sprintf(_('Gefundene Module für <i>%s</i>'), htmlReady($sterm)) ?>
        <span class="actions">
            <? printf(ngettext('%s Modul', '%s Module', $count), $count); ?>
        </span>
        <? endif; ?>
    </caption>
    <? if (count($module)) : ?>
    <colgroup>
        <col>
        <col>
        <col>
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2">
                <abbr title="<?= _('Moduldeskriptor') ?>">
                    <?= Icon::create('log', 'info') ?>
                </abbr>
            </th>
            <th><?= _('Modultitel') ?></th>
            <th><?= _('Gültigkeit') ?></th>
            <th><?= _('Einrichtung') ?></th>
        </tr>
    </thead>
<? foreach ($module as $modul) : ?>
    <?= $this->render_partial('search/module/_modul', ['modul' => $modul]); ?>
<? endforeach; ?>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align: right;">
            <? if ($count > MVVController::$items_per_page) : ?>
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
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
    <? endif; ?>
</table>