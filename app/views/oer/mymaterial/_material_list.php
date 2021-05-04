<table class="default oer_mymaterial">
    <thead>
    <tr>
        <th style="width:20px"></th>
        <th><?= _('Material') ?></th>
        <th><?= _('Bewertung') ?></th>
        <th><?= _('Downloads') ?></th>
        <th class="actions"><?= _('Aktion') ?></th>
    </tr>
    </thead>
    <tbody>
    <? $starwidth = '20px' ?>
    <? foreach ($materialien as $material) : ?>
        <tr>
            <td>
                <? if ($material->draft) : ?>
                    <?= Icon::create('lock-locked', Icon::ROLE_INFO)->asImg(20, ['class' => 'text-bottom']) ?>
                <? endif ?>
            </td>
            <td>
                <a href="<?= $controller->link_for('oer/market/details/' . $material->id) ?>">
                    <?= htmlReady($material['name']) ?>
                </a>
            </td>
            <td>
                <? if ($material->rating === null) : ?>
                    <?= Icon::create('star', Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <?= Icon::create('star', Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <?= Icon::create('star', Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <?= Icon::create('star', Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <?= Icon::create('star', Icon::ROLE_INFO)->asImg($starwidth) ?>
                <? else : ?>
                    <? $material->rating = round($material->rating, 1) / 2 ?>
                    <? $v = $material->rating >= 0.75 ? '' : ($material->rating >= 0.25 ? '-halffull' : '-empty') ?>
                    <?= Icon::create("star{$v}", Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <? $v = $material->rating >= 1.75 ? '' : ($material->rating >= 1.25 ? '-halffull' : '-empty') ?>
                    <?= Icon::create("star{$v}", Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <? $v = $material->rating >= 2.75 ? '' : ($material->rating >= 2.25 ? '-halffull' : '-empty') ?>
                    <?= Icon::create("star{$v}", Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <? $v = $material->rating >= 3.75 ? '' : ($material->rating >= 3.25 ? '-halffull' : '-empty') ?>
                    <?= Icon::create("star{$v}", Icon::ROLE_INFO)->asImg($starwidth) ?>
                    <? $v = $material->rating >= 4.75 ? '' : ($material->rating >= 4.25 ? '-halffull' : '-empty') ?>
                    <?= Icon::create("star{$v}", Icon::ROLE_INFO)->asImg($starwidth) ?>
                <? endif ?>
            </td>
            <td>
                <a href="<?= $controller->statistics($material) ?>" data-dialog="size=auto">
                    <?= OERDownloadcounter::countBySQL('material_id = ?', [$material->id]) ?>
                </a>
            </td>
            <td class="actions">
                <? if ($material->isMine()) : ?>
                    <a href="<?= $controller->edit($material) ?>" data-dialog
                       title="<?= _('Lernmaterial bearbeiten') ?>">
                        <?= Icon::create('edit', Icon::ROLE_CLICKABLE)->asImg(20) ?>
                    </a>
                    <form action="<?= $controller->delete($material) ?>"
                          class="inlineform"
                          method="post"
                          data-confirm="<?= _('Dieses Material wirklich löschen?') ?>">
                        <?= Icon::create("trash", Icon::ROLE_CLICKABLE)->asInput(20) ?>
                    </form>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
