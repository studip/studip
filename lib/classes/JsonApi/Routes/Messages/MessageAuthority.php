<?php

namespace JsonApi\Routes\Messages;

use Message;
use User;

class MessageAuthority
{
    public static function canCreateMessage(User $user)
    {
        return $user->id !== 'nobody';
    }

    public static function canShowMessage(User $user, Message $message)
    {
        return $message->permissionToRead($user->id);
    }

    public static function canShowMessagesOfUser(User $user, User $otherUser)
    {
        return $user->id === $otherUser->id;
    }

    public static function canDeleteMessage(User $user, Message $message)
    {
        return $message->permissionToRead($user->id);
    }
}
