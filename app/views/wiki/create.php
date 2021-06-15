<p>
    <?= _('Hier können Sie eine neue Wiki-Seite erstellen.') ?>
    <br />
    <?= _('Bitte beachten Sie:') ?>
    <?= _('Eckige Klammern und das Zeichen | sind im Titel nicht erlaubt.') ?>
</p>

<form action="<?= URLHelper::getLink('wiki.php', ['view' => 'editnew', 'lastpage' => $keyword]) ?>" method="post" class="default">
    <label>
        <span class="required"><?= _('Titel') ?></span>
        <input required type="text" name="keyword" pattern="[^\][|]+"
               placeholder="<?= _('Name der Wiki-Seite') ?>">
    </label>

    <label>
        <span class="required"><?= _('Vorgängerseite') ?></span>
        <select name="ancestor_select" id="ancestor_select">
            <option value=""> <?= _('nicht im Inhaltsverzeichnis') ?> </option>
            <option value="<?= 'WikiWikiWeb' ?>" <?= $this->keyword == $keyword ? 'selected="selected"' : '' ?> >
                <?= _('Wiki-Startseite')  ?>
            <? foreach ($wiki_page_names as $keyword): ?>
                <? if ($keyword != 'WikiWikiWeb') : ?>
                <option value="<?= htmlReady($keyword) ?>" <?= $this->keyword == $keyword ? 'selected="selected"' : '' ?> >
                    <?= htmlReady($keyword) ?>
                </option>
                <? endif ?>
            <? endforeach ?>
        </select>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('wiki.php', compact('keyword'))) ?>
    </footer>
</form>
