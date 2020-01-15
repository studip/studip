<article class="resource-object">
    <header class="resource-header">
        <h2><?= htmlReady(
            ($building->number
                ? $building->number->state
                : '' ) .
            $building->name) ?></h2>
    </header>
    <section class="resource-details">
        <img class="resource-picture" src="<?= $building->getImageUrl() ?>">

        <p><?= htmlReady($building->description) ?></p>
        <dl>
            <dt><?= _('Adresse') ?></dt>
            <dd><?= htmlReady($building->address->state) ?>
                <a href="<?= ResourceManager::getMapUrlForResourcePosition(
                                $building->geo_coordinates
                            ) ?>">
                    <?= Icon::create('place')->asImg(16) ?>
                </a>
            </dd>
            <dt><?= _('Verantwortliche Person') ?></dt>
            <dd>
                <?= $building->facility_manager
                        ? htmlReady($building->facility_manager->getFullName())
                        :       '&#x1f4a9; ' .  _('unbekannt') ?>
            </dd>
        </dl>
        <? if ($building->disability_friendly): ?>
        <strong>&#x2713; <?= _('Das Gebäude ist behindertengerecht ausgestattet.') ?></strong>
        <? endif ?>
    </section>
</article>
