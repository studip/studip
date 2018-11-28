<h1 class="table_header_bold">
    <?= _('Daten geändert!') ?>
</h1>

<?= MessageBox::info(sprintf(_("Um eine korrekte Authentifizierung mit Ihren neuen Daten sicherzustellen, wurden Sie automatisch ausgeloggt.<br>Wenn Sie Ihre E-Mail-Adresse geändert haben, müssen Sie das Ihnen an diese Adresse zugesandte Passwort verwenden!<br><br>Ihr aktueller Benutzername ist: %s"), '<b>'. htmlReady($username). '</b>'). '<br>---&gt; <a href="' . URLHelper::getLink('index.php?again=yes') . '">' . _("Login") . '</a> &lt;---') ?>
