# Anlegen des Benutzers root

# Benutzer: root@studip ; Password: testing
INSERT IGNORE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', '$2a$08$E2oDjsPcmCrnJT3GiSvHGugLhyUc1mlpcB3FstDzgMwgnIdzi8iSW', 'root', 'Root', 'Studip', 'root@localhost', '', 'standard', 0, NULL, NULL, 'unknown');
INSERT IGNORE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');

#
# wichtige News
#

INSERT IGNORE INTO `news` (`news_id`, `topic`, `body`, `author`, `date`, `user_id`, `expire`, `allow_comments`, `chdate`, `chdate_uid`, `mkdate`) VALUES ('29f2932ce32be989022c6f43b866e744', 'Herzlich Willkommen!', '<!--HTML-->
Das Stud.IP-Team heisst sie herzlich willkommen.<br />
Bitte schauen Sie sich ruhig um!<br /><br />
Wenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingefügt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten später wieder löschen, <strong>da die Passwörter der Accounts öffentlich bekannt sind</strong>.<br />
 
<p>Wenn Sie mit der Stud.IP Open Source-Community in Kontakt treten wollen und Fragen oder Ideen haben, können Sie sich auf unserem eigenen Stud.IP-System mit uns austauschen: <a href="https://develop.studip.de/studip" class="link-extern" target="_blank" rel="noreferrer noopener">develop.studip.de</a></p>

<p>Falls Sie die Einführung an einer Bildungseinrichtung planen und Unterstützung bei der Einführung oder Beratung zum Einsatz von digitalen Tools für die Online Lehre haben, steht Ihnen die Firma data-quest als Dienstleister rund um Stud.IP zur Seite: <a href="https://www.data-quest.de" class="link-extern" target="_blank" rel="noreferrer noopener">https://www.data-quest.de</a></p>', 'Root Studip', UNIX_TIMESTAMP(), '76ed43ef286fb55cf9e41beadb484a9f', 14562502, 1, UNIX_TIMESTAMP(), '', UNIX_TIMESTAMP());
#
# Daten für Tabelle `news_range`
#

INSERT IGNORE INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', 'studip');

