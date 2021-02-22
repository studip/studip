<? if ($building): ?>
    <nav class="resource-hierarchy">
        <? if ($building->location): ?>
            <a href="<?= $building->location->getActionLink('show') ?>" <?= (Request::isDialog()) ? 'data-dialog' : ''; ?>>
                <?= htmlReady($building->location->name) ?>
            </a>
            &gt;
        <? endif ?>
        <?= htmlReady($building->name) ?>
    </nav>
    <? if (!empty($building_details)) : ?>
        <dl>
            <? foreach ($building_details as $title => $building_detail) : ?>
                <dt>
                    <?= $title ?>
                </dt>
                <dd>
                    <?= htmlReady($building_detail) ?>
                </dd>
            <? endforeach ?>
        </dl>
    <? endif ?>
    <? if ($building->description): ?>
        <section class="contentbox">
            <header>
                <h1><?= _('Beschreibung und Hinweise') ?></h1>
            </header>
            <section>
                <p class="resource-description-text"><?= htmlReady($building->description) ?></p>
            </section>
        </section>
    <? endif ?>

    <div data-dialog-button>
        <? if (Request::isDialog()) : ?>
            <? if ($geo_coordinates_object instanceof ResourceProperty): ?>
                <?= \Studip\LinkButton::create(
                    _('Zum Lageplan'),
                    ResourceManager::getMapUrlForResourcePosition($geo_coordinates_object),
                    ['target' => '_blank']
                ) ?>
            <? endif ?>
            <?= \Studip\LinkButton::createEdit(
                _('Bearbeiten'),
                $building->getActionURL('edit'),
                [
                    'data-dialog' => 'size=auto'
                ]
            ) ?>
        <? endif ?>
    </div>
    <?
    $property_groups = $building->getGroupedProperties(
        ['geo_coordinates', 'number', 'address']
    );
    ?>
    <? if (count($property_groups)): ?>
        <?= $this->render_partial(
            'resources/resource/_standard_properties_display_part.php',
            [
                'property_groups' => $property_groups
            ]
        ) ?>
    <? endif ?>

<? endif ?>

<? $resource_folder = $building->getFolder(); ?>
<? if ($resource_folder && $resource_folder->getFiles()): ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Dateien') ?></h1>
        </header>
        <table class="default sortable-table" data-sortlist="[[2, 0]]">
            <?= $this->render_partial('files/_files_thead') ?>
            <? foreach ($resource_folder->getFiles() as $file): ?>
                <? if ($file->isVisible($GLOBALS['user']->id)) : ?>
                    <?= $this->render_partial(
                        'files/_fileref_tr',
                        [
                            'file' => $file,
                            'current_folder' => $resource_folder,
                            'last_visitdate' => time()
                        ]
                    ) ?>
                <? endif ?>
            <? endforeach ?>
        </table>
    </section>
<? endif ?>

<? if ($building->children): ?>
    <section class="contentbox">
        <header>
            <h1><?= _('Räume') ?></h1>
        </header>
        <section>
            <ul class="list-unstyled">
                <? foreach ($building->findChildrenByClassName('Room') as $child): ?>
                    <li>
                        <a href="<?= $controller->link_for('resources/room/index/' . $child->id); ?>"
                            <?= (Request::isDialog()) ? 'data-dialog' : ''; ?>>
                            <?= $child->getIcon('clickable')->asImg(
                                ['class' => 'text-bottom']
                            ) ?>
                            <?= htmlReady($child->name); ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>
        </section>
    </section>
<? endif ?>