<h3><?= $rule->getName() ?></h3>
<?= $tpl ?>
<input type="hidden" name="search_sem_qs_choose" value="title_lecturer_number">

<? if ($mandatory_course) : ?>
    <input type="hidden" name="mandatory_course_id_old" value="<?=$mandatory_course->id?>">
    <label class="caption">
        <?= _('Mitgliedschaft in folgender Veranstaltung überprüfen') ?>:
    </label>
    <p>
        <?=htmlReady($mandatory_course->getFullName('number-name-semester'));?>
        <a href="<?=URLHelper::getScriptLink('dispatch.php/course/details/index/' . $mandatory_course->id) ?>"  data-dialog>
            <?= Icon::create('info-circle', 'inactive', ['title' =>_('Veranstaltungsdetails aufrufen')])->asImg()?>
        </a>
    </p>
<? endif ?>

<label class="caption">
    <?= _('Modus') ?>:
</label>
<div>
     <label>
        <input type="radio" name="modus" value="0" <?=(!$rule->modus ? 'checked' : '')?>>
        <?=_("Mitgliedschaft ist notwendig")?>
    </label>
    <label>
        <input type="radio" name="modus" value="1" <?=($rule->modus ? 'checked' : '')?>>
        <?=_("Mitgliedschaft ist verboten")?>
    </label>
</div>

<label class="caption">
    <?= _('Veranstaltung suchen') ?>:
</label>

<div style="display:inline-block">

<?=
QuickSearch::get("mandatory_course_id", new SeminarSearch('number-name-lecturer'))
    ->render();
?>
<?= SemesterData::GetSemesterSelector(['name' => 'search_sem_sem'], SemesterData::GetSemesterIndexById($_SESSION['_default_sem']), 'key', false)?>

</div>

<br><br>
<script>
    $('#ruleform input[name="modus"]').on('change',
        function () {
            var message = [
                "<?=jsReady($rule->default_message, 'script-double')?>",
                "<?=jsReady($rule->default_message1, 'script-double')?>"
            ];
            $('#ruleform textarea').text(message[this.value]);
        });
</script>