<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <base href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/">
    <title>Stud.IP - Installation<?= $steps[$step] ? ' - ' . $steps[$step] : '' ?></title>
    <link href="<?= URLHelper::getLink('assets/stylesheets/studip-base.css') ?>" rel="stylesheet" type="text/css">
    <style>
    .stage {
        background: #fff;
        margin: 0 auto;
        padding: 1em;
        width: 800px;
    }
    .stage header h1 {
        background: url('<?= URLHelper::getURL('assets/images/logos/studip4-logo.svg') ?>') no-repeat left -18px center;
        background-size: 120px 60px;
        font-size: 25px;
        line-height: 40px;
        text-indent: 90px;
    }
    section {
        min-height: 15em;
        padding: 0.5em 1em;
    }
    nav {
        border-top: 1px dotted #888;
        text-align: center;
    }
    footer {
        border-top: 1px solid #444;
    }
    h2 {
        margin-bottom: 0;
    }
    dt {
        float: left;
        width: 200px;
    }
    dd {
        margin-left: 200px;
        word-break: break-all;
    }
    dd.failed {
        background: url('<?= URLHelper::getURL('assets/images/icons/red/decline.svg') ?>') no-repeat top left;
        color: red;
        padding-left: 20px;
    }
    dd.success {
        background: url('<?= URLHelper::getURL('assets/images/icons/green/accept.svg') ?>') no-repeat top left;
        color: green;
        padding-left: 20px;
    }
    dd code {
        font-weight: bold;
        white-space: nowrap;
    }
    dd textarea {
        width: 100%;
        height: 40em;
    }
    div.type-text {
        clear: both;
    }
    label:not(.plain) {
        display: block;
        float: left;
        padding: 2px;
        width: 100px;
    }
    label + input {
        display: block;
        margin: 1px;
        margin-left: 100px;
    }
    </style>
</head>
<body id="install">
    <div class="stage">
        <header>
            <h1>Installation</h1>
        <?php if ($steps[$step]): ?>
            <h2><?= $steps[$step] ?></h2>
        <?php endif; ?>
        </header>
        <form action="<?= $controller->link_for('admin/install', $step) ?>" method="post">
        <?php if ($error): ?>
            <?= MessageBox::error($error) ?>
        <?php endif; ?>
            <section>
                <?= $content_for_layout ?>
            </section>
            <nav>
            <?php if ($previous_step): ?>
                <?= Studip\LinkButton::create(
                    '<< zurück',
                    $controller->url_for('admin/install', $previous_step)
                ) ?>
            <?php else: ?>
                <?= Studip\LinkButton::create(
                    '<< zurück',
                    $controller->url_for('admin/install', $step),
                    ['style' => 'visibility: hidden;']
                ) ?>
            <?php endif; ?>

            <?php if (!$valid && $button_label): ?>
                <?= Studip\Button::create($button_label, 'continue') ?>
            <?php elseif (!$valid): ?>
                <?= Studip\Button::create('Erneut prüfen', 'continue') ?>
            <?php elseif ($next_step): ?>
                <?= Studip\Button::create('Weiter >>', 'continue') ?>
            <?php else: ?>
                <?= Studip\Button::create($button_label, 'continue', ['style' => 'visibility: hidden;']) ?>
            <?php endif; ?>
            </nav>
        </form>
        <footer>
            Hilfe | Blog
        </footer>
    </div>
</body>
</html>
