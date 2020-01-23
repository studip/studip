<nav class="resource-hierarchy">
    <? if ($room->building->location): ?>
        <a href="<?= $room->building->location->getLink('show') ?>" <?= (Request::isDialog()) ? 'data-dialog' : ''; ?>>
            <?= htmlReady($room->building->location->name) ?>
        </a>
        &gt;
    <? endif ?>
    <? if ($room->building): ?>
        <a href="<?= $room->building->getLink('show') ?>" <?= (Request::isDialog()) ? 'data-dialog' : ''; ?>>
            <?= htmlReady($room->building->name) ?>
        </a>
        &gt;
    <? endif ?>
    <?= htmlReady($room->name) ?>
</nav>

<section class="contentbox">
    <header>
        <h1><?= _('Beschreibung und Hinweise') ?></h1>
    </header>
    <section>
        <? if ($room->description): ?>
            <p><?= htmlReady($room->description) ?></p>
        <? endif ?>
        <ul>
            <? if ($room->room_type): ?>
                <li><?= htmlReady($room->room_type) ?></li>
            <? endif ?>
            <li>
                <?= sprintf(
                    ngettext('%d Sitzplatz', '%d Sitzplätze', intval($room->seats)), intval($room->seats)
                ) ?>
            </li>
        </ul>
    </section>
</section>

<? if ($grouped_properties): ?>
    <?= $this->render_partial(
        'resources/resource/_standard_properties_display_part.php',
        [
            'property_groups' => $grouped_properties
        ]
    ) ?>
<? endif ?>

<? $resource_folder = $room->getFolder(); ?>
<? if ($resource_folder && $resource_folder->getFiles()): ?>
   <section class="contentbox">
       <header>
           <h1><?= _('Dateien') ?></h1>
       </header>
       <table class="default sortable-table" data-sortlist="[[2, 0]]">
           <?= $this->render_partial('files/_files_thead') ?>
           <? foreach ($resource_folder->getFiles() as $file_ref): ?>
               <?= $this->render_partial('files/_fileref_tr',
                   [
                       'file_ref'       => $file_ref,
                       'current_folder' => $resource_folder,
                       'last_visitdate' => time()
                   ]) ?>
           <? endforeach ?>
       </table>
   </section>
<? endif ?>
<? if (!Request::isDialog()) : ?>
    <footer data-dialog-button>
        <? if ($room->userHasPermission(User::findCurrent(), 'autor')) : ?>
            <?= \Studip\LinkButton::create(
                _('Wochenbelegung'),
                $room->getLink('booking_plan'),
                ['target' => '_blank']) ?>
            <?= \Studip\LinkButton::create(
                _('Semesterbelegung'),
                $room->getLink('semester_plan'),
                ['target' => '_blank']) ?>
        <? elseif ($room->bookingPlanVisibleForUser(User::findCurrent())) : ?>
            <?= \Studip\LinkButton::create(
                _('Belegungsplan'),
                $room->getLink('booking_plan'),
                ['data-dialog' => 'size=big']) ?>
            <?= \Studip\LinkButton::create(
                _('Semesterbelegung'),
                $room->getLink('semester_plan'),
                ['data-dialog' => 'size=big']) ?>
        <? endif ?>
        <? if ($room->building) : ?>
            <?= \Studip\LinkButton::create(
                _('Zum Lageplan'),
                ResourceManager::getMapUrlForResourcePosition(
                    $room->building->getPropertyObject('geo_coordinates')
                ),
                ["target" => "_blank"]) ?>
        <? endif ?>
        <? if (!$current_user_is_resource_autor && $room->requestable) : ?>
            <?= \Studip\LinkButton::create(
                _('Raum anfragen'),
                $room->getLink('request'),
                ['data-dialog' => 'size=auto']) ?>
        <? endif ?>
    </footer>
<? endif ?>
