<?php

class ProfilePasswordNavigation
{
    public function isVisible()
    {
        $current_user = User::findCurrent();
        return
            !StudipAuthAbstract::CheckField('auth_user_md5.password', $current_user->auth_plugin)
            && !LockRules::check($current_user->user_id, "password", "user");
    }
}