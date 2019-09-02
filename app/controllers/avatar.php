<?php
/**
 * AvatarController - Administration of all avatar related settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.2
 */

class AvatarController extends AuthenticatedController
{
    /**
     * Display the avatar information of a user, course or institute
     * @param string $type object type: 'user', 'course' or 'institute'
     * @param string $id ID of the object this avatar belongs to
     */
    public function update_action($type, $id)
    {
        // Check for permission to save a new avatar.
        if ($type == 'user') {
            PageLayout::setHelpKeyword('Basis.HomepageBild');
            PageLayout::setTitle(_('Profilbild ändern'));
            SkipLinks::addIndex(_('Profilbild ändern'), 'edit_avatar');

            $has_perm = $GLOBALS['perm']->have_profile_perm('user', $id);
            $class = 'Avatar';
            $this->cancel_link = $this->url_for('profile', ['username' => User::find($id)->username]);
        } else if ($type == 'institute') {
            PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Einrichtungsbild ändern'));

            $has_perm = $GLOBALS['perm']->have_studip_perm('admin', $id);
            $class = 'InstituteAvatar';
            $this->cancel_link = $this->url_for('institute/basicdata/index', ['cid' => $id]);
        } else {
            PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Veranstaltungsbild ändern'));

            $has_perm = $GLOBALS['perm']->have_studip_perm('tutor', $id);
            $sem = Seminar::getInstance($id);
            $studygroup_mode = $sem->getSemClass()->offsetget('studygroup_mode');
            if ($studygroup_mode) {
                $class = 'StudygroupAvatar';
                $this->cancel_link = $this->url_for('course/studygroup/edit?cid=' . $id);
            } else {
                $class = 'CourseAvatar';
                $this->cancel_link = $this->url_for('course/management?cid=' . $id);
            }
        }

        if (!$has_perm) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, das Bild zu ändern.'));
        }

        if ($type == 'user') {
            Navigation::activateItem('/profile/index');
        } else if ($type == 'institute') {
            Navigation::activateItem('/admin/institute/details');
        } else {
            Navigation::activateItem('/course/admin/avatar');
        }

        $this->customized = false;
        $avatar = $class::getAvatar($id);
        $this->avatar = $avatar->getURL($class::NORMAL);
        if ($avatar->is_customized()) {
            $this->customized = true;
            SkipLinks::addIndex(_('Bild löschen'), 'delete_picture');
        }

        $this->type = $type;
        $this->id = $id;
    }

    /**
     * Upload a new avatar or removes the current avatar.
     * Sends an information email to the user if the action was not invoked by himself.
     * @param string $type object type: 'user', 'course' or 'institute'
     * @param string $id ID of the object this avatar belongs to
     */
    public function upload_action($type, $id)
    {
        CSRFProtection::verifyUnsafeRequest();

        // Check for permission to save a new avatar.
        if ($type == 'user') {
            $has_perm = $GLOBALS['perm']->have_profile_perm('user', $id);
            $class = 'Avatar';
            $redirect = 'profile?username=' . User::find($id)->username;
        } else if ($type == 'institute') {
            $has_perm = $GLOBALS['perm']->have_studip_perm('admin', $id);
            $class = 'InstituteAvatar';
            $redirect = 'institute/basicdata/index';
        } else {
            $has_perm = $GLOBALS['perm']->have_studip_perm('tutor', $id);
            $sem = Seminar::getInstance($id);
            $studygroup_mode = $sem->getSemClass()->offsetget('studygroup_mode');
            if ($studygroup_mode) {
                $class = 'StudygroupAvatar';
                $redirect = 'course/studygroup/edit/?cid=' . $id;
            } else {
                $class = 'CourseAvatar';
                $redirect = 'course/management';
            }
        }

        if (!$has_perm) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, das Bild zu ändern.'));
        }

        if (Request::submitted('reset')) {

            $class::getAvatar($id)->reset();
            if ($type == 'user') {
                Visibility::removePrivacySetting('picture', $id);
            }
            PageLayout::postSuccess(_('Bild gelöscht.'));

        } elseif (Request::submitted('upload')) {
            try {

                // Get the Base64-encoded data from cropper.
                $imgdata = Request::get('cropped-image');

                // Extract actual image data (prepended by mime type and meta data)
                list($type, $imgdata) = explode(';', $imgdata);
                list(, $imgdata) = explode(',', $imgdata);
                $imgdata = base64_decode($imgdata);
                // Write data to file.
                $filename = $GLOBALS['TMP_PATH'] . '/avatar-' . $id . '.png';
                file_put_contents($filename, $imgdata);

                // Use new image file for avatar creation.
                $class::getAvatar($id)->createFrom($filename);

                NotificationCenter::postNotification('AvatarDidUpload', $id);

                $message = _('Die Bilddatei wurde erfolgreich hochgeladen. '
                            .'Eventuell sehen Sie das neue Bild erst, nachdem Sie diese Seite '
                            .'neu geladen haben (in den meisten Browsern F5 drücken).');
                PageLayout::postSuccess($message);

                // Send message to user if necessary.
                if ($type == 'user') {
                    setTempLanguage($id);
                    $this->postPrivateMessage(_("Ein neues Bild wurde hochgeladen.\n"));
                    restoreLanguage();
                    Visibility::addPrivacySetting(_('Eigenes Bild'), 'picture', 'commondata', 1, $id);
                }

                unlink($filename);
            } catch (Exception $e) {
                PageLayout::postError($e->getMessage());
            }
        }
        $this->relocate($redirect);
    }

    /**
     * Deletes a custom avatar.
     * @param string $type object type: 'user', 'course' or 'institute'
     * @param string $id ID of the object this avatar belongs to
     */
    public function delete_action($type, $id)
    {
        // Check for permission to delete avatar.
        if ($type == 'user') {
            $has_perm = $GLOBALS['perm']->have_profile_perm('user', $id);
            $class = 'Avatar';
            $redirect = 'profile';
        } else if ($type == 'institute') {
            $has_perm = $GLOBALS['perm']->have_studip_perm('admin', $id);
            $class = 'InstituteAvatar';
            $redirect = 'institute/basicdata/index';
        } else {
            $has_perm = $GLOBALS['perm']->have_studip_perm('tutor', $id);
            $sem = Seminar::getInstance($id);
            $studygroup_mode = $sem->getSemClass()->offsetget('studygroup_mode');
            if ($studygroup_mode) {
                $class = 'StudygroupAvatar';
                $redirect = 'course/studygroup/edit/?cid=' . $id;
            } else {
                $class = 'CourseAvatar';
                $redirect = 'course/management';
            }
        }

        if (!$has_perm) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, das Bild zu ändern.'));
        }

        $class::getAvatar($id)->reset();

        PageLayout::postMessage(MessageBox::success(_('Das Bild wurde gelöscht.')));
        $this->relocate($redirect);
    }
}
