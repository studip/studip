<? if (!$controller->showResult($vote)): ?>
    <? if ($vote->isRunning() && !$nobody) : ?>
        <?= Studip\Button::create(_('Abstimmen'), 'vote', ['value' => $vote->id]) ?>
    <? endif ?>
    <?= Studip\LinkButton::create(_('Ergebnisse'), ContentBoxHelper::href($vote->id, ['preview[]' => $vote->id])) ?>
<? else: ?>
    <?= Studip\LinkButton::create(_('Ergebnisse ausblenden'), ContentBoxHelper::href($vote->id, ['preview' => 0])) ?>
    <?= Request::get('sort')
        ? Studip\LinkButton::create(_('Nicht sortieren'), ContentBoxHelper::href($vote->id, ['preview[]' => $vote->id, 'sort' => 0]))
        : Studip\LinkButton::create(_('Sortieren'), ContentBoxHelper::href($vote->id, ['preview[]' => $vote->id, 'sort' => 1]))
    ?>
    <? if ($vote->changeable && $vote->state == 'active' && !$nobody): ?>
        <?= Studip\LinkButton::create(_('Antwort ändern'), ContentBoxHelper::href($vote->id, ['change' => 1])) ?>
    <? endif; ?>
    <? if (!$vote->anonymous && ($admin || $vote->namesvisibility)): ?>
        <? if (Request::get('revealNames') === $vote->id) : ?>
            <?= Studip\LinkButton::create(_('Namen ausblenden'), ContentBoxHelper::href($vote->id, ['revealNames' => null])) ?>
        <? else : ?>
            <?= Studip\LinkButton::create(_('Namen zeigen'), ContentBoxHelper::href($vote->id, ['revealNames' => $vote->id])); ?>
        <? endif; ?>
    <? endif; ?>
<? endif; ?>