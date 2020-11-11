<?php
// "Rules"/definitions for critical changes in 5.0
return [
    'ScheduleDidUpdate' => 'Parameters for Notification have changed. You will now receive a #{yellow:Schedule object} instead of user_id and values array.',
    'ScheduleDidCreate' => 'Parameters for Notification have changed. You will now receive a #{yellow:Schedule object} instead of user_id and values array.',

    'get_config' => '#{red:Deprecated} - use #{yellow:Config::get()} instead',
];
