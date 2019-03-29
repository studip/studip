<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form action="<?= $controller->link_for('admin/plugin/search') ?>" method="post" class="default">
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


<? if (!$search_results): ?>
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
            <tr style="vertical-align: top">
                <td class="plugin_image">
                <? if ($plugin['image']): ?>
                    <a href="<?= htmlReady($plugin['image']) ?>"
                       data-lightbox="<?= htmlReady($plugin['displayname']) ?>"
                       data-title="<?= htmlReady($name) ?>">
                        <img src="<?= htmlReady($plugin['image']) ?>" class="plugin_preview">
                    </a>
                <? endif ?>
                </td>
                <td>
                    <a href="<?= htmlReady($plugin['marketplace_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= _('Zum Marktplatz') ?>">
                        <strong><?= htmlReady($name) ?></strong>
                    </a>
                <? if (mb_strlen($plugin['description']) > 500) : ?>
                    <span class="plugin_description short">
                        <div>
                            <p>
                                <?= htmlReady($plugin['description'], true, true) ?>
                            </p>
                        <? if ($plugin['plugin_url'] && $plugin['plugin_url'] !== $plugin['marketplace_url']): ?>
                            <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank" rel="noopener noreferrer" class="link-extern">
                                <?= _('Plugin-Homepage') ?>
                            </a>
                        <? endif ?>

                            <p class="read_more"></p>
                        </div>

                        <a href="" class="read_more_link" onClick="jQuery(this).parent().toggleClass('short');return false;">
                            <span><?= _('Weiterlesen') ?></span>
                        </a>
                    </span>
                <? else: ?>
                    <p>
                        <?= htmlReady($plugin['description'], true, true) ?>
                    </p>
                    <? if ($plugin['plugin_url'] && $plugin['plugin_url'] !== $plugin['marketplace_url']): ?>
                        <a href="<?= htmlReady($plugin['plugin_url']) ?>" target="_blank" rel="noopener noreferrer" class="link-extern">
                            <?= _('Plugin-Homepage') ?>
                        </a>
                    <? endif ?>
                <? endif ?>
                </td>
                <td>
                    <?= htmlReady($plugin['version']) ?>
                </td>
                <td class="plugin_score" <? if ($plugin['score']) printf('title="%s"', round($plugin['score'] / 2, 1)) ?>>
                <? for ($i = 0; $i < $plugin['score'] / 2; ++$i): ?>
                    <?= Icon::create('star', Icon::ROLE_INACTIVE) ?>
                <? endfor ?>
                </td>
                <td class="plugin_install actions">
                    <form action="<?= $controller->link_for('admin/plugin/install') ?>" method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <input type="hidden" name="plugin_url" value="<?= htmlReady($plugin['url']) ?>">
                        <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
                        <?= Icon::create('install')->asInput([
                            'title' => _('Plugin installieren'),
                            'class' => 'middle',
                            'name'  => 'install',
                        ]) ?>
                    </form>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
