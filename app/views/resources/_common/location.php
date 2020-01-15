<article class="resource-object">
    <header class="resource-header">
        <h2><?= htmlReady($location->name) ?></h2>
    </header>
    <section class="resource-details">
        <img class="resource-picture" src="<?= $building->getImageUrl() ?>">
        <p><?= htmlReady($location->description) ?></p>
        <dl>
            <dt><?= _('Adresse') ?></dt>
            <dd><?= htmlReady($location->getAddress()) ?>
                <a href="<?= ResourceManager::getMapUrlForResourcePosition(
                                $location->geo_coordinates
                            ) ?>">
                    <?= Icon::create('place')->asImg(16) ?>
                </a>
            </dd>
        </dl>
    </section>
</article>
