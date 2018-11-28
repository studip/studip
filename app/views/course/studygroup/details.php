<article class="studip">
    <header>
        <h1><?= _('Grunddaten') ?></h1>
    </header>
    <section>
        <dl style="margin: 0">
        <? if ($studygroup->description): ?>
            <dt><?= _('Beschreibung') ?></dt>
            <dd><?= formatLinks($studygroup->description) ?></dd>
        <? endif; ?>
            <dt><?= _('Moderiert von') ?></dt>
            <dd>
                <ul class="list-csv">
                <? foreach ($studygroup->getMembers(['dozent', 'tutor']) as $mod) : ?>
                    <li>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $mod['username']]) ?>">
                            <?= htmlready($mod['fullname']) ?>
                        </a>
                    </li>
                <? endforeach ?>
                </ul>
            </dd>
        </dl>
    </section>
</article>

<div class="hidden-medium-up">
<? foreach ($sidebarActions as $action) : ?>
    <?= Studip\LinkButton::create($action->label, $action->url) ?>
<? endforeach ?>
</div>
