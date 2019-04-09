<? if ($new['allow_comments']): ?>
    <footer>
        <section class="comments">
            <? if (Request::get('comments')): ?>
                <h1>
                    <?= _('Kommentare') ?>
                </h1>

                <? foreach (StudipComment::GetCommentsForObject($new['news_id']) as $index => $comment): ?>
                    <?= $this->render_partial('news/_commentbox', compact('index', 'comment')) ?>
                <? endforeach; ?>
                <? if (!$nobody) : ?>
                    <form action="<?= ContentBoxHelper::href($new->id, ['comments' => 1]) ?>" method="POST" class="default" style="text-align: left;">
                        <?= CSRFProtection::tokenTag() ?>
                        <input type="hidden" name="comsubmit" value="<?= $new['news_id'] ?>">
                        <fieldset>
                            <legend>
                                <?= _('Kommentieren') ?>
                            </legend>
                            <label>
                                <textarea class="add_toolbar wysiwyg" name="comment_content" style="width:70%" rows="8"
                                          cols="38" wrap="virtual"
                                          placeholder="<?= _('Geben Sie hier Ihren Kommentar ein!') ?>"></textarea>
                            </label>
                        </fieldset>

                        <footer>
                            <?= Studip\Button::createAccept(_('Absenden')) ?>
                        </footer>
                    </form>
                <? endif ?>
            <? else: ?>
                <a href="<?= ContentBoxHelper::href($new['news_id'], ["comments" => 1]) ?>">
                    <?= sprintf(_('Kommentare lesen (%s) / Kommentar schreiben'), StudipComment::NumCommentsForObject($new['news_id']))
                    ?>
                </a>
            <? endif; ?>
        </section>
    </footer>
<? endif; ?>
