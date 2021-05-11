<?php

class Oer_AdminController extends AuthenticatedController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/locations/oer");
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
    }

    public function hosts_action()
    {
        //init
        OERHost::thisOne();
        $this->hosts = OERHost::findAll();
        foreach ($this->hosts as $host) {
            if (strpos($host['public_key'], "\r") !== false) {
                $host['public_key'] = str_replace("\r", "", $host['public_key']);
                $host->store();
            }
        }

        $this->federation_problems = \Studip\ENV !== "production";
        if (!function_exists("curl_init")) {
            $this->federation_problems = true;
            PageLayout::postError(_('Ihr PHP hat kein aktiviertes cURL-Modul.'));
        }
        if (Config::get()->OER_PUBLIC_STATUS !== "nobody") {
            $this->federation_problems = true;
            PageLayout::postInfo(_('Der OER Campus ist nicht für nobody freigegeben. Damit kann er sich nicht mit anderen Stud.IPs verbinden. Um das zu ändern, setzen Sie die Konfiguration OER_PUBLIC_STATUS auf "nobody".'));
        }

        //zufällig einen Host nach Neuigkeiten fragen:
        if (count($this->hosts) > 1) {
            $index = rand(0, count($this->hosts) - 1);
            while($this->hosts[$index]->isMe()) {
                $index++;
                if ($index >= count($this->hosts)) {
                    $index = 0;
                }
            }
            $this->askForHosts($this->hosts[$index]);
        }


    }

    public function add_new_host_action()
    {
        PageLayout::setTitle(_("Neue Lernmaterialien einstellen"));
        if (Request::submitted("nothanx")) {
            $_SESSION['Lernmarktplatz_no_thanx'] = true;
            $this->redirect("oer/admin/hosts");
        } elseif (Request::isPost()) {
            $host = OERHost::findOneByUrl(trim(Request::get("url")));
            if (!$host) {
                $host = new OERHost();
                $host['url'] = trim(Request::get("url"));
                $host['last_updated'] = time();
                $host->fetchPublicKey();
                if ($host['public_key']) {
                    $host->store();
                    PageLayout::postSuccess(_("Server wurde gefunden und hinzugefügt."));
                } else {
                    PageLayout::postError(_("Server ist nicht erreichbar oder hat die Anfrage abgelehnt."));
                }
            } else {
                $host->fetchPublicKey();
                PageLayout::postInfo(_("Server ist schon in Liste."));
            }

            $this->redirect("oer/admin/hosts");
        }
    }

    public function ask_for_hosts_action($host_id)
    {
        $host = new OERHost($host_id);
        $added = $this->askForHosts($host);
        if ($added > 0) {
            PageLayout::postSuccess(sprintf(_("%s neue Server hinzugefügt."), $added));
        } else {
            PageLayout::postInfo(_("Keine neuen Server gefunden."));
        }
        $this->redirect("oer/admin/hosts");
    }

    protected function askForHosts(OERHost $host)
    {
        $data = $host->askKnownHosts();
        $added = 0;
        if ($data['hosts']) {
            foreach ($data['hosts'] as $host_data) {

                $host = OERHost::findOneByUrl($host_data['url']);
                if (!$host) {
                    $host = new OERHost();
                    $host['url'] = $host_data['url'];
                    $host->fetchPublicKey();
                    if ($host['public_key']) {
                        $added++;
                        $host->store();
                    }
                } else {
                    $host->fetchPublicKey();
                }
            }
        }
        return $added;
    }

    public function toggle_index_server_action()
    {
        if (Request::isPost()) {
            $host = new OERHost(Request::option("host_id"));
            if ($host->isMe()) {
                $host['index_server'] = Request::int("active", 0);
                $host->store();
                //distribute this info to adjacent server
                $data = [
                    'data' => [
                        'public_key' => $host['public_key'],
                        'url' => $host['url'],
                        'name' => $host['name'],
                        'index_server' => $host['index_server']
                    ]
                ];

                foreach (OERHost::findAll() as $remote) {
                    if (!$remote->isMe()) {
                        $remote->pushDataToEndpoint("update_server_info", $data);
                    }
                }

            } else {
                $host['allowed_as_index_server'] = Request::int("active", 0);
                $host->store();
            }
        }

        $this->render_text((
            Icon::create("checkbox-".(Request::int("active") ? "" : "un")."checked")->asImg(20)
        ));
    }

    public function toggle_server_active_action()
    {
        if (Request::isPost()) {
            $host = new OERHost(Request::option("host_id"));
            if (!$host->isMe()) {
                $host['active'] = Request::int("active", 0);
                $host->store();
            }
        }

        $this->render_text((
            Icon::create("checkbox-".(Request::int("active") ? "" : "un")."checked")->asImg(20)
        ));
    }

    public function refresh_hosts_action()
    {
        foreach (OERHost::findAll() as $host) {
            if (!$host->isMe()) {
                $host->fetchPublicKey();
            }
            $host->store();
        }

        PageLayout::postSuccess(_("Daten der Server wurden abgerufen und aufgefrischt."));
        $this->redirect("oer/admin/hosts");
    }

}
