<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>/">
    <title>Stud.IP - <?= _('Installation') ?> - <?= htmlReady($steps[$step]) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= URLHelper::getLink('assets/images/favicon.svg') ?>">
    <link href="<?= URLHelper::getLink('assets/stylesheets/studip-installer.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= URLHelper::getLink('assets/stylesheets/studip-base.css') ?>" rel="stylesheet" type="text/css">
</head>
<body id="install">
    <form action="<?= $controller->link_for($step) ?>" method="post" class="stage ui-dialog ui-corner-all ui-widget ui-widget-content ui-front studip-dialog ui-dialog-buttons">
        <noscript>
            <input type="hidden" name="basic" value="1">
        </noscript>
        <div class="ui-dialog-titlebar ui-corner-all ui-widget-header ui-helper-clearfix">
            <div><?= _('Installationsassistent') ?></div>
            <div>
                <?= sprintf('Schritt %u/%u:', $current_step, $total_steps) ?>
                <?= htmlReady($steps[$step]) ?>
            </div>
        </div>
        <div class="ui-dialog-content ui-widget-content">
        <?php if ($error): ?>
            <?= MessageBox::error($error, (array) @$error_details) ?>
        <?php endif; ?>
            <?= $content_for_layout ?>
        </div>
        <progress id="progress" value="<?= $current_step ?>" max="<?= $total_steps ?>"></progress>
        <div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">
            <div class="ui-dialog-buttonset">
            <?php if (!$hide_back_button && $previous_step): ?>
                <?= Studip\LinkButton::create(
                    '<< ' . _('zurück'),
                    $controller->url_for($previous_step)
                ) ?>
            <?php elseif ($hide_back_button): ?>
                <?= Studip\LinkButton::create(
                    '<< ' . _('zurück'),
                    $controller->url_for($step),
                    ['style' => 'visibility: hidden;']
                ) ?>
            <?php endif; ?>

            <?php if (!$valid && $button_label): ?>
                <?= Studip\Button::create($button_label, 'continue') ?>
            <?php elseif (!$valid): ?>
                <?= Studip\Button::create(_('Erneut prüfen'), 'check') ?>
            <?php elseif ($next_step): ?>
                <?= Studip\Button::create($button_label ?: (_('Weiter') . ' >>'), 'continue') ?>
            <?php else: ?>
                <?= Studip\Button::create($button_label, 'continue', ['style' => 'visibility: hidden;']) ?>
            <?php endif; ?>
            </div>
        </div>
        <footer>
            <ul>
                <li>
                    <a href="https://hilfe.studip.de/admin/Admins/Installationsanleitung" target="_blank">
                        <?= _('Hilfe') ?>
                    </a>
                </li>
                <li>
                    <a href="https://develop.studip.de" target="_blank">
                        <?= _('Stud.IP Entwicklungs- und Anwendungsforum') ?>
                    </a>
                </li>
            </ul>
        </footer>
    </form>

    <script src="<?= URLHelper::getLink('assets/javascripts/studip-installer.js') ?>"></script>
</body>
</html>
