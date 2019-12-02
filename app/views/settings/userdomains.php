<? use Studip\Button; ?>

<form action="<?= $controller->url_for('settings/userdomains/store') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <table class="default" id="assigned_userdomains">
        <caption><?= _('Ich bin folgenden Nutzerdomänen zugeordnet:') ?></caption>
        <colgroup>
            <col>
            <col width="100px">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Nutzerdomäne') ?></th>
                <th>
                <? if ($allow_change): ?>
                    <?= _('austragen') ?>
                <? endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <? if (count($user_domains) === 0): ?>
            <tr>
                <td colspan="2" style="text-align: center">
                    <?= _('Sie sind noch keiner Nutzerdomäne zugeordnet.') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($user_domains as $domain): ?>
            <tr>
                <td><?= htmlReady($domain->name) ?></td>
                <td style="text-align:center">
                <? if ($allow_change): ?>
                    <input type="checkbox" name="userdomain_delete[]" value="<?= $domain->id ?>">
                <? else: ?>
                    <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    <? if (count($user_domains) > 0 && $allow_change): ?>
        <tfoot>
            <tr>
                <td colspan="2">
                    <footer>
                        <?= Button::create(_('Übernehmen'), 'store_in', ['title' => _('Änderungen übernehmen')]) ?>
                    </footer>
                </td>
            </tr>
        </tfoot>
    <? endif ?>
    </table>
</form>

<? if ($allow_change): ?>
<form action="<?= $controller->url_for('settings/userdomains/store') ?>" method="post" class="default">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Nutzerdomäne hinzufügen') ?></legend>
</form>

        <a name="userdomains"></a>

        <label>
            <?= _('Wählen Sie eine Nutzerdomäne aus der folgenden Liste aus:') ?>

            <? if (!empty($domains)) : ?>
                <select name="new_userdomain" id="new_userdomain">
                    <option selected value="none"><?= _('-- Bitte Nutzerdomäne auswählen --') ?></option>
                    <? foreach ($domains as $domain) : ?>
                        <option value="<?= $domain->getID() ?>"><?= htmlReady(my_substr($domain->getName(), 0, 50)) ?></option>
                    <? endforeach ?>
                </select>
            <? endif ?>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
<form action="<?= $controller->url_for('settings/userdomains/store') ?>" method="post" class="default">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Nutzerdomäne hinzufügen') ?></legend>

        <a name="userdomains"></a>

        <label>
            <?= _('Wählen Sie eine Nutzerdomäne aus der folgenden Liste aus:') ?>

        <? if (!empty($domains)) : ?>
            <select name="new_userdomain" id="new_userdomain">
                <option selected value="none"><?= _('-- Bitte Nutzerdomäne auswählen --') ?></option>
                <? foreach ($domains as $domain) : ?>
                    <option value="<?= htmlReady($domain->id) ?>">
                        <?= htmlReady(my_substr($domain->name, 0, 50)) ?>
                    </option>
                <? endforeach ?>
            </select>
        <? endif ?>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
    </footer>
</form>
<? else: ?>
    <?= _('Die Informationen zu Ihren Nutzerdomänen werden vom System verwaltet und können daher von Ihnen nicht geändert werden.') ?>
<? endif; ?>
