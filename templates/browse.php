<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<!-- SEARCHBOX -->
<form class="default" action="<?= URLHelper::getLink() ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <!-- form zur wahl der institute -->
    <fieldset>
        <legend><?= _('Personensuche') ?></legend>
        <!-- form zur freien Suche -->
        <label class="col-3">
            <?= _('Name:') ?>
            <?= QuickSearch::get('name', $search_object)
                ->setInputStyle('width: 400px')
                ->setAttributes(array('autofocus' => ''))
                ->defaultValue('', $name)
                ->fireJSFunctionOnSelect('STUDIP.Browse.selectUser')
                ->noSelectbox()
                ->render() ?>
        </label>

        <? if (count($institutes)): ?>
            <label class="col-3">
                <?= _('Einrichtung:') ?>

                <select name="inst_id" style="min-width: 400px;">
                    <option value="0">&nbsp;</option>
                    <? foreach ($institutes as $institute): ?>
                        <option value="<?= $institute['id'] ?>" <?= $institute['id'] == $inst_id ? 'selected="selected"' : '' ?>>
                            <?= htmlReady($institute['name']) ?>
                        </option>
                    <? endforeach;?>
                </select>
            </label>
        <? endif ?>

        <!-- form zur wahl der seminare -->
        <? if ($courses && count($courses)): ?>
            <label class="col-3">
                <?= _('Veranstaltungen:') ?>
                <select name="sem_id" style="min-width: 400px;">
                    <option value="0">&nbsp;</option>
                    <? foreach ($courses as $sem => $_courses): ?>
                        <optgroup label="<?= htmlReady($sem)?>">
                            <? foreach($_courses as $course) :?>
                                <option value="<?= $course['id'] ?>" <?= $course['id'] == $sem_id ? 'selected="selected"' : '' ?>>
                                    <?= htmlReady($course['name']) ?>
                                </option>
                            <? endforeach ?>
                        </optgroup>
                    <? endforeach ?>
                </select>
            </label>
        <? endif ?>
    </fieldset>

    <footer>
        <?= Button::create(_('Suchen'), 'send')?>
        <?= Button::create(_('Zurücksetzen'), 'reset')?>
    </footer>
</form>

<!-- RESULTS -->
<? if (isset($users)) :?>
    <table class="default nohover">
        <caption>
            <?= _('Ergebnisse') ?>
        </caption>
        <thead class="hidden-small-down">
            <tr>
                <th>
                    <a href="<?= URLHelper::getLink('', compact('name', 'sem_id', 'inst_id')) ?>">
                        <?= _('Name') ?>
                    </a>
                </th>
                <th>
                    <? if ($inst_id): ?>
                        <?= _('Funktion an der Einrichtung') ?>
                    <? elseif ($sem_id): ?>
                        <a href="<?= URLHelper::getLink('', compact('name', 'sem_id') + array('sortby' => 'status')) ?>">
                            <?= _('Status in der Veranstaltung') ?>
                        </a>
                    <? else: ?>
                        <a href="<?= URLHelper::getLink('', compact('name') + array('sortby' => 'perms')) ?>">
                            <?= _('globaler Status') ?>
                        </a>
                    <? endif; ?>
                </th>
                <th class="actions">
                    <?= _('Nachricht verschicken') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($users as $user): ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', array('username' => $user['username'])) ?>">
                            <?= Avatar::getAvatar($user['user_id'], $user['username'])->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($user['fullname']) ?>
                        </a>
                    </td>
                    <td>
                        <?= htmlReady($user['status']) ?>
                    </td>
                    <td class="actions">
                        <a href="<?= URLHelper::getLink('dispatch.php/messages/write', array('rec_uname' => $user['username'])) ?>" data-dialog>
                            <?= Icon::create('mail', 'clickable')->asImg(['class' => 'text-top', 'title' => _('Nachricht an Benutzer verschicken')]) ?>
                        </a>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
<? elseif ($name != ''): ?>
    <?= MessageBox::info(_('Es wurde niemand gefunden.')) ?>
<? elseif (isset($name)): ?>
    <?= MessageBox::error(_('Bitte einen Vor- oder Nachnamen eingeben.')) ?>
<? endif; ?>

<?
$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/person-sidebar.png');
if (get_config('SCORE_ENABLE')) {
    $widget = new NavigationWidget();
    $widget->addLink(_('Zur Stud.IP-Rangliste'), URLHelper::getLink('dispatch.php/score'));
    $sidebar->addWidget($widget);
}
