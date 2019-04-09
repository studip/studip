<style>
    #layout_container {
        min-width: 900px;
    }
</style>
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="base_url" value="plugins.php/blubber/streams/">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="context_id" value="<?= $stream->getId() ?>">
<input type="hidden" id="stream" value="custom">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="browser_start_time" value="">
<script>jQuery(function () { jQuery("#browser_start_time").val(Math.floor(new Date().getTime() / 1000)); });</script>
<input type="hidden" id="loaded" value="1">
<input type="hidden" id="orderby" value="<?= $stream['sort'] === "age" ? "mkdate" : "discussion_time" ?>">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>

<div id="threadwriter" class="globalstream">
    <div class="row writer">
        <div class="context_selector select" title="<?= _("Kontext der Nachricht auswählen") ?>">
            <? $width = "50" ?>
            <?= Icon::create("blubber", "clickable")->asImg($width, ['class' => "select click"]) ?>
            <?= Assets::img($plugin->getPluginURL()."/assets/images/public_blue.svg", ['class' => "public click", 'height' => $width."px"]) ?>
            <?= Icon::create("group3",  "clickable")->asImg($width, ['class' => "private click"]) ?>
            <?= Icon::create("seminar", "clickable")->asImg($width, ['class' => "seminar click"]) ?>
        </div>
        <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>" aria-label="<?= _("Schreib was, frag was.") ?>"><?= ($search ? htmlReady("#".$search)." " : "").(Request::get("mention") ? "@".htmlReady(Request::username("mention")).", " : "") ?></textarea>
        <label title="<?= _("Datei hochladen") ?>" class="uploader">
            <input type="file" style="display: none;" multiple>
            <?= Assets::img('ajax-indicator-black.svg', ['class' => "text-bottom uploading", 'width' => "16px", 'height' => "16px"]) ?>
            <?= Icon::create('upload', 'clickable')->asImg(['class' => "text-bottom upload"]) ?>
        </label>
    </div>
    <div id="context_selector_title" style="display: none;"><?= _("Kontext auswählen") ?></div>
    <div id="context_selector" style="display: none;">
        <input type="hidden" name="content_type" id="context_type" value="">
        <table style="width: 100%">
            <tbody>
                <tr onMousedown="$('#context_type').val('public'); $('#threadwriter .context_selector').removeAttr('class').addClass('public context_selector'); $(this).parent().find('.selected').removeClass('selected'); $(this).addClass('selected'); ">
                    <td style="text-align: center; width: 15%">
                        <label>
                            <?= Assets::img($plugin->getPluginURL()."/assets/images/public.svg", ['class' => "text-bottom", 'height' => "32px"]) ?>
                            <br>
                            <?= _("Öffentlich") ?>
                        </label>
                    </td>
                    <td style="width: 70%">
                        <?= _("Dein Beitrag wird allen angezeigt.") ?>
                    </td>
                    <td style="width: 15%">
                        <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "text-bottom check"]) ?>
                        <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "text-bottom uncheck"]) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
                <tr onMousedown="$('#context_type').val('private'); $('#threadwriter .context_selector').removeAttr('class').addClass('private context_selector'); $(this).parent().find('.selected').removeClass('selected'); $(this).addClass('selected'); ">
                    <td style="text-align: center;">
                        <label>
                            <?= Icon::create('group3', 'info')->asImg(32, ['class' => "text-bottom"]) ?>
                            <br>
                            <?= _("Privat") ?>
                        </label>
                    </td>
                    <td>
                        <? if (count($contact_groups)) : ?>
                        <label>
                        <?= _("An Kontaktgruppe(n)") ?><br>
                        <select multiple name="contact_group[]" id="contact_groups" style="width: 100%" size="<?= count($contact_groups) <= 4 ? count($contact_groups) : "4"  ?>">
                            <? foreach ($contact_groups as $group) : ?>
                            <option value="<?= htmlReady($group['statusgruppe_id']) ?>"><?= htmlReady($group['name']) ?></option>
                            <? endforeach ?>
                        </select>
                        </label>
                        <? else : ?>
                        <a href="<?= URLHelper::getLink("dispatch.php/contact") ?>"><?= _("Legen Sie eine Kontaktgruppe an, um an mehrere Kontakte zugleich zu blubbern.") ?></a>
                        <? endif ?>
                        <br>
                        <?= _("Fügen Sie einzelne Personen mittels @Nutzernamen im Text der Nachricht oder der Kommentare hinzu.") ?>
                    </td>
                    <td style="width: 15%">
                        <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "text-bottom check"]) ?>
                        <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "text-bottom uncheck"]) ?>
                    </td>
                </tr>
                <? $mycourses = BlubberPosting::getMyBlubberCourses() ?>
                <? if (count($mycourses)) : ?>
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
                <tr onMousedown="$('#context_type').val('course'); $('#threadwriter .context_selector').removeAttr('class').addClass('seminar context_selector'); $(this).parent().find('.selected').removeClass('selected'); $(this).addClass('selected'); ">
                    <td style="text-align: center;">
                        <label>
                            <?= Icon::create('seminar', 'info')->asImg(32, ['class' => "text-bottom"]) ?>
                            <br>
                            <?= _("Veranstaltung") ?>
                        </label>
                    </td>
                    <td>
                        <label>
                        <?= _("In Veranstaltung") ?>
                        <select name="context">
                            <? foreach (BlubberPosting::getMyBlubberCourses() as $course_id) : ?>
                            <? $seminar = new Seminar($course_id) ?>
                            <option value="<?= htmlReady($course_id) ?>"><?= htmlReady($seminar->getName()) ?></option>
                            <? endforeach ?>
                        </select>
                        </label>
                    </td>
                    <td style="width: 15%">
                        <?= Icon::create('checkbox-checked', 'info')->asImg(['class' => "text-bottom check"]) ?>
                        <?= Icon::create('checkbox-unchecked', 'info')->asImg(['class' => "text-bottom uncheck"]) ?>
                    </td>
                </tr>
                <? endif ?>
            </tbody>
        </table>
        <div>
            <button class="button" id="submit_button" style="display: none;" onClick="STUDIP.Blubber.prepareSubmitGlobalPosting();">
                <?= _("abschicken") ?>
            </button>
        </div>
        <br>
    </div>
</div>



<div id="context_background">
<ul id="blubber_threads" class="globalstream" aria-live="polite" aria-relevant="additions">
    <? foreach ($threads as $thread) : ?>
    <?= $this->render_partial("streams/_blubber.php", ['thread' => $thread]) ?>
    <? endforeach ?>
    <? if ($more_threads) : ?>
    <li class="more"><?= Assets::img("ajax_indicator_small.gif", ['alt' => "loading"]) ?></li>
    <? endif ?>
</ul>
</div>


<?php

$sidebar = Sidebar::get();
$sidebar->setImage('sidebar/blubber-sidebar.png');
$streamAvatar = StreamAvatar::getAvatar($stream->getId());
if ($streamAvatar->is_customized()) {
    $sidebar->setContextAvatar($streamAvatar);
}

$actions = new ActionsWidget();
$actions->addLink(_("Diesen Stream bearbeiten"), PluginEngine::getURL($plugin, [], 'streams/edit/'.$stream->getId()), Icon::create("edit", "clickable"));
$actions->addLink(_("Diesen Stream löschen"), PluginEngine::getURL($plugin, [], 'streams/delete/'.$stream->getId()), Icon::create("trash", "clickable"), ['onclick' => "return window.confirm('"._("Wirklich löschen?")."');"]);
$sidebar->addWidget($actions);

$controller->addTagCloudWidgetToSidebar($tags, 'custom/' . $stream->getId());
