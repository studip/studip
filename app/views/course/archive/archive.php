<? if ($archivedCourses) : ?>
<?= MessageBox::info(
        ngettext(
            'Veranstaltung erfolgreich archiviert!',
            'Veranstaltungen erfolgreich archiviert!',
            count($archivedCourses)
        )
    ) ?>
<script>
    STUDIP.Archive.removeArchivedCourses([
        <? for($i = 0; $i < count($archivedCourses); $i++ ) : ?>
        <? if ($i > 0) : ?>,<? endif ?>
        "<?= htmlReady($archivedCourses[$i]->id); ?>"
        <? endfor ?>
    ]);
</script>

<p><?= _('Die folgenden Veranstaltungen wurden erfolgreich archiviert') . ':' ?></p>
<table class="default">
    <tr>
        <th><?= _('Name der Veranstaltung') ?></th>
    </tr>
<? foreach ($archivedCourses as $course) : ?>
    <tr>
        <td><?= htmlReady($course->name) ?></td>
    </tr>
<? endforeach ?>
</table>

<? else : 
//no course was archived successfully!
?>
<?= MessageBox::error(_('Fehler beim Archivieren von Veranstaltungen!')) ?>
<? endif ?>

<? if (!Request::isAjax()) :
/*
    If this view isn't requested via AJAX it was loaded from a course
    and we have to provide a link back to the course management page.
*/
?>
    <a href="<?= $controller->url_for('admin/courses') ?>" ><?= _("Zurück zur Veranstaltungsverwaltung") ?></a>
<? endif ?>
