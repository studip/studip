<? if (count($institutes) === 0): ?>
    <?= MessageBox::info(_('Sie sind keinem Institut / keiner Einrichtung zugeordnet!')); ?>
<? else: ?>

    <? $inst_count = 0; ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Zuordnungen zu Einrichtungen') ?></h1>
        </header>
        <? foreach ($institutes as $inst_id => $institute): ?>
            <article class="<?= ContentBoxHelper::classes($inst_id) ?><? if (Request::get('type') == 'institute' && Request::get('open') == $role_id) : ?>open<? endif ?>">
                <header>
                    <h1>
                        <a href="<?= ContentBoxHelper::href($inst_id) ?>">
                            <?= htmlReady($institute['name']) ?>
                        </a>
                    </h1>
                    <nav>
                        <? if (!$locked && $inst_count > 0) : ?>
                            <a href="<?= $controller->url_for('settings/statusgruppen/move', $inst_id, 'up') ?>">
                                <?= Icon::create('arr_2up', 'sort')->asImg() ?>
                            </a>
                        <? elseif (!$locked && count($institutes) > 1): ?>
                            <?= Icon::create('arr_2up', 'inactive')->asImg() ?>
                        <? endif; ?>

                        <? if (!$locked && $inst_count + 1 < count($institutes)): ?>
                            <a href="<?= $controller->url_for('settings/statusgruppen/move', $inst_id, 'down') ?>">
                                <?= Icon::create('arr_2down', 'sort')->asImg() ?>
                            </a>
                        <? elseif (!$locked && count($institutes) > 1): ?>
                            <?= Icon::create('arr_2down', 'inactive')->asImg() ?>
                        <? endif; ?>

                        <? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_id)) : ?>
                            <a href="<?= URLHelper::getURL('dispatch.php/institute/members', ['cid' => $inst_id, 'admin_view' => 1]) ?>">
                                <?= Icon::create('link-intern', 'clickable', ['title' => _('Zur Einrichtung')])->asImg() ?>
                            </a>
                        <? else: ?>
                            <a href="<?= URLHelper::getURL('dispatch.php/institute/overview', ['auswahl' => $inst_id]) ?>">
                                <?= Icon::create('link-intern', 'clickable', ['title' => _('Zur Einrichtung')])->asImg() ?>
                            </a>
                        <? endif; ?>
                    </nav>
                </header>
                <section>
                    <article>
                        <?= $this->render_partial('settings/statusgruppen/modify_institute', [
                            'followers' => count($institute['flattened']) > 0,
                            'inst_id'   => $inst_id,
                            'institute' => $institute,
                        ]) ?>
                    </article>
                </section>
            </article>

            <?
                $inst_count += 1;
                $role_count = 1;
                $max_roles  = count($institute['flattened']);
            ?>
            <? foreach ($institute['flattened'] as $role_id => $role): ?>
                <article class="indented <?= ContentBoxHelper::classes($role_id) ?> <? if (Request::get('type') === 'role' && Request::get('open') == $role_id) : ?>open<? endif ?>">
                    <header>
                        <h1>
                            <? if (count($institute['datafields'][$role_id]) > 0): ?>
                                <a href="<?= ContentBoxHelper::href($role_id) ?>"
                                   name="<?= $role_id ?>"
                                   class="link <?= $open === $role_id ? 'open' : 'closed' ?>">
                                    <?= htmlReady($role['name_long']) ?>
                                </a>
                            <? else: ?>
                                <a class="link" href="<?= ContentBoxHelper::href($role_id) ?>">
                                    <?= htmlReady($role['name_long']) ?>
                                </a>
                            <? endif; ?>
                        </h1>
                        <nav>
                            <? if ($GLOBALS['perm']->have_studip_perm('admin', $inst_id) && !$locked) : ?>
                                <a href="<?= $controller->url_for('settings/statusgruppen/verify/delete/' . $role_id) ?>#<?= $role_id ?>">
                                    <?= Icon::create('trash', 'clickable', ['title' => _('Löschen')])->asImg() ?>
                                </a>

                                <a href="<?= URLHelper::getURL('dispatch.php/admin/statusgroups', ['cid' => $inst_id]) ?>#group-<?= $role_id ?>">
                                    <?= Icon::create('link-intern', 'clickable', ['title' => _('Zur Funktion')])->asImg() ?>
                                </a>
                            <? endif; ?>
                        </nav>
                    </header>
                    <section>
                        <?= $this->render_partial('settings/statusgruppen/modify', [
                            'followers'  => $role_count < $max_roles,
                            'inst_id'    => $inst_id,
                            'role_id'    => $role_id,
                            'datafields' => $institute['datafields'][$role_id],
                            'role'       => $role['role'],
                        ]) ?>
                    </section>
                </article>

                <? $role_count += 1; ?>
            <? endforeach; ?>
        <? endforeach; ?>
    </section>

    <? if ($GLOBALS['perm']->have_perm('admin') && !$locked): ?>
        <?= $this->render_partial('settings/statusgruppen/assign', compact('subview_id', 'admin_insts', 'sub_admin_insts')) ?>
    <? endif; ?>

<? endif; ?>
