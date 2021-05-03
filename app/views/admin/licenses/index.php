<table class="default">
    <caption>
        <?= _("Lizenzen") ?>
    </caption>
    <thead>
        <tr>
            <th></th>
            <th><?= _("SPDX-Lizenzkürzel") ?></th>
            <th><?= _("Name") ?></th>
            <th class="actions"><?= _("Aktion") ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($licenses as $license) : ?>
        <tr>
            <td>
                <?= LicenseAvatar::getAvatar($license['identifier'])->getImageTag(Avatar::MEDIUM) ?>
            </td>
            <td><?= htmlReady($license['identifier']) ?></td>
            <td>
                <? if ($license['link']) : ?>
                <a href="<?= htmlReady($license['link']) ?>">
                <? endif ?>
                <?= htmlReady($license['name']) ?>
                <? if ($license['link']) : ?>
                </a>
                <? endif ?>
            </td>
            <td class="actions">
                <a href="<?= $controller->link_for("admin/licenses/edit", ['identifier' => $license['identifier']]) ?>" data-dialog>
                    <?= Icon::create("edit", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                </a>
                <form action="<?= $controller->link_for("admin/licenses/delete", ['identifier' => $license->getId()]) ?>"
                      method="post"
                      data-confirm="<?= _("Wirklich löschen?") ?>"
                      class="inline">
                    <?= Icon::create("trash", "clickable")->asInput(20) ?>
                </form>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>

<?

$actions = new ActionsWidget();
$actions->addLink(
    _("Lizenz erzeugen"),
    $controller->url_for("admin/licenses/edit"),
    Icon::create("add", "clickable"),
    ['data-dialog' => 1]
);
Sidebar::Get()->addWidget($actions);
