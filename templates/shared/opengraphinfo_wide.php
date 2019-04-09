<?php
$videofiles = $og->getVideoFiles();
$audiofiles = $og->getAudioFiles();
$og['image'] = filter_var($og['image'], FILTER_VALIDATE_URL) ? $og['image'] : '';
if (Config::get()->LOAD_EXTERNAL_MEDIA === "proxy" && Seminar_Session::is_current_session_authenticated()) {
    $media_url_func = function ($url) {
        return $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/media_proxy?url=' . urlencode($url);
    };
} elseif (Config::get()->LOAD_EXTERNAL_MEDIA === "deny") {
    $media_url_func = function ($url) {
        return '';
    };
} else {
    $media_url_func = function ($url) {
        return $url;
    };
}
?>
<div class="opengraph <? if (count($videofiles) > 0) echo 'video'; ?> <? if (count($audiofiles) > 0) echo 'audio'; ?>">
<? if ($og['image'] && count($videofiles) === 0): ?>
    <a href="<?= URLHelper::getLink($og['url'], [], false) ?>" class="image"
       target="_blank" rel="noopener noreferrer"
       style="background-image:url(<?= htmlReady($media_url_func($og['image'])) ?>)">
    </a>
<? endif; ?>
    <a href="<?= URLHelper::getLink($og['url'], [], false) ?>" class="info"
       target="_blank" rel="noopener noreferrer">
        <strong><?= htmlReady($og['title']) ?></strong>
    <? if (!count($videofiles)) : ?>
        <p><?= htmlReady($og['description']) ?></p>
    <? endif ?>
    </a>
<? if (count($videofiles)) : ?>
    <div class="video">
    <? if (in_array($videofiles[0][1], ["text/html", "application/x-shockwave-flash"])) : ?>
        <a href="<?= htmlReady($videofiles[0][0]) ?>"
           class="flash-embedder"
           style="background-image: url('<?= htmlReady($media_url_func($og['image'])) ?>');"
           title="<?= _("Video abspielen") ?>">
            <?= Icon::create('play', 'clickable')->asImg(80, ["class" => "play"])?>
        </a>
    <? else : ?>
        <video width="100%" height="200px" controls>
        <? foreach ($videofiles as $file) : ?>
            <source src="<?= htmlReady($media_url_func($file[0])) ?>"<?= $file[1] ? ' type="'.htmlReady($file[1]).'"' : "" ?>></source>
        <? endforeach ?>
        </video>
    <? endif ?>
    </div>
<? endif ?>
<? if (count($audiofiles)) : ?>
    <div class="audio">
    <? if (in_array($audiofiles[0][1], ["text/html", "application/x-shockwave-flash"])) : ?>
        <a href="<?= htmlReady($audiofiles[0][0]) ?>"
           class="flash-embedder"
           style="background-image: url('<?= htmlReady($media_url_func($og['image'])) ?>');"
           title="<?= _("Audio abspielen") ?>">
            <?= Icon::create('play', 'clickable')->asImg(100)?>
        </a>
    <? else : ?>
        <audio width="100%" height="50px" controls>
        <? foreach ($audiofiles as $file) : ?>
            <source src="<?= htmlReady($media_url_func($file[0])) ?>"<?= $file[1] ? ' type="'.htmlReady($file[1]).'"' : "" ?>></source>
        <? endforeach ?>
        </audio>
    <? endif ?>
    </div>
<? endif ?>
</div>
