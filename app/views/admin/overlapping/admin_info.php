<section class="contentbox">
    <header>
        <h1>
            <?= _('Zuständige Administratoren') ?>
        </h1>
    </header>
    <section>
        <dl>
            <dt>
                <?= _('Heimateinrichtung') ?>
            </dt>
            <dd>
                <?= htmlReady($course->home_institut->getFullname()) ?>
            </dd>
            <dt>
                <?= _('Zuständige Administratoren') ?>
            </dt>
            <? foreach ($admins as $admin) : ?>
            <dd>
                <a href="<?= $controller->url_for('profile', ['username' => $admin->username]) ?>">
                    <?= htmlReady($admin->user->getFullname()) ?>
                </a>
            </dd>
            <? endforeach; ?>
        </dl>
    </section>
</section>
