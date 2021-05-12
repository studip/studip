
<div class="cw-content-projects">
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
</div>