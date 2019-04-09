<?
# Lifter010: TODO
?>
<? if ($content_for_layout != ''): ?>
    <? if (!isset($admin_title)) $admin_title = _('Administration') ?>

    <table class="index_box">
        <tr>
            <td class="table_header_bold" style="font-weight: bold;">
                <? if (isset($icon_url)): ?>
                    <?= Assets::img($icon_url, ['class' => 'middle']) ?>
                <? endif ?>
                <?= htmlReady($title) ?>
            </td>

            <td class="table_header_bold" style="text-align: right;">
                <? if (isset($admin_url)): ?>
                    <a href="<?= URLHelper::getLink($admin_url) ?>" title="<?= htmlReady($admin_title) ?>">
                        <?= Icon::create('admin', 'info_alt')->asImg(16, ["alt" => htmlReady($admin_title)]) ?>
                    </a>
                <? endif ?>
            </td>
        </tr>

        <tr>
            <td class="index_box_cell" colspan="2">
                <?= $content_for_layout ?>
            </td>
        </tr>
    </table>
<? endif ?>
