<div class="index_container">
    <?= MessageBox::error(_('Cookies sind nicht aktiviert!'), [
            _('Die Anmeldung für Stud.IP ist nur möglich, wenn Sie das Setzen von Cookies erlauben!'),
            sprintf(
                    _('Bitte ändern Sie die Einstellungen Ihres Browsers und wiederholen Sie den %sLogin%s'),
                    '<a href="' . URLHelper::getLink($_SERVER['REQUEST_URI']).'">', '</a>'),
            sprintf(
                    _('Bitte wenden Sie sich bei Problemen an: <a href="mailto:%1$s">%1$s</a>'),
                    $GLOBALS['UNI_CONTACT'])
    ]) ?>
</div>
