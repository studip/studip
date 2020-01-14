<?php
    if(Request::submitted('defaultDate')){
        $submitted_date = explode('-', Request::get('defaultDate'));
        $default_date = $submitted_date[2] . '.' . $submitted_date[1] . '.' . $submitted_date[0];
    } else {
        $default_date = strftime('%x', time());
    }
?>
<?= \Studip\Button::create(
        _('Heute'),
        'today',
        [
            'id' => 'booking-plan-jmpdate-button',
            'onClick' => "$('#booking-plan-jmpdate').val('". strftime('%x', time()) ."').trigger('change');"
        ]
    ); ?>
<input id="booking-plan-jmpdate" type="text"
        value="<?= $default_date; ?>">

