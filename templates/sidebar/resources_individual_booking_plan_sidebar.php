<? $colour_amount = 4; ?>
<section class="colour-selectors">
    <p class="info-text">
        <?= sprintf(
            _('Im unteren Bereich können Sie bis zu %d Farben frei wählen und diese via Drag & Drop auf Termine ziehen. Wenn Sie fertig sind, klicken Sie auf das Drucken-Symbol unter den Farbwählern.'),
            $colour_amount
        ) ?>
    </p>
    <? for ($i = 0; $i < $colour_amount; $i++): ?>
        <div class="colour-selector" style="background-color: #000000;">
            <input type="color" value="#000000" class="big-colour-input">
        </div>
    <? endfor ?>
    <?= Icon::create('print', 'clickable')->asImg(
        '32px',
        [
            'class' => 'text-bottom print-action',
            'title' => _('Individuelle Druckansicht drucken'),
            'onclick' => 'javascript:window.print();'
        ]
    ) ?>
</section>
