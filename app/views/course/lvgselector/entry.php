<?
$id = htmlReady(implode('_', (array) $area->getId()));
$name = htmlReady($area->getDisplayName());
$expand_id = $area->hasChildren() ? $id : implode('_', (array) $area->getTrailParentId());
?>
<input class="lvgruppe_selection_add_<?= $id ?>"
       data-id="<?= $id ?>" data-course_id="<?= htmlReady($course_id) ?>"
       data-type="<?= get_class($area) ?>"
       type="image"
       name="lvgruppe_selection[add][<?= $id ?>]"
       src="<?= Icon::create('arr_2left', 'sort')->asImagePath(); ?>"
       title="<?= _('Diese LV-Gruppe zuordnen') ?>"
       alt="<?= _('Diese LV-Gruppe zuordnen') ?>"
       <?= (!$area->isAssignable() || $selection->includes($area))
           ? 'style="visibility:hidden;"' : '' ?>>
<? if (isset($show_link) && $show_link) : ?>
  <a class="lvgruppe_selection_expand"
     data-id="<?= htmlReady($expand_id) ?>" data-course_id="<?= htmlReady($course_id) ?>"
     data-type="<?= get_class($area) ?>"
     href="<?= URLHelper::getLink(isset($url) ? $url : '',
                   ['lvgruppe_selection[selected]' => htmlReady($expand_id),
                       'lvgruppe_selection[type]' => get_class($area)]) ?>">
    <?= $name ?>
  </a>
    <? if (isset($pathes) && count($pathes)) : ?>
        <ul>
        <? foreach ($pathes as $path) : ?>
            <li style="background-color:inherit;padding-left:20px;color:#666666">
                <?= htmlReady($path) ?>
            </li>
        <? endforeach; ?>
        </ul>
    <? endif; ?>
<? else : ?>
  <?= $name ?>
  <? if (isset($pathes) && count($pathes)) : ?>
        <ul>
        <? foreach ($pathes as $path) : ?>
            <li style="background-color:inherit;padding-left:20px;color:#666666">
                <?= htmlReady($path) ?>
            </li>
        <? endforeach; ?>
        </ul>
    <? endif; ?>
<? endif ?>
