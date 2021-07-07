<?php

class Oer_MymaterialController extends AuthenticatedController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_('Lernmaterialien'));

    }

    public function index_action()
    {
        if (Navigation::hasItem("/oer/mymaterial")) {
            Navigation::activateItem("/oer/mymaterial");
        }
        $this->materialien = OERMaterial::findMine();
        $this->buildSidebar();
    }

    public function edit_action(OERMaterial $material = null)
    {
        Pagelayout::setTitle($material->isNew() ? _('Neues Material hochladen') : _('Material bearbeiten'));
        if ($material->id && !$material->isMine() && !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        $content_types = ['application/x-zip-compressed', 'application/zip', 'application/x-zip'];
        $tmp_folder = $GLOBALS['TMP_PATH'] . '/temp_folder_' . md5(uniqid());
        if (Request::submitted('delete') && Request::isPost()) {
            $material->pushDataToIndexServers('delete');
            $material->delete();
            PageLayout::postSuccess(_('Das Material wurde gelöscht.'));
            $this->redirect('oer/market/index');
            return;
        } elseif (Request::isPost()) {
            $was_new = $material->isNew();
            $material->setData(Request::getArray('data'));
            $material['host_id'] = null;
            $material['license_identifier'] = Request::get('license', 'CC-BY-SA-4.0');
            if ($_FILES['file']['tmp_name']) {
                $material['content_type'] = $_FILES['file']['type'];
                if (in_array($material['content_type'], $content_types)) {
                    mkdir($tmp_folder);
                    \Studip\ZipArchive::extractToPath($_FILES['file']['tmp_name'], $tmp_folder);
                    $material['structure'] = $this->getFolderStructure($tmp_folder);
                    rmdirr($tmp_folder);
                } else {
                    $material['structure'] = null;
                }
                $material['filename'] = $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $material->getFilePath());
            } elseif($_SESSION['NEW_OER']['tmp_name']) {
                $material['content_type'] = $_SESSION['NEW_OER']['content_type'] ?: get_mime_type($_SESSION['NEW_OER']['tmp_name']);
                if (in_array($material['content_type'], $content_types)) {
                    mkdir($tmp_folder);
                    \Studip\ZipArchive::extractToPath($_SESSION['NEW_OER']['tmp_name'], $tmp_folder);
                    $material['structure'] = $this->getFolderStructure($tmp_folder);
                    rmdirr($tmp_folder);
                } else {
                    $material['structure'] = null;
                }
                $material['filename'] = $_SESSION['NEW_OER']['filename'];
                copy($_SESSION['NEW_OER']['tmp_name'], $material->getFilePath());
            }


            if ($_FILES['image']['tmp_name']) {
                $material['front_image_content_type'] = $_FILES['image']['type'];
                move_uploaded_file($_FILES['image']['tmp_name'], $material->getFrontImageFilePath());
            } elseif($_SESSION['NEW_OER']['image_tmp_name']) {
                $material['front_image_content_type'] = get_mime_type($_SESSION['NEW_OER']['image_tmp_name']);
                copy($_SESSION['NEW_OER']['image_tmp_name'], $material->getFrontImageFilePath());
            }
            if (Request::get('delete_front_image')) {
                $material['front_image_content_type'] = null;
            }
            if ($material->isNew() && $material['category'] === 'auto') {
                $material['category'] = $material->autoDetectCategory();
            }
            $material->store();

            if ($was_new) {
                OERMaterialUser::create(
                    [
                        'material_id' => $material->getId(),
                        'user_id' => $GLOBALS['user']->id,
                        'external_contact' => 0,
                        'position' => 1
                    ]
                );
                $material->notifyFollowersAboutNewMaterial();
            }
            $removed_users = Request::getArray('remove_users');
            if (!empty($removed_users)) {
                foreach (Request::getArray('remove_users') as $index => $user) {
                    if (!$index && count($removed_users) === count($material->users)) {
                        continue;
                    }
                    [$external, $user_id] = array_map('trim', explode('_', $user));
                    OERMaterialUser::deleteBySQL(
                        'user_id = ? AND material_id = ? AND external_contact = ?',
                        [$user_id, $material->getId(), $external]
                    );
                }
            }
            if (Request::get('new_user')) {
                [$external, $user_id] = array_map('trim', explode('_', Request::get('new_user')));

                OERMaterialUser::create(
                    [
                        'material_id' => $material->getId(),
                        'user_id' => $user_id,
                        'external_contact' => $external,
                        'position' => count($material->users) + 1
                    ]
                );
            }

            //Topics:
            $tags = Request::getArray('tags');
            if (!empty($tags)) {
                foreach ($tags as $key => $tag) {
                    if (!trim($tag)) {
                        unset($tags[$key]);
                    }
                }
            }
            $material->setTopics($tags);

            $material->pushDataToIndexServers();

            unset($_SESSION['NEW_OER']);

            PageLayout::postSuccess(_('Lernmaterial erfolgreich gespeichert.'));

            if (Request::get('redirect_url')) {
                $this->redirect(URLHelper::getURL(Request::get('redirect_url'), [
                    'material_id' => $material->getId(),
                    'url' => $this->url_for('oer/market/details/' . $material->id)
                ]));
            } else {
                $this->redirect('oer/market/details/' . $material->id);
            }
        }
        if (isset($_SESSION['NEW_OER'])) {
            $this->template = $_SESSION['NEW_OER'];
        }

        $this->usersearch = new SQLSearch("
            SELECT DISTINCT CONCAT('0_', auth_user_md5.user_id), CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname, ' (', auth_user_md5.username, ')')
            FROM auth_user_md5 LEFT JOIN user_info ON (user_info.user_id = auth_user_md5.user_id)
            WHERE (CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ')
                OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                OR auth_user_md5.username LIKE :input) AND " . get_vis_query() . "
            UNION SELECT CONCAT('1_', oer_user.user_id), oer_user.name
            FROM oer_user
            WHERE name LIKE :input
        ", _('Person hinzufügen'), 'user_id');
        $this->tagsearch = new SQLSearch("
            SELECT oer_tags.name, oer_tags.name
            FROM oer_tags
            WHERE name LIKE :input
            ORDER BY oer_tags.name
        ", _("Thema suchen"));
    }

    public function delete_action(OERMaterial $material)
    {
        if ($material->id && !$material->isMine() && !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $material->pushDataToIndexServers('delete');
            $material->delete();
            PageLayout::postSuccess(_('Das Material wurde gelöscht.'));
            $this->redirect('oer/market/index');
            return;
        } else {
            throw new Exception("Use this route with POST.");
        }
    }

    public function statistics_action(OERMaterial $material)
    {
        Pagelayout::setTitle(sprintf(
            _('Zugriffszahlen für %s'),
            $material->name
        ));
        if (!$GLOBALS['perm']->have_perm('root') && $material->user_id && $material->user_id !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        if (Request::get("export")) {
            $this->counter = OERDownloadcounter::findBySQL(
                'material_id = ? ORDER BY mkdate DESC',
                [$material->id]
            );
            $output = [
                ['Datum', 'Longitude', 'Latitude']
            ];
            foreach ($this->counter as $counter) {
                $output[] = [
                    date('Y-m-d H:i:s', $counter['mkdate']),
                    $counter['longitude'],
                    $counter['latitude']
                ];
            }

            $this->render_csv($output, FileManager::cleanFileName('Zugriffszahlen ' . $material->name . '.csv'));
            return;
        }
        $this->counter = OERDownloadcounter::countBySQL("material_id = ?", [$material->id]);
        $this->counter_today = OERDownloadcounter::countBySQL("material_id = :material_id AND mkdate >= :start", [
            'material_id' => $materia->id,
            'start' => mktime(0, 0, 0)
        ]);
    }

    public function show_tmp_image_action()
    {
        if ($_SESSION['NEW_OER']['image_tmp_name'] && file_exists($_SESSION['NEW_OER']['image_tmp_name'])) {
            $this->render_file(
                $_SESSION['NEW_OER']['image_tmp_name'],
                null,
                null,
                'inline'
            );
        } else {
            throw new Exception(_("Datei ist nicht vorhanden"));
        }
    }


    protected function getFolderStructure($folder)
    {
        $structure = [];
        foreach (scandir($folder) as $file) {
            if (!in_array($file, [".", ".."])) {
                $attributes = [
                    'is_folder' => is_dir($folder . "/" . $file) ? 1 : 0
                ];
                if (is_dir($folder . "/" . $file)) {
                    $attributes['structure'] = $this->getFolderStructure($folder . "/" . $file);
                } else {
                    $attributes['size'] = filesize($folder . "/" . $file);
                }
                $structure[$file] = $attributes;
            }
        }
        return $structure;
    }

    public function add_tag_action()
    {
        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }
        $this->material = new OERMaterial(Request::option('material_id'));
        $this->render_nothing();
    }

    private function buildSidebar()
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            _('Eigenes Lernmaterial hochladen'),
            $this->editURL(),
            Icon::create('add'),
            ['data-dialog' => 'size=auto']
        );

        Sidebar::Get()->addWidget($actions);
    }
}
