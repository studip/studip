<article class="studip">
    <header>
        <h1>
            <? printf(_('Bemerkungen zum Aufbaustudiengang %s (%s)'), $aufbaustg->getDisplayName(), $GLOBALS['MVV_AUFBAUSTUDIENGANG']['TYP']['values'][$aufbaustg->typ]['name']) ?>
        </h1>
    </header>
    <section>
        <?= formatReady($aufbaustg->kommentar) ?>
    </section>
</article>