<?php

class Oer_MarketController extends StudipController
{

    protected $with_session = true;

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Helpbar::Get()->addPlainText(
            _("Lernmaterialien"),
            _("Übungszettel, Musterlösungen, Vorlesungsmitschriften oder gar Folien, selbsterstellte Lernkarteikarten oder alte Klausuren. Das alles wird einmal erstellt und dann meist vergessen. Auf dem Lernmaterialienmarktplatz bleiben sie erhalten. Jeder kann was hochladen und jeder kann alles herunterladen. Alle Inhalte hier sind frei verfügbar für jeden.")
        );
        PageLayout::setTitle(_("Lernmaterialien"));
    }

    public function index_action()
    {
        if (!$GLOBALS['perm']->have_perm(Config::get()->OER_PUBLIC_STATUS)) {
            throw new AccessDeniedException();
        }
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }
        $tag_matrix_entries_number = 9;
        $this->best_nine_tags = OERTag::findBest($tag_matrix_entries_number);

        if (Request::get("tag")) {
            $this->materialien = OERMaterial::findByTag(Request::get("tag"));
        }
        if (Request::get("category")) {
            $this->materialien = OERMaterial::findBySQL("category = ? ORDER BY oer_material.mkdate DESC", [Request::get("category")]);
        }
        if (Request::get("get") === "all") {
            $this->materialien = OERMaterial::findBySQL("1 ORDER BY oer_material.mkdate DESC");
        }
        $this->new_ones = OERMaterial::findBySQL("LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
            WHERE draft = '0'
                AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
            ORDER BY mkdate DESC LIMIT 9");

        $statement = DBManager::get()->prepare("
            SELECT 1
            FROM oer_abo
            WHERE user_id = ?
                AND material_id IS NULL
        ");
        $statement->execute([$GLOBALS['user']->id]);
        $this->abo = (bool) $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function get_tags_action()
    {
        $tag_matrix_entries_number = 9;
        $tag_subtags_number = 9;

        if (!count(Request::getArray("tags"))) {
            $this->topics = OERTag::findBest($tag_matrix_entries_number);
            $this->materialien = [];
        } else {
            $tags = $this->tag_history = Request::getArray("tags");
            $this->without_tags = [];
            $tag_to_search_for = array_pop($tags);
            foreach (OERTag::findBest($tag_matrix_entries_number, true) as $related_tag) {
                if ($related_tag['tag_hash'] !== $this->tag_history[0]) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            foreach ($tags as $tag) {
                foreach (OERTag::findRelated($tag, $this->without_tags, $tag_subtags_number, true) as $related_tag) {
                    $this->without_tags[] = $related_tag['tag_hash'];
                }
            }
            $this->topics = OERTag::findRelated(
                $tag_to_search_for,
                $this->without_tags,
                $tag_subtags_number
            );
            $this->materialien = OERMaterial::findByTagHash($tag_to_search_for);
        }

        $output = [];
        $output['results'] = [
            'materials' => [],
            'tags' => []
        ];
        foreach ($this->materialien as $material) {
            $data = $material->toRawArray();

            $data['tags'] = array_map(function($tag) {
                return $tag['name'];
            }, $material->getTopics());

            $data['logo_url'] = $material->getLogoURL();
            $data['download_url'] = $material->getDownloadUrl();

            $output['results']['materials'][] = $data;
        }

        foreach ($this->topics as $topic) {
            $output['tags'][] = $topic->toArray();
        }

        $this->render_json($output);
    }


    public function search_action()
    {
        if (!$GLOBALS['perm']->have_perm(Config::get()->OER_PUBLIC_STATUS)) {
            throw new AccessDeniedException();
        }
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }
        if (Request::get("search") || Request::get("type") || Request::get("tag") || Request::get("difficulty")) {
            if (Request::get("search")) {
                OERMaterial::fetchRemoteSearch(
                    Request::get("search"),
                    Request::get("tag")
                );
            }
            $this->more = false;
            $search = SQLQuery::table("oer_material", "oer_material")
                ->join("oer_hosts", "oer_hosts.host_id = oer_material.host_id", "LEFT JOIN")
                ->where("draft = '0'")
                ->where("(oer_material.host_id IS NULL OR oer_hosts.`active` = '1')")
                ->groupBy("oer_material.material_id")
                ->orderBy("mkdate DESC");
            if (Request::get("type")) {
                $search->where("search_categories", "category = :category", ['category' => Request::get("type")]);
            }
            if (Request::get("search")) {
                //Tags
                $search->join(
                    "oer_tags_material",
                    "oer_material.material_id = oer_tags_material.material_id",
                    "LEFT JOIN"
                );
                $search->join(
                    "oer_tags",
                    "oer_tags_material.tag_hash = oer_tags.tag_hash",
                    "LEFT JOIN"
                );
                $search->where(
                    "textsearch",
                    "(oer_material.name LIKE :search OR oer_material.description LIKE :search OR oer_material.short_description LIKE :search OR oer_tags.name LIKE :search)",
                    ['search' => '%'.Request::get("search").'%']
                );
            }
            if (Request::get("difficulty")) {
                $difficulty = explode(",", Request::get("difficulty"));
                $search->where(
                    "difficulty",
                    "((difficulty_start <= :difficulty_start AND difficulty_end >= :difficulty_start) OR (difficulty_start <= :difficulty_end AND difficulty_end >= :difficulty_end) OR (difficulty_start <= :difficulty_start AND difficulty_end >= :difficulty_end) OR (difficulty_start >= :difficulty_start AND difficulty_end <= :difficulty_end))",
                    ['difficulty_start' => $difficulty[0], 'difficulty_end' => $difficulty[1]]
                );
            }
            if (Request::get("limit") || Request::get("offset")) {
                $search->limit(
                    Request::int("limit") + 1,
                    Request::int("offset")
                );
            }

            $this->materialien = $search->fetchAll("OERMaterial");
            if (Request::int("limit") && (count($this->materialien) > Request::int("limit"))) {
                $this->more = true;
                array_pop($this->materialien);
            }

            if (Request::isAjax()) {
                $output = [
                    'materials' => [],
                    'more' => $this->more,
                    'offset' => Request::int("offset", 0)
                ];
                if (Request::int("limit")) {
                    $output['limit'] = Request::int("limit");
                }
                foreach ($this->materialien as $material) {
                    $data = $material->toRawArray();

                    $data['tags'] = array_map(function($tag) {
                        return $tag['name'];
                    }, $material->getTopics());

                    $data['logo_url'] = $material->getLogoURL();
                    $data['download_url'] = $material->getDownloadUrl();

                    $output['materials'][] = $data;
                }
                $this->render_json($output);
            }
        } else {
            $this->redirect("oer/market/index");
        }
    }

    public function details_action($material_id)
    {
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }
        $this->material = new OERMaterial($material_id);

        //OpenGraph tags:
        PageLayout::addHeadElement("meta", ['og:title' => $this->material['name']]);

        $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        PageLayout::addHeadElement("meta", ['og:url' => $this->url_for("oer/market/details/".$this->material->getId())]);
        PageLayout::addHeadElement("meta", ['og:description' => $this->material['short_description']]);
        PageLayout::addHeadElement("meta", ['og:image' => $this->material->getLogoURL()]);
        if ($this->material->isVideo()) {
            PageLayout::addHeadElement("meta", ['og:type' => "video"]);
            $url = $this->material['host_id']
                ? $this->material->host->url."download/".$this->material['foreign_material_id']
                : $this->url_for("oer/market/download/".$this->material->getId());
            PageLayout::addHeadElement("meta", ['og:video' => $url]);
            PageLayout::addHeadElement("meta", ['og:video:type' => $this->material['content_type']]);
        } elseif($this->material->isAudio()) {
            PageLayout::addHeadElement("meta", ['og:type' => "audio"]);
            $url = $this->material['host_id']
                ? $this->material->host->url."download/".$this->material['foreign_material_id']
                : $this->url_for("oer/market/download/".$this->material->getId());
            PageLayout::addHeadElement("meta", ['og:audio' => $url]);
            PageLayout::addHeadElement("meta", ['og:audio:type' => $this->material['content_type']]);
        } else {
            PageLayout::addHeadElement("meta", ['og:type' => "article"]);
        }
        URLHelper::setBaseURL($base);

        if ($this->material['host_id']) {
            $success = $this->material->fetchData();
            if ($success === false) {
                PageLayout::postInfo(_("Dieses Material stammt von einem anderen Server, der zur Zeit nicht erreichbar ist."));
            } elseif ($success === "deleted") {
                $material = clone $this->material;
                $this->material->delete();
                $this->material = $material;
                PageLayout::postError(_("Dieses Material ist gelöscht worden und wird gleich aus dem Cache verschwinden."));
            }
            $this->material->resetRelation("users");
        }
        $this->material['rating'] = $this->material->calculateRating();
        $this->material->store();
    }

    public function embed_action($material_id)
    {
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }

        $this->material = new OERMaterial($material_id);
    }

    public function review_action($material_id = null)
    {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException();
        }
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }
        $this->material = new OERMaterial($material_id);
        $this->review = OERReview::findOneBySQL("context_id = ? AND user_id = ?", [
            $material_id,
            $GLOBALS['user']->id
        ]);
        if (!$this->review) {
            $this->review = new OERReview();
            $this->review['context_id'] = $this->material->getId();
            $this->review['user_id'] = $GLOBALS['user']->id;
            $this->review['display_class'] = "OERReview";
            $this->review['context_type'] = "public";
        }
        if (Request::isPost()) {
            $this->review['content'] = Request::get("review");
            $this->review['metadata'] = [
                'rating' => Request::get("rating")
            ];
            $this->review->store();

            $this->material['rating'] = $this->material->calculateRating();
            $this->material->store();
            PageLayout::postSuccess(_("Danke für das Review!"));
            $this->redirect("oer/market/details/".$material_id);
        }
    }

    public function discussion_action($review_id)
    {
        if (Navigation::hasItem("/oer/market")) {
            Navigation::activateItem("/oer/market");
        }

        $this->thread = new OERReview($review_id);
        if (!$this->thread->isNew()) {
            $this->thread->markAsRead();
        }
    }


    public function licenseinfo_action()
    {

    }

    public function add_to_course_action($material_id)
    {
        if (!$GLOBALS['perm']->have_perm("autor")) {
            throw new AccessDeniedException();
        }
        $this->material = new OERMaterial($material_id);
        if (Request::option("seminar_id") && $GLOBALS['perm']->have_studip_perm("autor", Request::option("seminar_id"))) {
            $this->course = new Course(Request::option("seminar_id"));

            $this->classes = ["CoreDocuments"];
            foreach (get_declared_classes() as $class) {
                if (in_array('OERModule', class_implements($class))) {
                    //check if module is even allowed in course
                    $semclass = $this->course->getSemClass();

                    if ($semclass->isModuleAllowed($class)
                            && $class::oerModuleWantsToUseMaterial($this->material)) {
                        $this->classes[] = $class;
                    }
                }
            }

            if (Request::get("class") || count($this->classes) === 1) {
                $class = Request::get("class") ?: $this->classes[0];
                if (class_exists($class) && in_array('OERModule', class_implements($class))) {
                    $semclass = $this->course->getSemClass();
                    if ($semclass->isModuleAllowed($class)) {
                        //activate module in course ?
                        $newfile = $class::oerModuleIntegrateMaterialToCourse(
                            $this->material,
                            $this->course
                        );
                        if (is_array($newfile)) {
                            PageLayout::postError(_("Beim Kopieren ist ein Fehler aufgetaucht."), $newfile);
                        } else {
                            PageLayout::postSuccess(_("Das Lernmaterial wurde kopiert."));
                        }
                        $this->response->add_header("X-Location", URLHelper::getURL("dispatch.php/course/files", array('cid' => $this->course->id)));
                        $this->response->add_header("X-Dialog-Close", 1);
                        $this->redirect(URLHelper::getURL("dispatch.php/course/files", array('cid' => $this->course->id)));
                        return;
                    }
                }
            } else {
                $this->render_template("oer/market/add_to_course_select_class");
            }

        }
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $this->courses = Course::findBySQL("INNER JOIN seminar_user USING (Seminar_id)
                WHERE seminar_user.user_id = ?
                ORDER BY seminare.mkdate DESC", [$GLOBALS['user']->id]
            );
        } else {
            $this->courses = [];
            foreach (AdminCourseFilter::get()->getCourses(false) as $coursedata) {
                $this->courses[] = Course::buildExisting($coursedata);
            }
        }
        $this->semesters = [];
        foreach ($this->courses as $course) {
            foreach ($course->semesters as $semester) {
                $this->semesters[$semester->getId()] = $semester;
            }
        }
        usort($this->semesters, function ($a, $b) {
            return $a['beginn'] < $b['beginn'];
        });
    }

    public function profile_action($external_user_id)
    {
        $this->user = new ExternalUser($external_user_id);
        if ($this->user->isNew()) {
            throw new Exception(_("Nutzer ist nicht erfasst."));
        }
        $this->materials = OERMaterial::findBySQL("user_id = ?
                AND host_id IS NOT NULL
            ORDER BY mkdate DESC", [
            $external_user_id
        ]);
    }

    public function abo_action()
    {
        $statement = DBManager::get()->prepare("
            SELECT 1
            FROM oer_abo
            WHERE user_id = ?
                AND material_id IS NULL
        ");
        $statement->execute([$GLOBALS['user']->id]);
        $this->abo = (bool) $statement->fetch(PDO::FETCH_COLUMN, 0);
        if (Request::isPost()) {
            if (Request::get("abo")) {
                $statement = DBManager::get()->prepare("
                    INSERT IGNORE INTO oer_abo
                    SET user_id = ?,
                        material_id = NULL
                ");
                $statement->execute([$GLOBALS['user']->id]);
                PageLayout::postSuccess(_("Erfolgreich abonniert."));
            } else {
                $statement = DBManager::get()->prepare("
                    DELETE
                    FROM oer_abo
                    WHERE user_id = ?
                        AND (material_id IS NULL OR material_id = '')
                ");
                $statement->execute([$GLOBALS['user']->id]);
                PageLayout::postSuccess(_("Abgemeldet von Neuigkeiten."));
            }

            $this->redirect("oer/market/index");
        }
    }

    protected function getFolderStructure($folder)
    {
        $structure = [];
        foreach (scandir($folder) as $file) {
            if (!in_array($file, [".", ".."])) {
                $attributes = [
                    'is_folder' => is_dir($folder."/".$file) ? 1 : 0
                ];
                if (is_dir($folder."/".$file)) {
                    $attributes['structure'] = $this->getFolderStructure($folder."/".$file);
                } else {
                    $attributes['size'] = filesize($folder."/".$file);
                }
                $structure[$file] = $attributes;
            }
        }
        return $structure;
    }

}
