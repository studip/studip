<? printf(_("%s. Woche"), strftime("%V", $calendars[0]->getStart())) ?>
<span class="hidden-large-up"><?= date('Y', $calendars[0]->getStart()) ?></span>
<span class="hidden-medium-down"><? printf(_("vom %s bis %s"), strftime("%x", $calendars[0]->getStart()), strftime("%x", $calendars[$week_type - 1]->getStart())) ?></span>
