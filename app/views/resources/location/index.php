<? if ($location): ?>
    <? if ((string)$location->description !== '') : ?>
        <section class="contentbox">
            <header>
                <h1><?= _('Beschreibung und Hinweise') ?></h1>
            </header>
            <section>
                <?= htmlReady($location->description) ?>
            </section>
        </section>
    <? endif ?>

    <? if (Request::isDialog()) : ?>
        <? if ($geo_coordinates_object instanceof ResourceProperty) : ?>
            <div data-dialog-button>
                <?= \Studip\LinkButton::create(
                    _('Zum Lageplan'),
                    ResourceManager::getMapUrlForResourcePosition($geo_coordinates_object),
                    ['target' => '_blank']
                ) ?>
            </div>
            <?= \Studip\LinkButton::createEdit(
                _('Bearbeiten'),
                $location->getActionURL('edit'),
                [
                    'data-dialog' => 'size=auto'
                ]
            ) ?>
        <? endif ?>
    <? endif ?>
    <? $property_groups = $location->getGroupedProperties($other_properties) ?>
    <? if (count($property_groups)): ?>
        <?= $this->render_partial(
            'resources/resource/_standard_properties_display_part.php',
            [
                'property_groups' => $property_groups
            ]
        ) ?>
    <? endif ?>

    <? $resource_folder = $location->getFolder(); ?>
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

    <? if (count($location->children)): ?>
        <section class="contentbox">
            <header>
                <h1><?= _('Gebäude') ?></h1>
            </header>
            <section>
                <ul class="list-unstyled">
                    <? foreach ($location->findChildrenByClassName('Building') as $child): ?>
                        <li>
                            <a href="<?= $controller->link_for('resources/building/index/' . $child->id); ?>"
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
<? endif ?>
