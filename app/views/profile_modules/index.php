<form action="<?= $controller->url_for('profilemodules/update', compact('username')) ?>" method="post" class="default plus">
    <?= CSRFProtection::tokenTag() ?>

<? foreach ($list as $category => $pluginlist): ?>
    <article class="studip">
        <header>
            <h1><?= htmlReady($category) ?></h1>
        </header>
    <? foreach ($pluginlist as $id => $item): ?>
        <section>
            <div class="plus_basic">

                <div class="element_header">
                    <input type="hidden" name="modules[<?= htmlReady($id) ?>]" value="0">
                    <input type="checkbox" value="1" class="studip-checkbox"
                           id="<?= md5($item['name']) ?>" name="modules[<?= htmlReady($id) ?>]"
                           onclick="jQuery(this).closest('form').submit()"
                           <? if ($item['activated']) echo 'checked'; ?>>

                    <label for="<?= md5($item['name']) ?>">
                        <strong><?= htmlReady($item['name']) ?></strong>
                    </label>
                </div>

                <div class="element_description">
                <? if ($item['icon']): ?>
                    <img class="plugin_icon text-bottom" alt="" src="<?= $item['icon'] ?>">
                <? endif ?>

                    <strong class="shortdesc">
                        <?= htmlReady($item['abstract']) ?>
                    </strong>
                </div>
            </div>

        <? if ($config['view'] === 'openall'): ?>
            <div class="plus_expert">
                <div class="screenshot_holder">
                <? if ($item['screenshots']): ?>
                    <a href="<?= htmlReady($item['screenshots'][0]['source']) ?>"
                       data-lightbox="<?= htmlReady($item['name']) ?>"
                       data-title="<?= htmlReady($item['screenshots'][0]['title']) ?>">
                        <img class="big_thumb" src="<?= htmlReady($item['screenshots'][0]['source']) ?>"
                             alt="<?= htmlReady($item['name']) ?>">
                    </a>

                    <? if (count($item['screenshots']) > 1): ?>
                        <div class="thumb_holder">
                        <? foreach (array_slice($item['screenshots'], 1) as $shot): ?>
                            <a href="<?= htmlReady($shot['source']) ?>"
                               data-lightbox="<?= htmlReady($item['name']) ?>"
                               data-title="<?= htmlReady($shot['title']) ?>">
                                <img class="small_thumb"
                                     src="<?= htmlReady($shot['source']) ?>"
                                     alt="<?= htmlReady($item['name']) ?>">
                            </a>

                        <? endforeach; ?>
                        </div>
                    <? endif; ?>
                <? endif ?>
                </div>

                <div class="descriptionbox">
                <? if ($item['keywords']): ?>
                    <ul class="keywords">
                    <? foreach ($item['keywords'] as $keyword): ?>
                        <li><?= htmlReady($keyword) ?></li>
                    <? endforeach; ?>
                    </ul>
                <? endif; ?>

                    <p class="longdesc">
                        <?= htmlReady($item['description']) ?: _('Keine Beschreibung vorhanden.') ?>
                    </p>

                <? if ($item['homepage']): ?>
                    <p>
                        <strong><?= _('Weitere Informationen:') ?></strong>
                        <a href="<?= htmlReady($item['homepage']) ?>">
                            <?= htmlReady($item['homepage']) ?>
                        </a>
                    </p>
                <? endif; ?>

                <? if ($item['helplink']): ?>
                    <a class="helplink" href="<?= htmlReady($item['helplink']) ?> ">
                        <?= _('...mehr') ?>
                    </a>
                <? endif; ?>
                </div>
            </div>
        <? endif; ?>
        </section>
    <? endforeach; ?>
    </article>
<? endforeach; ?>

    <footer class="hidden-js">
        <?= Studip\Button::create(_('An- / Ausschalten'), 'uebernehmen') ?>
    </footer>
</form>
