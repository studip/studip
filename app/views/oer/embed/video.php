<video controls
    <?= ($material['front_image_content_type'] ? 'poster="'.htmlReady($material->getLogoURL()).'"' : "") ?>
    crossorigin="anonymous"
    class='lernmarktplatz_player'
    src="<?= htmlReady($url) ?>"></video>
<?= $this->render_partial("oer/embed/_link") ?>
