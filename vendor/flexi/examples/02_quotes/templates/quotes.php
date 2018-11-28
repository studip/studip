<? $title = 'Zitate'; ?>
<div id="header">Zitate <span class="amp">&amp;</span> mehr</div>

<h2>Zitat des Tages (<?= date('d.m.Y', $time) ?>)</h2>
<p>
  <em>
    „<?= $quote_of_the_day['quote'] ?>“
  </em>
  (<?= $quote_of_the_day['author'] ?>)
</p>


<? if (sizeof($quotes)) : ?>
  <h2>Mehr Zitate</h2>
  <? foreach ($quotes as $quote) : ?>
    <p>
      <em>
        „<?= $quote['quote'] ?>“
      </em>
      (<?= $quote['author'] ?>)
    </p>
  <? endforeach ?>
<? endif ?>
