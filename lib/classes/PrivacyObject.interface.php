<?php
interface PrivacyObject
{
    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return StoredUserData object
     */
    public static function getUserdata(User $user);
}
