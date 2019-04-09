<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>

<form action="<?= $controller->link_for('/assign_role_institutes/' . $role->getRoleid() . '/' . $user->id) ?>" method="post" class="default" data-dialog="size=auto;reload-on-close">
    <fieldset>
        <legend>
            <?= _('Einrichtungszuordnung anpassen') ?>
        </legend>

        <label>
            <?= sprintf(_("Einrichtungszuordnung für %s in der Rolle %s"), htmlReady($user->getFullname()), htmlready($role->getRoleName()))?>
            <div class="hgroup">
                <?= $qsearch->render() ?>

            </div>
        </label>

        <h4><?= _('Vorhandene Zuordnungen') ?></h4>
        <ul>
        <? foreach ($institutes as $institute): ?>
            <li>
                  <?= htmlReady($institute->name) ?>
                  <a href="<?= $controller->link_for("/assign_role_institutes/{$role->getRoleid()}/{$user->id}", ['remove_institute' => $institute->id]) ?>" data-dialog="size=auto;reload-on-close">
                      <?= Icon::create('trash') ?>
                  </a>
            </li>
        <? endforeach ?>
        </ul>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::create(_('Einrichtung hinzufügen'), "add_institute", ["rel" => "lightbox"]) ?>
        <?= Studip\LinkButton::createCancel(_('Schließen'), $controller->url_for('/assign_role/' . $user->id), [
            'data-dialog-button' => '',
            'data-dialog' => 'size=auto;reload-on-close'
        ]) ?>
    </footer>
</form>
