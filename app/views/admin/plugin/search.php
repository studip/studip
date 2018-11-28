<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->url_for('admin/plugin/search') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Plugins suchen') ?>
        </legend>

        <label>
            <?= _('Pluginname') ?>
            <input name="search" type="text" size="20" value="<?= htmlReady($search) ?>">
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Suchen'), 'suchen', ['title' => _('Suche starten')])?>
        <?= LinkButton::create(_('Zurücksetzen'), $controller->url_for('admin/plugin/search'), array('title' => _('Suche zurücksetzen')))?>
    </footer>
</form>


<? if (empty($search_results)): ?>
    <?= MessageBox::info(_('Es wurden keine Plugins gefunden.')) ?>
<? else: ?>
    <br>
    <table class="default nohover">
        <caption>
        <? if ($search === null): ?>
            <?= _('Empfohlene Plugins') ?>
        <? else: ?>
            <?= _('Suchergebnisse') ?>
        <? endif ?>
        </caption>
        <thead>
            <tr>
                <th class="plugin_image"><?= _('Bild')?></th>
                <th><?= _('Name und Beschreibung')?></th>
                <th><?= _('Version') ?></th>
                <th><?= _('Bewertung') ?></th>
                <th class="plugin_install actions"><?= _('Installieren') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($search_results as $name => $plugin): ?>
            <tr>
                <td class="plugin_image">
                    <? if ($plugin['image']): ?>
                        <? if ($plugin['plugin_url']): ?>
                            <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?= htmlReady($plugin['image']) ?>" class="plugin_preview">
                            </a>
                        <? else: ?>
                            <img src="<?= htmlReady($plugin['image']) ?>" class="plugin_preview">
                        <? endif ?>
                    <? endif ?>
                </td>
                <td>
                <? if ($plugin['plugin_url']): ?>
                    <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank" rel="noopener noreferrer">
                        <strong><?= htmlReady($name) ?></strong>
                    </a>
                <? else: ?>
                    <strong><?= htmlReady($name) ?></strong>
                <? endif ?>
                <? if (mb_strlen($plugin['description']) > 500) : ?>
                <span class="plugin_description short">
                    <div>
                        <?= nl2br(htmlReady($plugin['description'])) ?>

                        <p class="read_more"></p>
                    </div>

                    <a href="" class="read_more_link" onClick="jQuery(this).parent().toggleClass('short');return false;">
                        <span><?= _('Weiterlesen') ?></span>
                    </a>
                </span>
                <? else: ?>
                    <p>
                        <?= nl2br(htmlReady($plugin['description'])) ?>
                    </p>
                <? endif ?>
                </td>
                <td>
                    <?= htmlReady($plugin['version']) ?>
                </td>
                <td class="plugin_score">
                <? for ($i = 0; $i < $plugin['score']; ++$i): ?>
                    <?= Icon::create('star', 'inactive')->asImg() ?>
                <? endfor ?>
                </td>
                <td class="plugin_install actions">
                    <form action="<?= $controller->url_for('admin/plugin/install') ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <input type="hidden" name="plugin_url" value="<?= htmlReady($plugin['url']) ?>">
                        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
                        <?= Icon::create('install', 'clickable', ['title' => _('Plugin installieren')])->asInput(["type" => "image", "class" => "middle", "name" => "install"]) ?>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
<? if ($unknown_plugins) : ?>
    <table class="default">
        <caption>
            <?= _('Im Pluginverzeichnis vorhandene Plugins registrieren') ?>
        </caption>
        <thead>
            <tr>
                <th><?= _('Name')?></th>
                <th><?= _('Pluginklasse')?></th>
                <th><?= _('Version') ?></th>
                <th><?= _('Ursprung') ?></th>
                <th class="plugin_install"><?= _('Registrieren') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($unknown_plugins as $n => $plugin): ?>
            <tr>
                <td><?= htmlReady($plugin['pluginname']) ?></td>
                <td><?= htmlReady($plugin['pluginclassname']) ?></td>
                <td><?= htmlReady($plugin['version']) ?></td>
                <td><?= htmlReady($plugin['origin']) ?></td>
                <td class="plugin_install">
                    <form action="<?= $controller->url_for('admin/plugin/register/' . $n) ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <?= Icon::create('install', 'clickable', ['title' => _('Plugin registrieren')])->asInput([
                            'type'  => 'image',
                            'class' => 'middle',
                            'name'  => 'install',
                        ]) ?>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif; ?>

<? if (Config::get()->PLUGINS_UPLOAD_ENABLE): ?>
    <form action="<?= $controller->url_for('admin/plugin/install') ?>" enctype="multipart/form-data" method="post" class="default">
        <?= CSRFProtection::tokenTag() ?>

        <fieldset>
            <legend>
                <?= _('Plugin als ZIP-Datei hochladen') ?>
            </legend>

            <label class="file-upload">
                <?= _('Plugin-Datei auswählen') ?>
                <input name="upload_file" type="file" size="40">
                <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
            </label>
        </fieldset>

        <footer>
            <?= Button::create(_('Hinzufügen'), 'hinzufuegen', ['title' => _('Neues Plugin installieren')])?>
        </footer>
    </form>
<? endif ?>
