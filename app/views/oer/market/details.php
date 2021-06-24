<div class="contentbar">
    <div class="contentbar_title">
        <?= htmlReady($material['name']) ?>
    </div>
    <div class="contentbar_info">
        <a class="consuming_mode_trigger"
           href="#"
           title="<?= _("Konsummodus ein-/ausschalten") ?>">
        </a>
    </div>
</div>


    <? $url = $material['host_id'] ? $material->host->url."download/".$material['foreign_material_id'] : $controller->link_for("oer/endpoints/download/".$material->getId()) ?>

    <? if ($material['player_url']) : ?>
        <iframe src="<?= htmlReady($material['player_url']) ?>"
                class="lernmarktplatz_player"></iframe>
        <? OERDownloadcounter::addCounter($material->id) ?>
    <? elseif ($material->isVideo()) : ?>
        <video controls
            <?= $material['front_image_content_type'] ? 'poster="'.htmlReady($material->getLogoURL()).'"' : "" ?>
               crossorigin="anonymous"
               src="<?= htmlReady($url) ?>"
               class="lernmarktplatz_player"></video>
    <? elseif ($material->isAudio()) : ?>
        <div>
            <a href="<?= htmlReady($url) ?>" onClick="var player = jQuery('#audioplayer')[0]; if (player.paused == false) { player.pause(); } else { player.play(); }; return false;">
                <img src="<?= htmlReady($material->getLogoURL()) ?>" class="lernmarktplatz_player">
            </a>
        </div>
        <div class="center">
            <audio controls
                   id="audioplayer"
                   crossorigin="anonymous"
                   src="<?= htmlReady($url) ?>"></audio>
        </div>
    <? elseif ($material->isPDF()) : ?>
        <iframe src="<?= htmlReady($url) ?>"
                class="lernmarktplatz_player"></iframe>
    <? elseif ($material['front_image_content_type']) : ?>
        <div style="background-image: url('<?= htmlReady($material->getLogoURL()) ?>');" class="lernmarktplatz_player image"></div>
    <? endif ?>

    <? if ($material->isFolder() && !$material['player_url']) : ?>
        <h2><?= _('Verzeichnisstruktur') ?></h2>
        <ol class="lernmarktplatz structure">
            <? foreach ($material['structure'] as $filename => $file) : ?>
                <?= $this->render_partial("oer/market/_details_file.php", ['name' => $filename, 'file' => $file]) ?>
            <? endforeach ?>
        </ol>
    <? endif ?>

    <? if ($url && $material['filename']) : ?>
        <div class="center">
            <a class="button download_link"
               href="<?= htmlReady($url) ?>" title="<?= _('Herunterladen') ?>"
               download="<?= htmlReady($material['filename']) ?>">
                <?= Icon::create("download", Icon::ROLE_CLICKABLE)->asImg(23, ['class' => "blue"]) ?>
                <?= Icon::create("download", Icon::ROLE_INFO_ALT)->asImg(23, ['class' => "whitebutton"]) ?>
                <div class="filename"><?= htmlReady($material['filename']) ?></div>
            </a>

            <? if ($GLOBALS['perm']->have_perm("autor")) : ?>
                <a class="button download_link"
                   href="<?= $controller->link_for( "oer/market/add_to_course/" . $material->getId()) ?>"
                   data-dialog>
                    <?= Icon::create("seminar+move_right", Icon::ROLE_CLICKABLE)->asImg(23, ['class' => "blue"]) ?>
                    <?= Icon::create("seminar+move_right", Icon::ROLE_INFO_ALT)->asImg(23, ['class' => "whitebutton"]) ?>
                    <?= _('Zu Veranstaltung hinzufügen') ?>
                </a>
            <? endif ?>
        </div>
    <? endif ?>

    <div class="oer_columns">

        <aside>

            <? $tags = $material->getTopics() ?>
            <? if (count($tags) > 0) : ?>
                <div class="tags">
                    <h2><?= _('Themen') ?></h2>
                    <ul class="clean">
                        <? foreach ($tags as $tag) : ?>
                            <li>
                                <a href="<?= $controller->link_for("oer/market", ['tag' => $tag['name']]) ?>">
                                    #
                                    <?= htmlReady(ucfirst($tag['name'])) ?>
                                </a>
                            </li>
                        <? endforeach ?>
                    </ul>
                </div>
            <? endif ?>

            <? if ($material['difficulty_start'] != 1 || $material['difficulty_end'] != 12) : ?>
                <h2><?= _('Niveau') ?></h2>
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8em; color: grey;">
                        <div><?= _('Kindergarten') ?></div>
                        <div><?= _('Aktuelle Forschung') ?></div>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <? for ($i = 1; $i <= 12; $i++) : ?>
                            <div><?= ($i < 10 ? "&nbsp;" : "").$i ?></div>
                        <? endfor ?>
                    </div>
                    <div id="difficulty_slider" style="margin-left: 5px; margin-right: 9px;"></div>

                    <script>
                        jQuery(function () {
                            jQuery("#difficulty_slider").slider({
                                range: true,
                                min: 1,
                                max: 12,
                                disabled: true,
                                values: [<?= htmlReady($material['difficulty_start']) ?>, <?= htmlReady($material['difficulty_end']) ?>]
                            });
                        });
                    </script>
                </div>
            <? endif ?>

            <? if (!Config::get()->OER_DISABLE_LICENSE) : ?>
                <div class="license" style="margin-top: 20px;">
                    <h2><?= _('Lizenz') ?></h2>
                    <? if ($material->license['link']) : ?>
                    <a href="<?= htmlReady($material->license['link']) ?>" target="_blank">
                        <? endif ?>
                        <?= LicenseAvatar::getAvatar($material['license_identifier'])->getImageTag(Avatar::MEDIUM) ?>
                        <?= htmlReady($material['license_identifier']) ?>
                        <? if ($material->license['link']) : ?>
                    </a>
                <? endif ?>
                    <div>
                        <a href="<?= $controller->link_for("oer/market/licenseinfo") ?>" data-dialog>
                            <?= _('Was heißt das?') ?>
                        </a>
                    </div>
                </div>
            <? endif ?>

        </aside>
        <div>
            <div>
                <h2><?= _('Beschreibung') ?></h2>
                <?= formatReady($material['description']) ?>
            </div>

            <h2><?= _('Zum Autor') ?></h2>
            <ul class="author_information clean">
                <? foreach ($material->users as $materialuser) : ?>
                    <li>
                        <? if ($materialuser['external_contact']) : ?>
                            <? $user = $materialuser['oeruser'] ?>
                            <? $image = $user['avatar_url'] ?>
                            <? $host = OERHost::find($user['host_id']) ?>
                            <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
                            <div>
                                <div class="author_name">
                                    <a href="<?= $controller->link_for("oer/market/profile/".$user->getId()) ?>">
                                        <?= htmlReady($user['name']) ?>
                                    </a>
                                </div>
                                <div class="author_host">(<?= htmlReady($host->name) ?>)</div>
                                <div class="description"><?= formatReady($user['data']['description']) ?></div>
                            </div>
                        <? else : ?>
                            <? $user = User::find($materialuser['user_id']) ?>
                            <? $image = Avatar::getAvatar($materialuser['user_id'])->getURL(Avatar::MEDIUM) ?>
                            <div class="avatar" style="background-image: url('<?= $image ?>');"></div>
                            <div>
                                <div class="author_name">
                                    <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $user['username']]) ?>">
                                        <?= htmlReady($user ? $user->getFullName() : _('unbekannt')) ?>
                                    </a>
                                </div>
                                <div class="author_host">(<?= htmlReady(Config::get()->UNI_NAME_CLEAN) ?>)</div>
                                <div class="description">
                                    <? if ($user['oercampus_description']) : ?>
                                    <?= htmlReady($user['oercampus_description']) ?>
                                    <? elseif ($materialuser['user_id'] === $GLOBALS['user']->id) : ?>
                                        <em>
                                            <?= sprintf(_('Noch keine Beschreibung für den %s vorhanden.'), Config::get()->OER_TITLE) ?>
                                            <a href="<?= URLHelper::getLink("dispatch.php/settings/details") ?>">
                                                <?= _('Jetzt eine eingeben.') ?>
                                            </a>
                                        </em>
                                    <? endif ?>
                                </div>
                            </div>
                        <? endif ?>
                    </li>
                <? endforeach ?>
            </ul>
        </div>


    </div>

    <div class="center">
        <? if (!$material['host_id'] && ($material->isMine() || $GLOBALS['perm']->have_perm("root"))) : ?>
            <?= \Studip\LinkButton::create(_('Bearbeiten'), $controller->link_for("oer/mymaterial/edit/".$material->getId()), ['data-dialog' => "1"]) ?>
            <form action="<?= $controller->link_for("oer/mymaterial/edit/".$material->getId()) ?>" method="post" style="display: inline;">
                <?= \Studip\Button::create(_('Löschen'), "delete", ['value' => 1, 'data-confirm' => _('Wirklich löschen?')]) ?>
            </form>
        <? endif ?>
    </div>


<? $allowed_to_review = !$material->isMine() && $GLOBALS['perm']->have_perm("autor") ?>
<? if (!$material->isMine() || count($material->reviews)) : ?>
    <article class="studip">
        <header>
            <h1><?= _('Reviews') ?></h1>
        </header>

        <div class="center">
            <? if ($material['rating'] === null) : ?>
                <? if ($allowed_to_review) : ?>
                    <a style="opacity: 0.3;"
                    title="<?= $GLOBALS['perm']->have_perm("autor") ? _('Geben Sie die erste Bewertung ab.') : _('Noch keine Bewertung abgegeben.') ?>"
                    href="<?= $controller->link_for('oer/market/review/' . $material->getId()) ?>" data-dialog>
                <? endif ?>
                <?= Icon::create("star", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(50) ?>
                <?= Icon::create("star", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(50) ?>
                <?= Icon::create("star", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(50) ?>
                <?= Icon::create("star", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(50) ?>
                <?= Icon::create("star", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(50) ?>
                <? if ($allowed_to_review) : ?>
                    </a>
                <? endif ?>
            <? else : ?>
                <? if ($allowed_to_review) : ?>
                    <a href="<?= $controller->link_for('oer/market/review/' . $material->getId()) ?>" data-dialog title="<?= sprintf(_('%s von 5 Sternen'), round($material['rating'] / 2, 1)) ?>">
                <? endif ?>
                <? $material['rating'] = round($material['rating'], 1) / 2 ?>
                <? $v = $material['rating'] >= 0.75 ? "" : ($material['rating'] >= 0.25 ? "-halffull" : "-empty") ?>
                <?= Icon::create("star$v", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->asImg(50) ?>
                <? $v = $material['rating'] >= 1.75 ? "" : ($material['rating'] >= 1.25 ? "-halffull" : "-empty") ?>
                <?= Icon::create("star$v", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->asImg(50) ?>
                <? $v = $material['rating'] >= 2.75 ? "" : ($material['rating'] >= 2.25 ? "-halffull" : "-empty") ?>
                <?= Icon::create("star$v", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->asImg(50) ?>
                <? $v = $material['rating'] >= 3.75 ? "" : ($material['rating'] >= 3.25 ? "-halffull" : "-empty") ?>
                <?= Icon::create("star$v", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->asImg(50) ?>
                <? $v = $material['rating'] >= 4.75 ? "" : ($material['rating'] >= 4.25 ? "-halffull" : "-empty") ?>
                <?= Icon::create("star$v", $allowed_to_review ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO)->asImg(50) ?>
                <? if ($allowed_to_review) : ?>
                    </a>
                <? endif ?>
            <? endif ?>
        </div>

        <ul class="reviews">
            <? foreach ($material->reviews as $review) : ?>
                <li id="review_<?= $review->getId() ?>" class="review">
                    <div class="avatar">
                        <img width="50px" height="50px" src="<?= htmlReady($review['metadata']['host_id']
                            ? ExternalUser::find($review['user_id'])->avatar_url
                            : Avatar::getAvatar($review['user_id'])->getURL(Avatar::MEDIUM)) ?>">
                    </div>
                    <div class="content">
                        <div class="timestamp">
                            <?= date("j.n.Y G:i", $review['chdate']) ?>
                        </div>
                        <strong>
                            <? if ($review['metadata']['host_id']) : ?>
                                <? $user = ExternalUser::find($review['user_id']) ?>
                                <a href="<?= $controller->link_for("oer/market/profile/".$user->getId()) ?>">
                                    <?= htmlReady($user->name) ?>
                                </a>
                            <? else : ?>
                                <? $user = new User($review['user_id']) ?>
                                <a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => $user['username']]) ?>">
                                    <?= htmlReady($user->getFullName()) ?>
                                </a>
                            <? endif ?>
                        </strong>
                        <span class="origin">(<?= htmlReady($review['metadata']['host_id'] ? $review->host['name'] : Config::get()->UNI_NAME_CLEAN) ?>)</span>
                        <div class="review_text">
                            <?= formatReady($review['content']) ?>
                        </div>
                        <div class="stars">
                            <? $rating = round($review['metadata']['rating'], 1) ?>
                            <? $v = $rating >= 0.75 ? "" : ($rating >= 0.25 ? "-halffull" : "-empty") ?>
                            <?= Icon::create("star$v", Icon::ROLE_INFO)->asImg(16) ?>
                            <? $v = $rating >= 1.75 ? "" : ($rating >= 1.25 ? "-halffull" : "-empty") ?>
                            <?= Icon::create("star$v", Icon::ROLE_INFO)->asImg(16) ?>
                            <? $v = $rating >= 2.75 ? "" : ($rating >= 2.25 ? "-halffull" : "-empty") ?>
                            <?= Icon::create("star$v", Icon::ROLE_INFO)->asImg(16) ?>
                            <? $v = $rating >= 3.75 ? "" : ($rating >= 3.25 ? "-halffull" : "-empty") ?>
                            <?= Icon::create("star$v", Icon::ROLE_INFO)->asImg(16) ?>
                            <? $v = $rating >= 4.75 ? "" : ($rating >= 4.25 ? "-halffull" : "-empty") ?>
                            <?= Icon::create("star$v", Icon::ROLE_INFO)->asImg(16) ?>

                            <? if ($GLOBALS['perm']->have_perm("autor") && !count($review->comments)) : ?>
                                <a href="<?= $controller->link_for("oer/market/discussion/".$review->getId()) ?>" style="font-size: 0.8em;">
                                    <?= _('Darauf antworten') ?>
                                </a>
                            <? endif ?>
                        </div>
                        <div class="comments center">
                            <? if (count($review->comments)) : ?>
                                <a href="<?= $controller->link_for("oer/market/discussion/".$review->getId()) ?>">
                                    <?= Icon::create("comment", Icon::ROLE_CLICKABLE)->asImg(16, ['class' => "text-bottom"]) ?>
                                    <?= sprintf(_('%s Kommentare dazu'), count($review->comments)) ?>
                                </a>
                            <? elseif ($material->isMine()) : ?>
                                <a href="<?= $controller->link_for("oer/market/discussion/".$review->getId()) ?>">
                                    <?= Icon::create("comment", Icon::ROLE_CLICKABLE)->asImg(16, ['class' => "text-bottom"]) ?>
                                    <?= _('Dazu einen Kommentar schreiben') ?>
                                </a>
                            <? endif ?>
                        </div>
                    </div>
                </li>
            <? endforeach ?>
        </ul>

        <div class="center">
            <? if (!$material->isMine() && $GLOBALS['perm']->have_perm("autor")) : ?>
                <?= \Studip\LinkButton::create(_('Review schreiben'), $controller->url_for('oer/market/review/' . $material->getId()), ['data-dialog' => 1]) ?>
            <? endif ?>
        </div>
    </article>
<? endif ?>


<?
$actions = new ActionsWidget();
$GLOBALS['perm']->have_perm(Config::get()->OER_PUBLIC_STATUS);
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions->addLink(
        _('Eigenes Lernmaterial hochladen'),
        $controller->url_for("oer/mymaterial/edit"),
        Icon::create("add", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "1"]
    );
    if (!$material['host_id'] && $material->isMine()) {
        $actions->addLink(
            _('Bearbeiten'),
            $controller->url_for("oer/mymaterial/edit/".$material->getId()),
            Icon::create("edit", Icon::ROLE_CLICKABLE),
            ['data-dialog' => "1"]
        );
    }
}
if ($url && $material['filename']) {
    $actions->addLink(
        _('Herunterladen'),
        $url,
        Icon::create("download", Icon::ROLE_CLICKABLE)
    );
}
if ($GLOBALS['perm']->have_perm("autor")) {
    $actions->addLink(
        _('Zu Veranstaltung hinzufügen'),
        $controller->url_for( "oer/market/add_to_course/" . $material->getId()),
        Icon::create("seminar+move_right", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "1"]
    );
}

if ($material['player_url'] || $material->isVideo() || $material->isPDF()) {
    $actions->addLink(
        _('Vollbild aktivieren'),
        "#",
        Icon::create("fullscreen-on", Icon::ROLE_CLICKABLE),
        ['onclick' => "STUDIP.OER.requestFullscreen('.lernmarktplatz_player');"]
    );
}
$actions->addLink(
    _('Teilen und einbetten'),
    $controller->url_for("oer/market/embed/".$material->getId()),
    Icon::create("code", Icon::ROLE_CLICKABLE),
    ['data-dialog' => "1"]
);

if (!$material['host_id'] && ($GLOBALS['perm']->have_perm("root") || $material->isMine())) {
    $actions->addLink(
        _('Zugriffszahlen'),
        $controller->url_for("oer/mymaterial/statistics/".$material->getId()),
        Icon::create("graph", Icon::ROLE_CLICKABLE),
        ['data-dialog' => "1"]
    );
}

Sidebar::Get()->addWidget($actions);
