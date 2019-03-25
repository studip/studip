<? use Studip\Button, Studip\LinkButton; ?>
<? if(!empty($flash['question_text'])) : ?>
    <? $form_content = array('news_isvisible' => htmlReady(json_encode($news_isvisible)),
              'news_selectable_areas' => htmlReady(json_encode($area_options_selectable)),
              'news_selected_areas' => htmlReady(json_encode($area_options_selected)),
              'news_basic_js' => '',
              'news_comments_js' => '',
              'news_areas_js' => '',
              'news_allow_comments' => $news['allow_comments'],
              'news_topic' => $news['topic'],
              'news_body' => $news['body'],
              'news_startdate' => ($news['date']) ? date('d.m.Y', $news['date']) : "",
              'news_enddate' => ($news['expire']) ? date('d.m.Y', $news['date']+$news['expire']) : "",
              'news_allow_comments' => $news['allow_comments']) ?>
    <?=createQuestion2($flash['question_text'],
        array_merge($flash['question_param'], $form_content),
        $form_content,
        URLHelper::getURL('dispatch.php/'.$route.'#anker')); ?>
<? endif ?>
<form action="<?=URLHelper::getURL('dispatch.php/'.$route.'#anker')?>"
    data-dialog="size=auto" method="POST" class="default collapsable">
    <?=CSRFProtection::tokenTag(); ?>
    <input type="hidden" name="news_basic_js" value="">
    <input type="hidden" name="news_comments_js" value="">
    <input type="hidden" name="news_areas_js" value="">
    <input type="hidden" name="news_isvisible" value="<?=htmlReady(json_encode($news_isvisible))?>">
    <input type="hidden" name="news_selectable_areas" value="<?=htmlReady(json_encode($area_options_selectable))?>">
    <input type="hidden" name="news_selected_areas" value="<?=htmlReady(json_encode($area_options_selected))?>">

    <? if (Request::isXhr()) : ?>
        <? foreach (PageLayout::getMessages() as $msg) : ?>
            <?=$msg?>
            <? $anker = ''; ?>
        <? endforeach ?>
    <? endif ?>

    <fieldset <?= $news_isvisible['news_basic'] ? '' : 'class="collapsed"' ?>>
        <legend class="news_category_header" id="news_basic">
            <?=_("Grunddaten")?>
        </legend>

        <label>
            <span class="required">
                <?= _("Titel") ?>
            </span>
            <input type="text" name="news_topic" class="news_topic news_prevent_submit size-l" aria-label="<?= _('Titel der Ankündigung') ?>"
                   value="<?= htmlReady($news['topic']) ?>" required>
        </label>

        <label>
            <span class="required">
                <?= _("Inhalt") ?>
            </span>

            <? list ($body, $admin_msg) = explode("<admin_msg>", $news['body']); ?>
            <textarea class="news_body add_toolbar wysiwyg size-l" name="news_body" rows="6"
                wrap="virtual" placeholder="<?= _('Geben Sie hier den Ankündigungstext ein') ?>"
                aria-label="<?= _('Inhalt der Ankündigung') ?>" required><?= wysiwygReady($body) ?></textarea>
        </label>

        <label class="col-2">
            <span class="required">
                <?= _('Veröffentlichungsdatum') ?>
            </span>

            <input type="text" class="news_date news_prevent_submit"
                   name="news_startdate" id="news_startdate"
                   data-date-picker
                   value="<? if ($news['date']) echo date('d.m.Y', $news['date']); ?>"
                   aria-label="<?= _('Einstelldatum') ?>" required>
        </label>

        <label class="col-2">
            <span class="required">
                <?= _('Ablaufdatum') ?>
            </span>

            <input type="text" class="news_date news_prevent_submit"
                   name="news_enddate" id="news_enddate"
                   data-date-picker='{">=":"#news_startdate","offset":"#news_duration"}'
                   value="<? if ($news['expire']) echo date('d.m.Y', $news['date'] + $news['expire']) ?>"
                   aria-label="<?= _('Ablaufdatum') ?>" required>
        </label>

        <label class="col-2">
            <?= _('Laufzeit in Tagen') ?>

            <input type="number" class="news_date news_prevent_submit"
                   name="news_duration" id="news_duration"
                   value="<?= $news['expire'] ? round($news['expire'] / (24 * 60 * 60)) : 7 ?>"
                   aria-label="<?= _('Laufzeit') ?>"
                   min="0">
        </label>

        <? if ($anker == 'news_comments') : ?>
            <a name='anker'></a>
        <? endif ?>
        <label>
            <input type="checkbox"
                   id="news_allow_comments" name="news_allow_comments" value="1"
                   <? if ($news['allow_comments']) echo 'checked'; ?>>
            <?= _('Kommentare zulassen') ?>
        </label>
    </fieldset>

    <? if (is_array($comments) && count($comments)) : ?>
        <fieldset <?= $news_isvisible['news_comments'] ? '' : 'class="collapsed"' ?>>
            <legend class="news_category_header" id="news_comments">
                <?=_("Kommentare zu dieser Ankündigung")?>
            </legend>
            <table class="default nohover">
                <tbody>
                    <? foreach ($comments as $index => $comment): ?>
                        <?= $this->render_partial('../../templates/news/comment-box', compact('index', 'comment')) ?>
                    <? endforeach; ?>

                    <? if ($comments_admin): ?>
                        <tfoot>
                            <tr>
                                <td colspan="3">
                                    <?=Button::create(_('Markierte Kommentare löschen'), 'delete_marked_comments', array('title' => _('Markierte Kommentare löschen'))) ?>
                                </td>
                            </tr>
                        </tfoot>
                    <? endif ?>
                </tbody>
            </table>
        </fieldset>
    <? endif ?>

    <fieldset <?= $news_isvisible['news_areas'] ? '' : 'class="collapsed"' ?>>
        <legend class="news_category_header" id="news_areas">
            <?=_('In weiteren Bereichen anzeigen')?>
        </legend>

        <? if ($anker == 'news_areas') : ?>
            <a name='anker'></a>
        <? endif ?>

        <label class="with-action">
            <span>
                <?= _('Suchvorlage auswählen') ?>
            </span>

            <select name="search_preset" aria-label="<?= _('Vorauswahl bestimmter Bereiche, alternativ zur Suche') ?>"
                    onchange="jQuery('input[name=area_search_preset]').click()">
                <option><?=_('--- Suchvorlagen ---')?></option>
                <? foreach($search_presets as $value => $title) : ?>
                    <option value="<?=$value?>"<?=($this->current_search_preset == $value) ? ' selected' : '' ?>>
                        <?=htmlReady($title)?>
                    </option>
                <? endforeach ?>
            </select>

            <?= Icon::create('accept')->asInput([
                'name'           => 'area_search_preset',
                'title'          => _('Vorauswahl anwenden'),
                'formnovalidate' => '',
            ]) ?>
        </label>

        <label class="with-action">
            <span>
                <?= _('Freitextsuche') ?>
            </span>

            <input name="area_search_term" class="news_search_term" type="text" placeholder="<?=_('Suchen')?>"
                   aria-label="<?= _('Suchbegriff') ?>">
            <?= Icon::create('search')->asInput([
                'name'           => 'area_search',
                'title'          => _('Suche starten'),
                'formnovalidate' => '',
            ]) ?>
        </label>


        <div class="news_area_selectable">
            <label>
                <?=_('Suchergebnis')?>
            <select name="area_options_selectable[]" class="news_area_options" size="7" multiple
                    aria-label="<?= _('Gefundene Bereiche, die der Ankündigung hinzugefügt werden können') ?>"
                    ondblclick="jQuery('input[name=news_add_areas]').click()">
            <? foreach ($area_structure as $area_key => $area_data) : ?>
                <? if (is_array($area_options_selectable[$area_key]) && count($area_options_selectable[$area_key])) : ?>
                    <optgroup class="news_area_title"
                            style="background-image: url('<?= Icon::create($area_data['icon'], 'info')->asImagePath() ?>');" label="<?=htmlReady($area_data['title'])?>">
                    <? foreach ($area_options_selectable[$area_key] as $area_option_key => $area_option_title) : ?>
                        <option <?= StudipNews::haveRangePermission('edit', $area_option_key) ? 'value="'.$area_option_key.'"' : 'disabled'?>
                                <?=tooltip($area_option_title);?>>
                            <?= htmlReady(mila($area_option_title))?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endif ?>
            <? endforeach ?>
            </select>
            </label>
        </div>
        <div class="news_area_actions">
            <br>
            <br>
            <br>
            <?= Icon::create('arr_2right')->asInput([
                'name'           => 'news_add_areas',
                'title'          => _('In den Suchergebnissen markierte Bereiche der Ankündigung hinzufügen'),
                'formnovalidate' => '',
            ]) ?>
            <br><br>
            <?= Icon::create('arr_2left')->asInput([
                'name'           => 'news_remove_areas',
                'title'          => _('Bei den bereits ausgewählten Bereichen die markierten Bereiche entfernen'),
                'formnovalidate' => '',
            ]) ?>
        </div>
        <div class="news_area_selected">
            <? foreach ($area_structure as $area_key => $area_data) : ?>
                <? if (isset($area_options_selected[$area_key])) : ?>
                    <? $area_count += count($area_options_selected[$area_key]) ?>
                <? endif ?>
            <? endforeach ?>
            <label>
            <div id="news_area_text">
                <? if ($area_count == 0) : ?>
                    <?=_('Keine Bereiche ausgewählt')?>
                <? elseif ($area_count == 1) : ?>
                    <?=_('1 Bereich ausgewählt')?>
                <? else : ?>
                    <?=sprintf(_('%s Bereiche ausgewählt'), $area_count)?>
                <? endif ?>
            </div>
            <select name="area_options_selected[]" class="news_area_options" size="7" multiple
                    aria-label="<?= _('Bereiche, in denen die Ankündigung angezeigt wird') ?>"
                    ondblclick="jQuery('input[name=news_remove_areas]').click()">
            <? foreach ($area_structure as $area_key => $area_data) : ?>
                <? if (isset($area_options_selected[$area_key]) && count($area_options_selected[$area_key])) : ?>
                    <optgroup class="news_area_title"
                            style="background-image: url('<?= Icon::create($area_data['icon'], 'info')->asImagePath() ?>');" label="<?=htmlReady($area_data['title'])?>">
                    <? foreach ($area_options_selected[$area_key] as $area_option_key => $area_option_title) : ?>
                        <option <?= (StudipNews::haveRangePermission('edit', $area_option_key) OR $may_delete) ? 'value="'.$area_option_key.'"' : 'disabled'?>
                                <?=tooltip($area_option_title);?>>
                            <?= htmlReady(mila($area_option_title))?>
                        </option>
                    <? endforeach ?>
                    </optgroup>
                <? endif ?>
            <? endforeach ?>
            </select>
            </label>
        </div>
    </fieldset>

    <footer data-dialog-button>
        <?  if ($news["mkdate"]) : ?>
            <?= Button::createAccept(_('Änderungen speichern'), 'save_news') ?>
        <? else : ?>
            <?= Button::createAccept(_('Ankündigung erstellen'), 'save_news') ?>
        <? endif ?>
        <? if (Request::isXhr()) : ?>
            <?= LinkButton::createCancel(_('Schließen'), URLHelper::getURL(''), array('rel' => 'close_dialog')) ?>
        <? endif ?>
    </footer>
</form>

<script>
    jQuery('.news_prevent_submit').keydown(function(event) {
        if (event.which === 13) {
            event.preventDefault();
        }
    });
    jQuery('input[name=area_search_term]').keydown(function(event) {
        if (event.which === 13) {
            jQuery('input[name=area_search]').click();
            event.preventDefault();
        }
    });
</script>
