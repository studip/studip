<li class="template invisible"
    data-template-type="bool">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <input type="hidden"
           value="1"
           class="room-search-widget_criteria-list_input">
    <label class="undecorated">
        <span></span>
    </label>
</li>
<li class="template invisible"
    data-template-type="range">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="range-search-label undecorated">
        <input type="hidden">
        <span></span>
        <div class="range-input-container">
                    <?= _('von') ?>
            <input type="number"
                   class="room-search-widget_criteria-list_input">
                    <?= _('bis') ?>
            <input type="number"
                   class="room-search-widget_criteria-list_input">
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="num">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="number"
               class="room-search-widget_criteria-list_input">
    </label>
</li>
<li class="template invisible"
    data-template-type="select">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <select class="room-search-widget_criteria-list_input">
        </select>
    </label>
</li>
<li class="template invisible"
    data-template-type="date">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <div class="range-input-container">
            <input type="date">
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="date_range">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <div class="range-input-container">
            <input type="date">
            <input type="date">
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
            <input type="text" data-time="yes">
            <?= _('Uhr') ?>
        </div>
    </label>
</li>
<li class="template invisible"
    data-template-type="other">
    <?= Icon::create('trash', 'clickable')->asImg(
        '16px',
        [
            'class' => 'text-bottom remove-icon'
        ]
    ) ?>
    <label class="undecorated">
        <span></span>
        <input type="text"
               class="room-search-widget_criteria-list_input">
    </label>
</li>
