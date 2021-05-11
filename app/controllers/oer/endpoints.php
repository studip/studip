<?php

class Oer_EndpointsController extends StudipController
{

    public function index_action()
    {
        $this->controllerreflection = new ReflectionClass($this);
    }

    /**
     * Returns the public key and some other information of this host.
     * The returned text is a json-object like
     * [code]
     *     {
     *         "name": "name of this host",
     *         "public_key": "the armored public key",
     *         "url": "the preferred URL of this host. May be configured in config_local.inc.php as the variable $GLOBALS['OER_PREFERRED_URI'] ",
     *         "index_server": 0 // or 1, 1 if this host is ready to be asked as an index-server, else 0.
     *     }
     * [/code]
     */
    public function fetch_public_host_key_action()
    {
        $host = OERHost::thisOne();
        if (Request::get("from")) {
            $this->refreshHost(Request::get("from"));
        }
        $this->render_json([
            'name' => Config::get()->UNI_NAME_CLEAN,
            'public_key' => $host['public_key'],
            'url' => $GLOBALS['OER_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/oer/endpoints/",
            'index_server' => $host['index_server']
        ]);
    }

    /**
     * Called by a remote-server to update its server-information via post-request.
     * Even the public key could be updated this way!
     */
    public function update_server_info_action()
    {
        if (!Request::isPost()) {
            throw new Exception("USE POST TO PUSH.");
        }

        $host = $this->getHostFromRequest();
        if ($host && !$host->isMe()) {
            $data = $this->extractDataForHost($host);

            $host['name'] = $data['data']['name'];
            $host['index_server'] = $data['data']['index_server'];
            $host['public_key'] = $data['data']['public_key'];
            $host['url'] = $data['data']['url'];
            $host['last_updated'] = time();
            $host->store();

            $this->render_text('stored');
        } else {
            $this->render_text('');
        }
    }


    /**
     * Returns a json with all known hosts.
     * If there is a "from" GET-parameter, this host will
     * fetch the public key of the from-host and saves it to its database.
     */
    public function fetch_known_hosts_action()
    {
        $output = [];

        if (Request::get("from")) {
            $this->refreshHost(Request::get("from"));
        }

        foreach (OERHost::findAll() as $host) {
            if (!$host->isMe() && $host['active']) {
                $output['hosts'][] = [
                    'name' => $host['name'],
                    'url' => $host['url']
                ];
            }
        }

        $this->render_json($output);
    }

    protected function refreshHost($url)
    {
        $host_data = file_get_contents($url."fetch_public_host_key");
        if ($host_data) {
            $host_data = json_decode($host_data, true);
            if ($host_data) {
                $host = OERHost::findOneByUrl($url);
                if (!$host) {
                    $host = OERHost::findOneByPublic_key($host_data['public_key']);
                }
                if (!$host) {
                    $host = new OERHost();
                }
                $host['name'] = $host_data['name'];
                $host['url'] = Request::get("from");
                $host['public_key'] = $host_data['public_key'];
                $host['last_updated'] = time();
                if ($host->isNew()) {
                    $host['active'] = Config::get()->LERNMARKTPLATZ_ACTIVATE_NEW_HOSTS ? 1 : 0;
                }
                $host->store();
            }
        }
    }

    public function search_items_action()
    {
        $host = OERHost::thisOne();
        if (Request::get("text")) {
            $this->materialien = OERMaterial::findByText(Request::get("text"));
        } elseif (Request::get("tag")) {
            $this->materialien = OERMaterial::findByTag(Request::get("tag"));
        }

        $output = ['results' => []];
        foreach ($this->materialien as $material) {
            $data = [];
            $data['host'] = [
                'name' => $material->host ? $material->host['name'] : $host['name'],
                'url' => $material->host ? $material->host['url'] : $host['url'],
                'public_key' => $material->host ? $material->host['public_key'] : $host['public_key']
            ];
            $data['data'] = $material->toArray();
            unset($data['data']['material_id']);
            $data['users'] = [];
            foreach ($material->users as $userdata) {
                $user = $userdata['external_contact']
                    ? ExternalUser::find($userdata['user_id'])
                    : User::find($userdata['user_id']);
                $data['users'][] = [
                    'user_id' => $userdata['external_contact']
                        ? $user->foreign_id
                        : $userdata['user_id'],
                    'name' => $userdata['external_contact']
                        ? $user['name']
                        : get_fullname($userdata['user_id']),
                    'avatar' => $userdata['external_contact']
                        ? $user->avatar_url
                        : Avatar::getAvatar($userdata['user_id'])->getURL(Avatar::NORMAL),
                    'host_url' => $material->host ? $material->host['url'] : $host['url']
                ];
            }
            $data['topics'] = [];
            foreach ($material->getTopics() as $topic) {
                $data['topics'][] = $topic['name'];
            }
            $output['results'][] = $data;
        }
        $this->render_json($output);
    }

    /**
     * Returns data of a given item including where to download it and the structure, decription, etc.
     * If item is not hosted on this server, just relocate the request to the real server.
     *
     * This endpoint should be called by a remote whenever a client wants to view the details of an item.
     *
     * @param $item_id : ID of the item on this server.
     */
    public function get_item_data_action($item_id)
    {
        $material = new OERMaterial($item_id);
        if ($material->isNew()) {
            $this->render_json([
                'deleted' => 1
            ]);
        } elseif (!$material['foreign_material_id']) {
            $me = OERHost::thisOne();
            $topics = [];
            foreach ($material->getTopics() as $topic) {
                $topics[] = $topic['name'];
            }

            $reviews = [];
            foreach ($material->reviews as $review) {
                if ($review['metadata']['host_id']) {
                    $user = ExternalUser::find($review['user_id']);
                    $user = [
                        'user_id' => $review['user_id'],
                        'name' => $user['name'],
                        'avatar' => $user['avatar_url'],
                        'description' => $user['data']['description']
                    ];
                } else {

                    $user = [
                        'user_id' => $review['user_id'],
                        'name' =>get_fullname($review['user_id']),
                        'avatar' => Avatar::getAvatar($review['user_id'])->getURL(Avatar::NORMAL),
                        'description' => $review->user['oercampus_description']
                    ];
                }
                $reviews[] = [
                    'foreign_review_id' => $review['metadata']['foreign_review_id'] ?: $review->getId(),
                    'review' => $review['content'],
                    'rating' => $review['metadata']['rating'],
                    'user' => $user,
                    'host' => [
                        'name' => $review['metadata']['host_id'] ? $review->host['name'] : $me['name'],
                        'url' => $review['metadata']['host_id'] ? $review->host['url'] : $me['url'],
                        'public_key' => $review['metadata']['host_id'] ? $review->host['public_key'] : $me['public_key']
                    ],
                    'mkdate' => $review['mkdate'],
                    'chkdate' => $review['chdate']
                ];
            }
            $users = [];
            foreach ($material->users as $userdata) {
                $user = $userdata['external_contact']
                    ? ExternalUser::find($userdata['user_id'])
                    : User::find($userdata['user_id']);
                $users[] = [
                    'user_id' => $userdata['external_contact']
                        ? $user->foreign_id
                        : $userdata['user_id'],
                    'name' => $userdata['external_contact']
                        ? $user['name']
                        : get_fullname($userdata['user_id']),
                    'avatar' => $userdata['external_contact']
                        ? $user->avatar_url
                        : Avatar::getAvatar($userdata['user_id'])->getURL(Avatar::NORMAL),
                    'host_url' => $material->host ? $material->host['url'] : $me['url']
                ];
            }
            $this->render_json([
                    'data' => [
                    'name' => $material['name'],
                    'short_description' => $material['short_description'],
                    'description' => $material['description'],
                    'content_type' => $material['content_type'],
                    'front_image_content_type' => $material['front_image_content_type'],
                    'url' => ($GLOBALS['OER_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'])."dispatch.php/oer/market/download/".$item_id,
                    'player_url' => $material['player_url'],
                    'tool' => $material['tool'],
                    'structure' => ($material['structure'] ? $material['structure']->getArrayCopy() : null),
                    'license' => $material['license']
                ],
                'users' => $users,
                'topics' => $topics,
                'reviews' => $reviews
            ]);
        } else {
            $host = new OERHost($material['host_id']);
            header("Location: ".$host['url']."get_item_data/".$item_id);
            return;
        }
    }

    /**
     * Update data of an item via POST-request.
     */
    public function push_data_action()
    {
        if (!Request::isPost()) {
            throw new Exception("USE POST TO PUSH.");
        }

        $host = $this->getHostFromRequest();
        if ($host && !$host->isMe()) {
            $data = $this->extractDataForHost($host);

            $material = OERMaterial::findOneBySQL("host_id = ? AND foreign_material_id = ?", [
                $host->getId(),
                $data['data']['foreign_material_id']
            ]);
            if (!$material) {
                $material = new OERMaterial();
            }
            if ($data['delete_material']) {
                $material->delete();
                $this->render_text("deleted ");
            } else {
                $material->setData($data['data']);
                $material['host_id'] = $host->getId();
                $material->store();
                $material->setTopics($data['topics']);
                $material->setUsers($data['users']);
                $this->render_text("stored ");
            }
        } else {
            $this->render_text('');
        }
    }

    /**
     * Download an item from this server. The ##material_id## of the item must be given.
     * @param $material_id : material_id from this server or foreign_material_id from another server.
     */
    public function download_action($material_id, $disposition = "inline")
    {
        $this->material = new OERMaterial($material_id);
        if ($this->material['draft']) {
            throw new AccessDeniedException();
        }

        while (ob_get_level()) {
            ob_end_clean();
        }
        page_close();

        $filesize = filesize($this->material->getFilePath());
        header("Accept-Ranges: bytes");
        $start = 0;
        $end = $filesize - 1;
        $length = $filesize;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;
            [, $range] = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (mb_strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$filesize");
                exit;
            }
            if ($range[0] == '-') {
                $c_start = $filesize - mb_substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $filesize;
            }
            $c_end = ($c_end > $end) ? $end : $c_end;
            if ($c_start > $c_end || $c_start > $filesize - 1 || $c_end >= $filesize) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$filesize");
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$filesize");
        }

        header("Content-Length: $length");

        header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
        header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
        if ($_SERVER['HTTPS'] == "on") {
            header("Pragma: public");
            header("Cache-Control: private");
        } else {
            header("Pragma: no-cache");
            header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
        }
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Content-Type: ".$this->material['content_type']);
        header("Content-Disposition: " . ($disposition ?: "inline") . "; " . encode_header_parameter('filename', $this->material['filename']));

        readfile_chunked($this->material->getFilePath(), $start, $end);

        if (!$start) {
            OERDownloadcounter::addCounter($material_id);
        }

        die();
    }

    /**
     * Download image of this item from this server. The ##material_id## of the item must be given.
     * @param $material_id : material_id from this server or foreign_material_id from another server.
     */
    public function download_front_image_action($material_id)
    {
        $this->material = new OERMaterial($material_id);
        $this->set_content_type($this->material['front_image_content_type']);
        $this->response->add_header('Content-Disposition', 'inline');
        $this->response->add_header('Content-Length', filesize($this->material->getFrontImageFilePath()));
        $this->render_text(file_get_contents($this->material->getFrontImageFilePath()));
    }

    /**
     * Adds or edits a review to the material on this server from a client of another server.
     * Use this request only as a POST request, the body must be a JSON-object that carries all the
     * necessary variables.
     * @param $material_id : ID of the item on this server.
     */
    public function add_review_action($material_id)
    {
        if (!Request::isPost()) {
            throw new Exception("USE POST TO PUSH.");
        }

        $host = $this->getHostFromRequest();
        if ($host && !$host->isMe()) {
            $data = $this->extractDataForHost($host);

            $material = new OERMaterial($material_id);
            if ($material->isNew() || $material['host_id']) {
                throw new Exception("Unknown material.");
            }

            $user = ExternalUser::findOneBySQL("host_id = ? AND foreign_id = ?", [
                $host->getId(),
                $data['user']['user_id']
            ]);
            if (!$user) {
                $user = new ExternalUser();
                $user['host_id'] = $host->getId();
                $user['foreign_id'] = $data['user']['user_id'];
            }
            $user['contact_type'] = 'oercampus';
            $user['name'] = $data['user']['name'];
            $user['avatar_url'] = $data['user']['avatar'];
            $user['data']['description'] = $data['user']['description'] ?: "";
            $user->store();

            $review = OERReview::findOneBySQL("display_class = 'OERReview'
                    AND external_contact = '1'
                    AND context_id = :material_id
                    AND user_id = :user_id
                    AND metadata LIKE :host_id", [
                'material_id' => $material_id,
                'user_id' => $user->getId(),
                'host_id' => "%".$host->getId()."%"
            ]);

            if (!$review) {
                $review = new OERReview();
                $review['user_id'] = $user->getId();
                $review['display_class'] = "OERReview";
                $review['context_id'] = $material_id;
            }
            $review['content'] = $data['data']['review'];
            $review['metadata'] = [
                'host_id' => $host->getId(),
                'foreign_review_id' => $data['data']['foreign_review_id'],
                'rating' => $data['data']['rating']
            ];
            $review['mkdate'] = $data['data']['mkdate'];
            $review['chdate'] = $data['data']['chdate'];
            $review->store();

            $this->render_text("stored ");
        } else {
            $this->render_text('');
        }
    }

    /**
     * Adds or edits a comment to the material on this server from a client of another server.
     * Use this request only as a POST request, the body must be a JSON-object that carries all the
     * necessary variables.
     * The review_id is the foreign_review_id if the host_hash is not empty or the review_id if the host_hash is empty.
     * @param $material_id : ID of the item on this server.
     */
    public function add_comment_action($review_id, $host_hash = null)
    {
        if (!Request::isPost()) {
            throw new Exception("USE POST TO PUSH.");
        }

        $host = $this->getHostFromRequest();
        if ($host && !$host->isMe()) {
            if ($host_hash) {
                $review = OERReview::findOneBySQL("
                        display_class = 'OERREview'
                        AND context_type = 'public'
                        AND metadata LIKE :foreign_review_id
                        AND metadata LIKE :host_id
                    ", [
                    'foreign_review_id' => "%".$review_id."%",
                    'host_id' => "%".$host->getId()."%"
                ]);
            } else {
                $review = OERReview::find($review_id);
            }
            if (!$review) {
                throw new Exception("Unknown material.");
            }

            $data = $this->extractDataForHost($host);

            $user = ExternalUser::findOneBySQL("host_id = ? AND foreign_id = ?", [
                $host->getId(),
                $data['user']['user_id']
            ]);
            if (!$user) {
                $user = new ExternalUser();
                $user['host_id'] = $host->getId();
                $user['foreign_id'] = $data['user']['user_id'];
            }
            $user['contact_type'] = 'oercampus';
            $user['name'] = $data['user']['name'];
            $user['avatar_url'] = $data['user']['avatar'];
            $user['data']['description'] = $data['user']['description'] ?: "";
            $user->store();

            $comment = new BlubberComment();
            $comment['user_id'] = $user->getId();
            $comment['external_contact'] = "1";
            $comment['thread_id'] = $review->getId();
            $comment['content'] = $data['data']['comment'];
            $comment['mkdate'] = $data['data']['mkdate'];
            $comment['chdate'] = $data['data']['chdate'];
            $comment->store();

            $this->render_text("stored ");
        } else {
            $this->render_text('');
        }
    }

    private function getHostFromRequest()
    {
        $public_key_hash = $this->getHttpHeader(OERHost::OER_HEADER_PUBLIC_KEY_HASH); //MD5_HASH_OF_RSA_PUBLIC_KEY
        return OERHost::findOneBySQL("MD5(public_key) = ?", [$public_key_hash]);
    }

    private function extractDataForHost(OERHost $host)
    {
        $encoded_signature = $this->getHttpHeader(OERHost::OER_HEADER_SIGNATURE); //BASE64_RSA_SIGNATURE
        $signature = base64_decode($encoded_signature);

        $body = file_get_contents('php://input');
        if (!$host->verifySignature($body, $signature)) {
            throw new Exception('Wrong signature, sorry.');
        }

        return json_decode($body, true);
    }

    private function getHttpHeader($name)
    {
        $header_name = 'HTTP_' . str_replace('-', '_', mb_strtoupper($name));
        return $_SERVER[$header_name];
    }

}
