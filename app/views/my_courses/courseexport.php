<html>
    <head>
       <meta charset="UTF-8">
        <title><?= _('Export Veranstaltungsübersicht') ?></title>
        <style>
            table {
                border: 1px solid #000;
            }
            th {
                font-weight: bold;
                background-color: #c7c7c7;
                border: 1px solid #a8a8a8;
            }
            td {
                border: 1px solid #c7c7c7;
            }
        </style>
    </head>
    <body>
    <? if ($sem_courses && is_array($sem_courses)) : ?>
        <h1><?= _('Meine Veranstaltungen'); ?></h1>
        <div id="my_seminars">
        <? foreach ($sem_courses as $sem_key => $course_group) : ?>
            <h2><?= htmlReady($sem_data[$sem_key]['name']) ?></h2>
            <?= $this->render_partial('my_courses/_exportcourse', [
                'course_collection' => $course_group,
            ]) ?>
        <? endforeach ?>
        </div>
    <? endif ?>
    </body>
</html>
