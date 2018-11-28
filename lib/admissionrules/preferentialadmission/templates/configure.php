<h3><?= htmlReady($rule->getName()) ?></h3>
<label for="prefadmission_conditions" class="caption">
    <?= _('Folgende Personen bei der Platzverteilung bevorzugen:') ?>
</label>
<div id="prefadmission_conditions">
    <span class="nofilter" style="<?=(!$rule->getConditions() ? '' : 'display: none')?>">
        <i><?= _('Sie haben noch keine Auswahl festgelegt.'); ?></i>
    </span>
    <div class="userfilter" style="<?=($rule->getConditions() ? '' : 'display: none')?>">
        <div id="no_conditiongroups" class="ungrouped_conditions">
            <div class="condition_list">
                <?php foreach ($rule->getConditions() as $condition) :
                    $condition->show_user_count = true; ?>

                    <div class="condition" id="condition_<?= $condition->getId() ?>">
                        <?= $condition->toString() ?>
                        <a href="#" onclick="return STUDIP.UserFilter.removeConditionField($(this).parent())"
                            class="conditionfield_delete">
                            <?= Icon::create('trash', 'clickable')->asImg(); ?></a>
                        <input type="hidden" name="conditions[]" value="<?= htmlReady(ObjectBuilder::exportAsJson($condition)) ?>"/>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
    <br><br>
    <a href="<?= URLHelper::getURL('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>"
       onclick="return STUDIP.UserFilter.configureCondition('condition', '<?=
            URLHelper::getLink('dispatch.php/userfilter/filter/configure/prefadmission_conditions') ?>')">
        <?= Icon::create('add', 'clickable', ['title' => _('Bedingung hinzufügen')])->asImg(16, ["alt" => _('Bedingung hinzufügen')]) ?>
        <?= _('Bedingung hinzufügen') ?>
    </a>
</div>
<br>
<label class="caption">
    <input type="checkbox" name="favor_semester"<?= $rule->getFavorSemester() ? ' checked' : '' ?>/>
    <?= _('Höhere Fachsemester bevorzugen') ?>
</label>
