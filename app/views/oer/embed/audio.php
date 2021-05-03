<div>
    <a href="<?= htmlReady($url) ?>"
       onClick="var player = jQuery('#audioplayer')[0]; if (player.paused == false) { player.pause(); } else { player.play(); }; return false;">
    <img src="<?= htmlReady($material->getLogoURL()) ?>" class="lernmarktplatz_player">
    </a>
</div>
<div class="center">
    <audio controls
           id="audioplayer"
           crossorigin="anonymous"
           src="<?= htmlReady($url) ?>"></audio>
</div>
<?= $this->render_partial("oer/embed/_link") ?>
