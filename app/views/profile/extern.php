<div class="responsive-visible">
    <img src="<?= htmlReady($user['avatar_url'] ?: Avatar::getNobody()->getURL(Avatar::NORMAL)) ?>">
</div>

<? if (Config::get()->OERCAMPUS_ENABLED && $user['data']['description']) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Beschreibung für den OER Campus') ?></h1>
        </header>
        <section>
            <?= nl2br(htmlReady($user['data']['description'])) ?>
        </section>
    </article>
<? endif ?>
<?

