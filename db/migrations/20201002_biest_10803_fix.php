<?php
/**
 * This migration will cleanup the table mvv_modul_deskriptor and alter the
 * table by adding a unique key on `modul_id`.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @see    https://develop.studip.de/trac/ticket/10803
 */
class Biest10803Fix extends Migration
{
    public function description()
    {
        return 'This migration will cleanup mvv_modul_deskriptor and add '
             . 'a unique key on column modul_id';
    }

    public function up()
    {
        // Select neccessary modul ids
        $query = "SELECT `modul_id`
                  FROM `mvv_modul_deskriptor`
                  GROUP BY `modul_id`
                  HAVING COUNT(*) > 1";
        $statement = DBManager::get()->query($query);
        $modul_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        // Prepare statement that reads all deskriptor info
        $query = "SELECT *
                  FROM `mvv_modul_deskriptor`
                  WHERE `modul_id` = :id
                  ORDER BY `chdate` ASC";
        $data_statement = DBManager::get()->prepare($query);

        // Prepare query that updates a module deskriptor
        $query = "UPDATE `mvv_modul_deskriptor`
                  SET `verantwortlich` = :verantwortlich,
                      `bezeichnung` = :bezeichnung,
                      `voraussetzung` = :voraussetzung,
                      `kompetenzziele` = :kompetenzziele,
                      `inhalte` = :inhalte,
                      `literatur` = :literatur,
                      `links` = :links,
                      `kommentar` = :kommentar,
                      `turnus` = :turnus,
                      `kommentar_kapazitaet` = :kommentar_kapazitaet,
                      `kommentar_sws` = :kommentar_sws,
                      `kommentar_wl_selbst` = :kommentar_wl_selbst,
                      `kommentar_wl_pruef` = :kommentar_wl_pruef,
                      `kommentar_note` = :kommentar_note,
                      `pruef_vorleistung` = :pruef_vorleistung,
                      `pruef_leistung` = :pruef_leistung,
                      `pruef_wiederholung` = :pruef_wiederholung,
                      `ersatztext` = :ersatztext
                  WHERE `deskriptor_id` = :id";
        $update_statement = DBManager::get()->prepare($query);

        // Prepare statements that removes all unneccessary deskriptors
        $query = "DELETE FROM `mvv_modul_deskriptor`
                  WHERE `deskriptor_id` IN (:ids)";
        $remove_statement = DBManager::get()->prepare($query);

        // For each module id, gather all info chronologically and combine
        // them. This way, hopefully no valid information will be lost.
        foreach ($modul_ids as $modul_id) {
            $data_statement->bindValue(':id', $modul_id);
            $data_statement->execute();
            $data_statement->setFetchMode(PDO::FETCH_ASSOC);

            $remove_desk_ids = [];

            $data = [];
            foreach ($data_statement as $row) {
                if (!$data) {
                    $data = $row;
                    continue;
                }

                foreach ($row as $key => $value) {
                    if (in_array($key, ['deskriptor_id', 'mkdate', 'chdate'])) {
                        continue;
                    }
                    if ($value || $row['author_id'] || $row['editor_id']) {
                        $data[$key] = $value;
                    }
                }

                $remove_desk_ids[] = $row['deskriptor_id'];
            }

            $update_statement->bindValue(':verantwortlich', $data['verantwortlich']);
            $update_statement->bindValue(':bezeichnung', $data['bezeichnung']);
            $update_statement->bindValue(':voraussetzung', $data['voraussetzung']);
            $update_statement->bindValue(':kompetenzziele', $data['kompetenzziele']);
            $update_statement->bindValue(':inhalte', $data['inhalte']);
            $update_statement->bindValue(':literatur', $data['literatur']);
            $update_statement->bindValue(':links', $data['links']);
            $update_statement->bindValue(':kommentar', $data['kommentar']);
            $update_statement->bindValue(':turnus', $data['turnus']);
            $update_statement->bindValue(':kommentar_kapazitaet', $data['kommentar_kapazitaet']);
            $update_statement->bindValue(':kommentar_sws', $data['kommentar_sws']);
            $update_statement->bindValue(':kommentar_wl_selbst', $data['kommentar_wl_selbst']);
            $update_statement->bindValue(':kommentar_wl_pruef', $data['kommentar_wl_pruef']);
            $update_statement->bindValue(':kommentar_note', $data['kommentar_note']);
            $update_statement->bindValue(':pruef_vorleistung', $data['pruef_vorleistung']);
            $update_statement->bindValue(':pruef_leistung', $data['pruef_leistung']);
            $update_statement->bindValue(':pruef_wiederholung', $data['pruef_wiederholung']);
            $update_statement->bindValue(':ersatztext', $data['ersatztext']);
            $update_statement->bindValue(':id', $data['deskriptor_id']);
            $update_statement->execute();

            if ($remove_desk_ids) {
                $remove_statement->bindValue(':ids', $remove_desk_ids);
                $remove_statement->execute();
            }
        }

        // Add unique key on column modul_id
        $query = "ALTER TABLE `mvv_modul_deskriptor`
                    DROP KEY `modul_id`,
                    ADD UNIQUE KEY `modul_id` (`modul_id`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // Drop unique key on column modul_id
        $query = "ALTER TABLE `mvv_modul_deskriptor`
                    DROP KEY `modul_id`,
                    ADD KEY `modul_id` (`modul_id`)";
        DBManager::get()->exec($query);
    }
}
