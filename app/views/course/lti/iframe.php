<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body onload="document.ltiLaunchForm.submit();">
    <form name="ltiLaunchForm" method="post" action="<?= htmlReady($launch_url) ?>">
        <? foreach ($launch_data as $key => $value): ?>
            <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value, false) ?>">
        <? endforeach ?>
        <input type="hidden" name="oauth_signature" value="<?= $signature ?>">
        <noscript>
            <button><?= _('Anwendung starten') ?></button>
        </noscript>
    </form>
</body>
</html>
