<?php

/**
 * vCard.php - HelperClass to ceate vCard string of a user object
 *
 * Use the export function to create a vCard string of a single user
 * element or an array of userelements
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @copyright   2014 Stud.IP Core-Group
 */
class vCard {

    /**
     * Transforms a user or an array of users into a vCard export string
     *
     * @param User $users Userobject
     * @return String vCard export string
     */
    public static function export($users) {

        // Non array fallback
        if ($users instanceof User) {
            return self::exportUser($users);
        }

        foreach ($users as $user) {
            $export .= self::exportUser($user);
        }

        return $export;
    }

    /**
     * Export of a single user
     *
     * @param User $user Userobject
     * @return String vCard export string
     */
    private static function exportUser(User $user) {

        // If user is not visible export nothing
        if (!get_visibility_by_id($user->id)) {
            return "";
        }

        // vCard exportheader
        $vCard['BEGIN'] = 'VCARD';
        $vCard['VERSION'] = '3.0';
        $vCard['PRODID'] = 'Stud.IP//' . Config::get()->UNI_NAME_CLEAN . '//DE';
        $vCard['REV'] = date('Y-m-d  H:i:s');
        $vCard['TZ'] = date('O');

        // User specific data
        //Fullname
        $vCard['FN'] = $user->getFullname();

        //Name
        $vCard['N'][] = $user->Nachname;
        $vCard['N'][] = $user->Vorname;
        $vCard['N'][] = $user->info->title_rear;
        $vCard['N'][] = $user->info->title_front;

        // Adress
        if (Visibility::verify('privadr', $user->id)) {
            $vCard['ADR;TYPE=HOME'] = $user->info->privadr;
        }

        // Tel
        if (Visibility::verify('private_phone', $user->id)) {
            $vCard['TEL;TYPE=HOME'] = $user->info->privatnr;
        }
        if (Visibility::verify('private_cell', $user->id)) {
            $vCard['TEL;TYPE=CELL'] = $user->info->privatcell;
        }

        // Email
        if (get_local_visibility_by_id($user->id, 'email')) {
            $vCard['EMAIL'] = $user->email;
        }

        // Photo
        if (Visibility::verify('picture', $user->id)) {

            // Fetch avatar
            $avatar = Avatar::getAvatar($user->id);

            // Only export if
            if ($avatar->is_customized()) {
                $vCard['PHOTO;JPEG;ENCODING=BASE64'] = base64_encode(file_get_contents($avatar->getFilename(Avatar::NORMAL)));
            }
        }

        // vCard end
        $vCard['END'] = 'VCARD';

        // Produce string
        foreach ($vCard as $index => $value) {
            $exportString .= $value ? $index . ':' . (is_array($value) ? join(';', $value) : $value) . "\r\n" : "";
        }

        return $exportString;
    }

}
