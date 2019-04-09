<?php

class RepairStatusgroupUserNumberation extends Migration {

    /**
     * short description of this migration
     */
    function description() {
        return 'Repairs the statusgroup numberation';
    }

    /**
     * perform this migration
     */
    function up() {
        
        //PURGE DATABASE
        DBManager::get()->query('DELETE FROM statusgruppe_user WHERE statusgruppe_id = ""');
        
        $sql = "SELECT DISTINCT statusgruppe_id FROM statusgruppe_user";
        $user_sql = "SELECT user_id, position FROM statusgruppe_user WHERE statusgruppe_id = ? ORDER BY position ASC";
        $update_sql = "UPDATE statusgruppe_user SET position = ? WHERE statusgruppe_id = ? AND user_id = ?";
        $userStmt = DBManager::get()->prepare($user_sql);
        $result = DBManager::get()->query($sql);
        $update = DBManager::get()->prepare($update_sql);
        while ($group = $result->fetch(PDO::FETCH_COLUMN)) {
            $userStmt->execute([$group]);
            $realPosition = 0;
            while ($user = $userStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($user['position'] != $realPosition++) {
                    $update->execute([$realPosition - 1, $group, $user['user_id']]);
                }
            }
        }
    }

    /**
     * revert this migration
     */
    function down() {
        // we could randomly assign new position ids here to screw things up like
        // it was before the migration
    }

}
?>