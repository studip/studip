<div id="ilias_module_details_window">
<aside id="ilias_module_aside">
    <div class="ilias-module-icon">
        <?= $module->getIcon()?>
    </div>

    <table class="default nohover">
        <tbody>
            <tr>
                <td><?= _('Erstellt') ?></td>
                <td>
                    <?= htmlReady($module->getMakeDate(), true, true)?>
                </td>
            </tr>
            <tr>
                <td><?= _('Geändert') ?></td>
                <td>
                    <?= htmlReady($module->getChangeDate(), true, true)?>
                </td>
            </tr>
            <tr>
                <td><?= _('Besitzer/-in') ?></td>
                <td>
                <? if ($module->getAuthorStudip()): ?>
                    <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $module->getAuthorStudip()->username]) ?>">
                        <?= htmlReady($module->getAuthorStudip()->getFullName()) ?>
                    </a>
                <? else: ?>
                    <?= _('ILIAS-Nutzer/-in') ?>:
                    <?= htmlReady($ilias->getUserFullname($module->getAuthorIlias())); ?>
                <? endif ?>
                </td>
            </tr>

        </tbody>
    </table>
</aside>

    <div id="ilias_module_preview">
        <h2><?= htmlReady($module->getTitle()) ?></h2>
        <h3><?= _('Typ') ?></h3>
        <article>
            <?= htmlReady($module->getModuleTypeName()) ?>
        </article>

        <h3><?= _('Beschreibung') ?></h3>
        <article>
            <?= htmlReady($module->getDescription()?: _('Keine Beschreibung vorhanden.'), true, true) ?>
        </article>

        <? if (!$module->isConnected()) : ?>
        <h3><?=_('Pfad')?></h3>
        <article>
            <?= htmlReady($ilias->getPath($module->getId())); ?>
        </article>
        <? endif ?>

        <? if ((($module->getModuleType() == 'htlm') || ($module->getModuleType() == 'sahs') || ($module->getModuleType() == 'lm')) && ($ilias->getStructure($module->getId()))) : ?>
        <h3><?=_('Struktur')?></h3>
        <article>
            <? foreach ($ilias->getStructure($module->getId()) as $chapter) : ?>
            <div><?=htmlReady($chapter)?></div>
            <? endforeach ?>
        </article>
        <? endif ?>
    </div>
</div>