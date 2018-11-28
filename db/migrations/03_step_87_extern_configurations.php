<?php
require_once "lib/functions.php";
require_once $GLOBALS["RELATIVE_PATH_EXTERN"]."/extern_config.inc.php";
require_once $GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternConfig.class.php";
require_once $GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternConfigIni.class.php";
require_once $GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternConfigDb.class.php";


class Step87ExternConfigurations extends Migration
{

    public function description ()
    {
        return 'Extends table extern_config and converts configurations for the external pages from INI-style files to serialised arrays stored in the database.';
    }

    public function up ()
    {
        DBManager::get()->exec("ALTER TABLE `extern_config` ADD `config` MEDIUMTEXT NOT NULL AFTER `is_standard`");

        $configs = DBManager::get()->query("SELECT `range_id`, `config_id` FROM `extern_config`")->fetchAll(PDO::FETCH_ASSOC);

        $this->announce(" KONVERTIERUNG START ");

        $i = 0;
        foreach ($configs as $config) {
            $old_config = new ExternConfigIni($config['range_id'], '', $config['config_id']);
            $new_config = new ExternConfigDb($config['range_id'], '', $config['config_id']);

            $new_config->setConfiguration($old_config->getConfiguration());

            if ($new_config->store()) {
                $this->write(sprintf("Konfiguration mit der id %s konvertiert!", $new_config->getId()));
                $i++;
            } else {
                $this->write(sprintf("FEHLER! Die Konfiguration mit der id %s konnte nicht konvertiert werden!", $config['config_id']));
            }
        }

        if (count($configs) == $i) {
            $this->write("Alle Konfigurationsdateien vermutlich fehlerfrei in die Datenbank uebertragen!");
            $this->write(sprintf("Es wurden %s Konfigurationsdateien uebertragen.", $i));
        } else {
            $this->write("Es wurden nicht alle Konfigurationsdateien uebertragen!");
            $this->write(sprintf("Es wurden %s Konfigurationsdateien von %s Konfigurationsdateien uebertragen!", $i, count($configs)));
            $this->write("Bitte die fehlerhaften Konfigurationen manuell ueberpruefen.");
        }

        $this->announce(" KONVERTIERUNG ENDE ");

    }
}
