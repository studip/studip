<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>
            <?= htmlReady(
                sprintf(
                    _('Kalenderwoche: %d. Woche'),
                    $date->format('W')
                )
            ) ?>
        </title>
    </head>
    <body>
        <?= $this->render_partial(
            'booking_comments.php',
            [
                'data' => $data,
                'standalone' => true
            ]
        ) ?>
    </body>
</html>
