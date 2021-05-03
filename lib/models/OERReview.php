<?php

class OERReview extends BlubberThread
{
    public static function findByMaterial_id($material_id)
    {
        return self::findBySQL("
            context_type = 'public'
            AND context_id = :material_id
            AND display_class = 'OERReview'
        ", [
            'material_id' => $material_id
        ]);
    }

    protected static function configure($config = [])
    {
        $config['belongs_to']['material'] = [
            'class_name' => OERMaterial::class,
            'foreign_key' => 'context_id'
        ];
        $config['has_one']['host'] = [
            'class_name' => OERHost::class,
            'foreign_key' => function ($review) {
                return $review['metadata']['host_id'];
            },
            'assoc_func' => 'find'
        ];
        $config['registered_callbacks']['after_store'][] = 'afterStoreCallback';
        parent::configure($config);
    }

    public function afterStoreCallback()
    {
        if (!$this->material['host_id'] && !$this->material->isMine() && $this->isDirty()) {
            PersonalNotifications::add(
                $this->material->users->pluck("user_id"),
                URLHelper::getURL("dispatch.php/oer/market/details/".$this->material->getId()."#review_".$this->getId()),
                $this->isNew()
                    ? sprintf(_('%1$s hat ein Review zu "%2$s" geschrieben.'), $this['metadata']['host_id']
                        ? ExternalUser::find($this['user_id'])->name
                        : get_fullname($this['user_id']), $this->material['name'])
                    : sprintf(_('%1$s hat ein Review zu "%2$s" verändert.'), $this['metadata']['host_id']
                        ? ExternalUser::find($this['user_id'])->name
                        : get_fullname($this['user_id']), $this->material['name']),
                "review_".$this->getId(),
                Icon::create("support", "clickable")
            );
        }
        //only push if the comment is from this server and the material-server is different
        if ($this->material['host_id'] && !$this['metadata']['host_id'] && $this->isDirty()) {
            $remote = new OERHost($this->material['host_id']);
            $myHost = OERHost::thisOne();
            $data = [];
            $data['host'] = [
                'name' => $myHost['name'],
                'url' => $myHost['url'],
                'public_key' => $myHost['public_key']
            ];
            $data['data'] = [
                'foreign_review_id' => $this->getId(),
                'review' => $this['content'],
                'rating' => $this['metadata']['rating'],
                'chdate' => $this['chdate'],
                'mkdate' => $this['mkdate']
            ];

            $data['user'] = [
                'user_id' => $this['user_id'],
                'name' => get_fullname($this['user_id']),
                'avatar' => Avatar::getAvatar($this['user_id'])->getURL(Avatar::NORMAL),
                'description' => $this->user ? $this->user['oercampus_description'] : ""
            ];

            if (!$remote->isMe()) {
                $remote->pushDataToEndpoint("add_review/".$this->material['foreign_material_id'], $data);
            }
        }
    }

    public function notifyUsersForNewComment($comment)
    {
        parent::notifyUsersForNewComment($comment);
        //push comment to remote hosts
        //only push if the comment is from this server and the material-server is different
        if ($comment['external_contact'] == 0) {
            $myHost = OERHost::thisOne();
            $data = [];
            $data['host'] = [
                'name' => $myHost['name'],
                'url' => $myHost['url'],
                'public_key' => $myHost['public_key']
            ];
            $data['data'] = [
                'foreign_comment_id' => $comment->getId(),
                'comment' => $comment['content'],
                'chdate' => $comment['chdate'],
                'mkdate' => $comment['mkdate']
            ];
            $data['user'] = [
                'user_id' => $comment['user_id'],
                'name' => get_fullname($comment['user_id']),
                'avatar' => Avatar::getAvatar($comment['user_id'])->getURL(Avatar::NORMAL),
                'description' => $comment->user ? $comment->user['oercampus_description'] : ""
            ];

            $statement = DBManager::get()->prepare("
                SELECT external_users.host_id
                FROM blubber_comments
                    INNER JOIN external_users ON (external_users.external_contact_id = blubber_comments.user_id)
                WHERE blubber_comments.thread_id = :thread_id
                    AND blubber_comments.external_contact = '1'
                    AND external_users.host_id IS NOT NULL
                GROUP BY external_users.host_id
            ");
            $statement->execute([
                'thread_id' => $this->getId()
            ]);
            $hosts = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
            if ($this->metadata['host_id'] && !in_array($this->metadata['host_id'], $hosts)) {
                $hosts[] = $this->metadata['host_id'];
            }
            if ($this->material['host_id'] && !in_array($this->material['host_id'], $hosts)) {
                $hosts[] = $this->material['host_id'];
            }
            foreach ($hosts as $host_id) {
                $remote = new OERHost($host_id);
                if (!$remote->isMe()) {
                    $review_id = ($this['metadata']['foreign_review_id'] ?: $this->getId());
                    if ($this['metadata']['foreign_review_id']) {
                        if ($this['metadata']['host_id'] === $remote->getId()) {
                            $host_hash = null;
                        } else {
                            $host_hash = md5($this->host['public_key']);
                        }
                    } else {
                        $host_hash = md5($myHost['public_key']);
                    }
                    $remote->pushDataToEndpoint("add_comment/" . $review_id ."/".$host_hash, $data);
                }
            }
        }
    }

    public function getName()
    {
        return _("Diskussion zu einem OER-Review");
    }

    public function getContentTemplate()
    {
        $template = $GLOBALS['template_factory']->open('blubber/thread_content');
        $template->thread = $this;
        return $template;
    }

    public function getContextTemplate()
    {
        return null;
    }

    public function getURL()
    {
        return URLHelper::getURL('dispatch.php/oer/market/discussion/' . $this->getId());
    }

    public function isReadable(string $user_id = null)
    {
        return true;
    }

    public function isCommentable(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        return $GLOBALS['perm']->have_perm('autor', $user_id);
    }

    public function getJSONData($limit_comments = 50, $user_id = null, $search = null)
    {
        $data = parent::getJSONData($limit_comments, $user_id, $search);

        $data['thread_posting']['html'] .= "<div>";
        $rating = round($this['metadata']['rating'], 1);
        $v = $rating >= 0.75 ? "" : ($rating >= 0.25 ? "-halffull" : "-empty");
        $data['thread_posting']['html'] .= Icon::create("star$v", "info")->asImg(25);
        $v = $rating >= 1.75 ? "" : ($rating >= 1.25 ? "-halffull" : "-empty");
        $data['thread_posting']['html'] .= Icon::create("star$v", "info")->asImg(25);
        $v = $rating >= 2.75 ? "" : ($rating >= 2.25 ? "-halffull" : "-empty");
        $data['thread_posting']['html'] .= Icon::create("star$v", "info")->asImg(25);
        $v = $rating >= 3.75 ? "" : ($rating >= 3.25 ? "-halffull" : "-empty");
        $data['thread_posting']['html'] .= Icon::create("star$v", "info")->asImg(25);
        $v = $rating >= 4.75 ? "" : ($rating >= 4.25 ? "-halffull" : "-empty");
        $data['thread_posting']['html'] .= Icon::create("star$v", "info")->asImg(25);
        $data['thread_posting']['html'] .= "</div>";

        return $data;
    }
}
