<? if (isset($flash['delete'])): ?>
    <?= QuestionBox::create(
        _('Wollen Sie das Banner wirklich löschen?'),
        $controller->deleteURL($flash['delete']['banner_id'], ['delete' => 1]),
        $controller->deleteURL($flash['delete']['banner_id'], ['back' => 1])
    ) ?>
<? endif; ?>

<table class="default">
    <thead>
        <tr>
            <th><?= _('Banner') ?></th>
            <th><?= _('Beschreibung') ?></th>
            <th><?= _('Typ') ?></th>
            <th><?= _('Ziel') ?></th>
            <th><?= _('Zeitraum') ?></th>
            <th><?= _('Klicks') ?></th>
            <th><?= _('Views') ?></th>
            <th><?= _('Prio') ?></th>
            <th><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($banners as $banner): ?>
        <tr id="banner-<?= htmlReady($banner->id) ?>">
            <td style="text-align: center;">
                <?= $banner->toImg(['style' => 'max-width: 80px']) ?>
            </td>
            <td><?= htmlReady($banner->description) ?></td>
            <td><?= htmlReady($banner->target_type) ?></td>
            <td>
            <? if ($banner['target_type'] === 'seminar'): ?>
                <?= mila(reset(get_object_name($banner->target, 'sem')), 30) ?>
            <? elseif ($banner['target_type'] === 'inst') :?>
                <?= mila(reset(get_object_name($banner->target, 'inst')), 30) ?>
            <? else: ?>
                <?= htmlReady($banner->target) ?>
            <? endif; ?>
            </td>
            <td style="text-align: center;">
                <?= $banner->startdate ? strftime('%x', $banner->startdate) : _('sofort') ?><br>
                <?= _('bis') ?><br>
                <?= $banner->enddate ? strftime('%x', $banner->enddate) : _('unbegrenzt') ?>
            </td>
            <td align="center">
                <?= number_format($banner->clicks, 0, ',', '.') ?>
            </td>
            <td align="center">
                <?= number_format($banner->views, 0, ',', '.') ?>
            </td>
            <td><?= $banner->priority ?> (<?= $banner->getViewProbability() ?>)</td>
            <td class="actions">
                <a class="load-in-new-row" href="<?= $controller->info($banner, ['path' => $banner->banner_path]) ?>">
                    <?= Icon::create('info')->asImg(['title' => _('Eigenschaften')]) ?>
                </a>
                <a href="<?= $controller->edit($banner, ['path' => $banner->banner_path]) ?>" data-dialog="size=auto">
                    <?= Icon::create('edit')->asImg(['title' => _('Banner bearbeiten')]) ?>
                </a>
                <a href="<?= $controller->reset($banner) ?>">
                    <?= Icon::create('refresh')->asImg(['title' => _('Klicks/Views zurücksetzen')]) ?>
                </a>
                <a href="<?= $controller->delete($banner) ?>">
                    <?= Icon::create('trash')->asImg(['title' => _('Banner löschen')]) ?>
                </a>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>
