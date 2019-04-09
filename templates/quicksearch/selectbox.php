<?
# Lifter010: TODO

//Kein Javascript aktiviert, also über Select-Box arbeiten. Wir sind automatisch schon in Schritt 2 der
                //non-javascript-Suche.
if ($withButton) : ?>
<div class="quicksearch_frame" style="width: <?= $box_width ?>px;">
<? $withAttributes['style'] = "width: ".($box_width-23)."px;"; ?>
    <? if ($box_align === "left") : ?>
    <?= Icon::create('refresh', 'clickable')->asInput(["class" => 'text-bottom']) ?>
<? endif ?>
<? endif ?>
<select<? foreach ($withAttributes as $attr_name => $attr_value) {
        if($attr_name != 'class') {
            //The class attribute is handled below!
            print ' '.$attr_name.'="'.$attr_value.'"';
        }
    }
    ?> name="<?= $name ?>" class="quicksearch_select <?= array_key_exists('class', $withAttributes) ? htmlReady($withAttributes['class']) : '' ?>">
<? if (count($searchresults)) : ?>
  <? foreach ($searchresults as $result) : ?>
  <option value="<?= htmlReady($result[0]) ?>"><?= htmlReady($result[1]) ?></option>
  <? endforeach ?>
<? else : ?>
  <option value=""><?= _("Keine Treffer gefunden") ?></option>
<? endif ?>
</select>
<? if ($withButton) : ?>
    <? if ($box_align !== "left") : ?>
    <?= Icon::create('refresh', 'clickable', ['title' => _('Suche zurücksetzen')])->asInput(['name'=>$reset_button_name?:'','class'=>'text-bottom',]) ?>
<? endif ?>
    </div>
<? endif ?>
