<? use Studip\Button, Studip\LinkButton; ?>
<form action="<?=$controller->url_for('admin/webservice_access/test')?>" method="post" class="default">
    <?=CSRFProtection::tokenTag()?>
    <fieldset>
        <legend>
            <?=_("Testen der Zugriffsregeln")?>
        </legend>

        <label>
            <?= _('API KEY') ?>
            <input type="text" name="test_api_key" size="50" required value="<?=htmlReady(Request::get("test_api_key"))?>">
        </label>

        <label>
            <?= _('Methode') ?></td>
            <input type="text" name="test_method" size="50" required value="<?=htmlReady(Request::get("test_method"))?>">
        </label>

        <label>
            <?= _('IP Adresse') ?></td>
            <input type="text" name="test_ip" size="50" required value="<?=htmlReady(Request::get("test_ip"))?>">
        </label>
    </fieldset>

    <footer>
        <?= Button::createAccept(_('Abschicken'), 'ok', ['title' => _('Test starten')])?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/webservice_access'), ['title' => _('Test abbrechen')])?>
    </footer>
</form>

<?
$sidebar = Sidebar::Get();
$sidebar->setImage('sidebar/admin-sidebar.png');
$sidebar->setTitle(_('Webservices'));

$actions = new ActionsWidget();
$actions->addLink(_('Liste der Zugriffsregeln'),$controller->url_for('admin/webservice_access'), Icon::create('add', 'clickable'));
$actions->addLink(_('Neue Zugriffsregel anlegen'),$controller->url_for('admin/webservice_access/new'), Icon::create('add', 'clickable'));

$sidebar->addWidget($actions);
