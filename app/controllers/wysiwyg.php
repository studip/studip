<?php
/**
 * wysiwyg.php - Provide web services for the WYSIWYG editor.
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */

class WysiwygException extends Exception {};

class WysiwygHttpException extends WysiwygException {};

class WysiwygHttpExceptionBadRequest extends WysiwygHttpException
{
    public function __construct($message = '', $previous = null) {
        parent::__construct($message, 400, $previous);
    }
}

class WysiwygHttpExceptionForbidden extends WysiwygHttpException
{
    public function __construct($message = '', $previous = null) {
        parent::__construct($message, 403, $previous);
    }
}

class WysiwygHttpExceptionNotFound extends WysiwygHttpException
{
    public function __construct($message = '', $previous = null) {
        parent::__construct($message, 404, $previous);
    }
}

class WysiwygHttpExceptionMethodNotAllowed extends WysiwygHttpException
{
    public function __construct($message = '', $previous = null) {
        parent::__construct($message, 405, $previous);
    }
}

class WysiwygController extends AuthenticatedController
{
    const UPLOAD_PERMISSION = 'autor'; // minimum permission level for uploading
    const FOLDER_NAME = 'Wysiwyg Uploads';
    const FOLDER_DESCRIPTION = 'Vom WYSIWYG Editor hochgeladene Dateien.';

    /**
     * Handle the WYSIWYG editor's file uploads.
     *
     * Files must be posted as an HTML array named "files":
     *   <input type="file" name="files[]" multiple />
     *
     * Files will be stored in a folder named "Wysiwyg Uploads". If the
     * folder doesn't exist, it will be created.
     *
     * Results are returned as JSON-encoded array:
     *
     * [{"name": filename, "type": mime-type, "url": download-link},
     *  {"name": filename, "type": mime-type, "error": error-message},
     *  ...]
     *
     * Each array-entry corresponds to a single file, each file that was
     * sent with the post request has exactly one entry.
     *
     * Entries with the property "url" correspond to successful uploads.
     * Entries with the property "error" correspond to failed uploads.
     */
    public function upload_action()
    {
        try {
            CSRFProtection::verifyUnsafeRequest();

            $user = User::findCurrent();

            //try to find an already existing WYSIWYG folder inside the
            //user's personal file area:
            $wysiwyg_folder = Folder::findOneBySql(
                "range_id = :user_id
                AND folder_type = 'PublicFolder'
                AND name = :wysiwyg_name ",
                [
                    'user_id' => $user->id,
                    'wysiwyg_name' => self::FOLDER_NAME
                ]
            );

            if (!$wysiwyg_folder) {
                //get the top folder of the user's personal file area and its FolderType:
                $top_folder = Folder::findTopFolder($user->id)->getTypedFolder();

                $wysiwyg_folder = new PublicFolder(Folder::build([
                    'user_id' => $user->id,
                    'name' => self::FOLDER_NAME,
                    'description' => self::FOLDER_DESCRIPTION
                ]));

                if (!$top_folder->createSubfolder($wysiwyg_folder)) {
                    $this->render_json(_('WYSIWYG-Ordner für hochgeladene Dateien konnte nicht erstellt werden!'));
                    return;
                }
            } else {
                $wysiwyg_folder = $wysiwyg_folder->getTypedFolder();
            }

            //Ok, we have our folder where we can store the uploaded files in:
            $response = [];

            if (!$wysiwyg_folder->isWritable($user->id)) {
                throw new AccessDeniedException();
            }
            if (Request::isPost() && is_array($_FILES['files'])) {
                $validatedFiles = FileManager::handleFileUpload(
                    $_FILES['files'],
                    $wysiwyg_folder,
                    $GLOBALS['user']->id
                );

                if (count($validatedFiles['error']) > 0) {
                    // error during upload: display error message:
                    $this->render_json(_('Beim Hochladen ist ein Fehler aufgetreten ') . "\n" .
                        join("\n", $validatedFiles['error'])
                    );
                    return;
                }

                //all files were uploaded successfully:
                $storedFiles = [];
                foreach ($validatedFiles['files'] as $fileref) {
                    $response['files'][] = [
                        'name' => $fileref->name,
                        'type' => $fileref->file->mime_type,
                        'url'  => $fileref->getDownloadURL()
                    ];
                }
            }
        } catch (AccessDeniedException $e) {
            $response = $e->getMessage();
        }
        $this->render_json($response); // send HTTP response to client
    }

    /**
     * Store or retrieve settings.
     *
     * Settings are further subdivided into groups. For example: global,
     * seminar- and user-specific settings (see below).
     *
     * HTTP GET
     * returns a JSON object with current settings.
     *
     * HTTP PUT
     * expects a JSON object with settings to store and returns
     * updated settings as a JSON object. Some settings are read-only,
     * others can only be set if the user has the necessary access level.
     *
     * Currently only the following basic features are supported:
     *
     * HTTP GET wysiwyg/settings/global
     *   Always returns:
     *   {
     *     "upload": {
     *       "permission": "autor",
     *         "folder": {
     *           "name": "Wysiwyg Uploads",
     *           "description": "Vom WYSIWYG Editor hochgeladene Dateien."
     *         }
     *       }
     *     }
     *   }
     *
     * HTTP GET wysiwyg/settings/users/current
     *   Always returns following setting for the authenticated user:
     *   {
     *     "disabled": false | true
     *   }
     *
     * HTTP PUT wysiwyg/settings/users/current
     *   Allows only to reset or set the disabled state with:
     *   {
     *     "disabled": false | true
     *   }
     *
     * Below is a specification of possible future extensions to this
     * interface, that are based on current feature requests by users
     * (mainly people from ELMO, ELAN and ECULT).
     *
     * wysiwyg/settings/global
     *   Common settings for all WYSIWYG editors throughout Stud.IP.
     * wysiwyg/settings/seminars
     *   Settings of all seminars.
     *   Listed seminars depend on access level:
     *     root => full access to all seminars
     *     dozent, tutor of a seminar => full access to those seminars
     *     others => read-access to seminars they are a member of
     * wysiwyg/settings/seminars/ID
     *   Settings of the seminar with the given ID.
     *   Access permissions: see above.
     * wysiwyg/settings/seminars/ID/users
     *   Seminar's settings for all its users.
     *   Access permissions: see above.
     * wysiwyg/settings/seminars/ID/users/ID
     *   Seminar's settings for a specific user in that seminar.
     *   Access permissions: see above.
     * wysiwyg/settings/users
     *   Settings of all users.
     *   Listed users depend on access level:
     *     root => full access to all users
     *     not root => full access to own settings only
     * wysiwyg/settings/users/ID
     *   Settings of the user with the given ID.
     *   Access permissions: see above.
     * wysiwyg/settings/users/ID/seminars
     *   User's settings for all seminars the user is a member of.
     *   Access permissions: see above.
     * wysiwyg/settings/users/ID/seminars/ID
     *   User's settings for the seminar with the given ID.
     *   Access permissions: see above.
     *
     * The difference of seminar's settings for a user and user's settings
     * for a seminar:
     *
     *   A seminar's teacher may want to set the upload directory for each user
     *   to a separate one, which should not be overwritable by a user, in
     *   order to make sure that users cannot see other users uploads (there
     *   are other ways to do this, but it's just an example).
     *
     *   A user might want to have a specific upload directory in order to
     *   collaborate better with other users in the same seminar (e.g. when
     *   students form a study group).
     *
     *   For example the ELMO module needs such settings.
     *
     * JSON scheme for access to wysiwyg/settings:
     * {
     *   "global": { "SETTING": ..., ... },
     *   "seminars": {
     *     "ID": {
     *       "users": { "ID": {...}, ... },
     *       "SETTING": ...,
     *       ...
     *     },
     *     "ID": {...},
     *     ...
     *   },
     *   "users": {
     *     "ID": {
     *       "seminars": { "ID": {...}, ... },
     *       "SETTING": ...,
     *       ...
     *     },
     *     "ID": {...},
     *     ...
     *   }
     * }
     *
     * When accessing a sub-resource that resource's branch of the JSON scheme
     * will be returned.
     */
    public function settings_action()
    {
        try {
            if (!Request::isGet() && !Request::isPut()) {
                throw new WysiwygHttpExceptionMethodNotAllowed(
                    _('Nur die HTTP-Methoden GET und PUT sind erlaubt.')
                );
            }

            $arguments = func_get_args();
            $settingsGroup = array_shift($arguments);

            if (Request::isPut()) {
                $this->setSettings($settingsGroup, $arguments);
            }
            $this->render_json($this->objectToArray(
                $this->getSettings($settingsGroup, $arguments)
            ));
        } catch (WysiwygHttpException $e) {
            $this->set_status($e->getCode());
            $this->set_content_type('text/plain; charset=utf-8');
            $this->render_text($e->getMessage());
        }
    }

    /**
     * Set WYSIWYG settings for a specific group.
     *
     * Dummy implementation: Currently only accepts setting the
     * disabled flag for wysiwyg/settings/users/current.
     *
     * The HTTP request's body must contain a JSON document of the form:
     * {
     *   "disabled": true | false
     * }
     *
     * If the JSON contains other additional values they will be ignored.
     *
     * @param string $group Must be set to 'users'.
     * @param array $arguments Must contain exactly one entry: 'current'.
     */
    private function setSettings($group, $arguments) {
        $user = array_shift($arguments);
        if ($group !== 'users' || $user !== 'current') {
            throw new WysiwygHttpExceptionForbidden(
                _('Zugriff verweigert')
            );
        }

        $subgroup = array_shift($arguments);
        if (($subgroup !== null && $subgroup !== '') || count($arguments) > 0) {
            throw new WysiwygHttpExceptionNotFound(
                _('Die Benutzereinstellungen enthalten keine Untergruppen.')
            );
        }

        $data = json_decode(file_get_contents('php://input'));
        if (isset($data->disabled)) {
            $config = $GLOBALS['user']->cfg;
            //$config->WYSIWYG_DISABLED = (boolean)$data->disabled;
            $config->store(
                'WYSIWYG_DISABLED',
                (boolean)$data->disabled
            );
        } else {
            throw new WysiwygHttpExceptionBadRequest(
                _('Die Anfrage enthält ungültige Werte.')
            );
        }
        // all unknown parameters are ignored
    }

    /**
     * Return WYSIWYG settings for a specific group.
     *
     * @param $group string The requested settings group: 'user', 'seminar',
     * 'global' or 'all'. If the group is set to 'all' then all levels will be
     * returned. If the group is unknown an error will be thrown.
     *
     * @return object Settings for the requested group.
     */
    private function getSettings($group, $arguments)
    {
        switch ($group) {
            case null: return $this->getAllSettings();
            case 'global': return $this->getGlobalSettings($arguments);
            case 'users': return $this->getUserSettings($arguments);
        }
        throw new WysiwygHttpExceptionNotFound(
            _('Die angeforderte Gruppe von Einstellungen existiert nicht.')
        );
    }

    /**
     * Return all WYSIWYG settings.
     *
     * Returns an object with properties named after settings groups,
     * containing the respective group's settings. For example:
     *
     * {
     *     "global": {...}
     *     "seminars": {...},
     *     "users": {...},
     * }
     *
     * @return object All settings.
     */
    private function getAllSettings()
    {
        $settings = new stdClass;
        $settings->global = $this->getGlobalSettings();
        $settings->users = $this->getUserSettings();
        return $settings;
    }

    /**
     * Return global WYSIWYG settings.
     *
     * @return object Global settings.
     */
    private function getGlobalSettings($arguments = [])
    {
        $subgroup = array_shift($arguments);
        if (($subgroup !== null && $subgroup !== '') || count($arguments) > 0) {
            throw new WysiwygHttpExceptionNotFound(sprintf(
                _('Die globalen Einstellungen enthalten keine Untergruppen.'),
                $level
            ));
        }
        $settings = new stdClass;
        $settings->disabled = !\Config::get()->WYSIWYG;
        $settings->upload = new stdClass;
        $settings->upload->permission = self::UPLOAD_PERMISSION;
        $settings->upload->folder = new stdClass;
        $settings->upload->folder->name = self::FOLDER_NAME;
        $settings->upload->folder->description = self::FOLDER_DESCRIPTION;
        return $settings;
    }

    /**
     * Return current user's WYSIWYG settings.
     *
     * @return object User's settings.
     */
    private function getUserSettings($arguments = [])
    {
        // NOTE simulate a list of users containing only the current
        // user until this is implemented correctly
        $settings = new stdClass;
        $settings->current = $this->getCurrentUserSettings();

        $userId = array_shift($arguments);
        if ($userId === null || $userId === '' && count($arguments) === 0) {
            return $settings;
        }

        if ($userId === 'current') {
            $subgroup = array_shift($arguments);
            if (($subgroup !== null && $subgroup !== '') || count($arguments) > 0) {
                throw new WysiwygHttpExceptionNotFound(
                    _('Die Benutzereinstellungen enthalten keine Untergruppen.')
                );
            }
            return $settings->current;
        }

        throw new WysiwygHttpExceptionForbidden(
            _('Zugriff verweigert.')
        );
    }

    /**
     * Return current user's WYSIWYG settings.
     *
     * @return object User's settings.
     */
    public function getCurrentUserSettings()
    {
        $config = $GLOBALS['user']->cfg;
        $settings = new stdClass;
        $settings->disabled = (boolean)$config->WYSIWYG_DISABLED;
        return $settings;
    }

    /**
     * Recursively convert objects to associative arrays.
     *
     * Workaround for broken StudipController::render_json.
     *
     * If the data is neither object nor array then it will be
     * returned unchanged.
     *
     * @param mixed $data Data to convert.
     *
     * @return mixed Converted data.
     */
    private function objectToArray($data)
    {
        if (gettype($data) === 'object') {
            $data = (array)$data;
        }
        if (gettype($data) === 'array') {
            foreach ($data as $key => $value) {
                $data[$key] = $this->objectToArray($value);
            }
        }
        return $data;
    }
}
