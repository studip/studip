
<div class="cw-content-projects">
    <? if(!empty($elements)): ?>
        <ul class="cw-tiles">
            <? foreach($elements as $element) :?>
                <li class="tile <?= htmlReady($element['payload']['color'])?>">
                    <a href="<?= URLHelper::getLink('dispatch.php/contents/courseware/courseware#/structural_element/'.$element['id']) ?>">
                        <div class="preview-image" style="background-image: url(<?= htmlReady($element->getImageUrl()) ?>)" ></div>
                        <div class="description">
                            <header><?= htmlReady($element['title']) ?></header>
                            <div class="description-text-wrapper">
                                <p>
                                    <?= htmlReady($element['payload']['description']) ?>
                                </p>
                            </div>
                            <footer>
                                <?= sprintf(ngettext('%d Seite', '%d Seiten', $element->countChildren()), $element->countChildren()); ?>
                            </footer>
                        </div>
                    </a>
                </li>
            <? endforeach; ?>
        </ul>
    <? else: ?>
        <div class="cw-contents-overview-teaser">
            <div class="cw-contents-overview-teaser-content">
                <header><?= _('Ihre persönlichen Lernmaterialien')?></header>
                <p><?= _('Erstellen und Verwalten Sie hier ihre eigenen persönlichen Lernmaterialien in Form von ePorfolios, 
                          Vorlagen für Veranstaltungen oder einfach nur persönliche Inhalte für das Studium. 
                          Entwickeln Sie ihre eigenen (Lehr-)Materialien für Studium oder die Lehre und teilen diese mit anderen Nutzenden.')?></p>
                <a class="button"
                href="<?= $controller->link_for('contents/courseware/create_project', []) ?>"
                data-dialog="size=700"
                title="<?= _('Neues Lernmaterial anlegen') ?>">
                    <?= _('Neues Lernmaterial anlegen') ?>
                </a>
            </div>
        </div>
    <? endif; ?> 
</div>