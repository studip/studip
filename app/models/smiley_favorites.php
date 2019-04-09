<?php
/**
 * SmileyFavorites
 * 
 * This model provides access to the favored smileys of an user.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @category    Stud.IP
 * @package     smiley
 * @since       2.3
 *
 * @uses        DBManager
 * @uses        Smiley
 */
class SmileyFavorites
{
    private $user_id;
    private $favorites = [];
    
    /**
     * Initializes an user's favorites
     *
     * @param String $user_id Id of the user
     */
    function __construct($user_id)
    {
        $this->user_id = $user_id;

        $query = "SELECT smiley_favorite FROM user_info WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->user_id]);
        $favorite_string = $statement->fetchColumn() ?: '';
        $this->favorites = explode(',', $favorite_string);
        $this->favorites = array_filter($this->favorites);
    }

    /**
     * Returns the user's favored smileys' ids.
     *
     * @return Array Ids of the smileys the user has vaored
     */
    function get()
    {
        return $this->favorites;
    }

    /**
     * Updates the user's favored smileys.
     *
     * @param Array $favorites Ids of the user's favored smileys
     */
    function set($favorites = [])
    {
        $favorite_string = implode(',', $favorites);
        if (mb_strlen($favorite_string) > 255) {
            throw new OutOfBoundsException;
        }

        DBManager::get()
            ->prepare("UPDATE user_info SET smiley_favorite = ? WHERE user_id = ?")
            ->execute([$favorite_string, $this->user_id]);

        $this->favorites = $favorites;
    }

    /**
     * Returns whether the smiley with the given id is favored by the user.
     *
     * @param  int  $smiley_id Id of the smiley
     * @return bool True if the smiley is favored by the user, false otherwise
     */
    function contain($smiley_id)
    {
        return in_array($smiley_id, $this->favorites);
    }

    /**
     * Toggles whether a smiley is favored by the user. You can either provide
     * an acutal state or omit the state to toggle the current state.
     *
     * @param  int   $smiley_id  Id of the smiley to favor/disfavor
     * @param  mixed $favorite   Either a boolean state or null to toggle current state
     * @return bool  True if the smiley is favored by the user, false otherwise
     */
    function toggle($smiley_id, $favorite = null)
    {
        if ($favorite === null) {
            $favorite = !$this->contain($smiley_id);
        }
        $favorites = $this->favorites;
        
        if ($favorite) {
            $favorites[] = $smiley_id;
        } else {
            $favorites = array_diff($favorites, [$smiley_id]);
        }
        $this->set($favorites);

        return $this->contain($smiley_id);
    }
    
    
    /**
     * Returns whether the ability to favor smiley is enabled.
     * 
     * @return bool
     */
    static function isEnabled()
    {
        $state = null;
        if ($state === null) {
            $state = DBManager::get()
                ->query("SHOW COLUMNS FROM user_info LIKE 'smiley_favorite%'")
                ->fetchColumn();
        }
        return $state;
    }

    /**
     * Returns a list of how often a smiley has been favored.
     *
     * @return Array Associative array with smiley name as key and according
     *               favored numbers as value
     */
    static function getUsage()
    {
        $usage = [];
        
        $query = "SELECT user_id, smiley_favorite FROM user_info WHERE smiley_favorite != ''";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([]);
        $temp = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        foreach ($temp as $user_id => $favorite_string) {
            $favorites = explode(',', $favorite_string);
            $smileys = Smiley::getByIds($favorites);
            foreach ($smileys as $smiley) {
                if (!isset($usage[$smiley->name])) {
                    $usage[$smiley->name] = 0;
                }
                $usage[$smiley->name] += 1;

            }
        }
        
        return $usage;
    }

    /**
     * Garbage collector. Removes all smiley ids from the users' favorites
     * that are no longer in the database.
     *
     * @return int Number of changed records
     */
    static function gc()
    {
        $smileys = Smiley::getGrouped('all', Smiley::FETCH_ID);
        
        $query = "SELECT user_id, smiley_favorite FROM user_info WHERE smiley_favorite != ''";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([]);
        $temp = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        $changed = 0;
        foreach ($temp as $user_id => $favorite_string) {
            $old_favorites = explode(',', $favorite_string);
            $new_favorites = array_intersect($smileys, $old_favorites);
            if (count($old_favorites) > count($new_favorites)) {
                $favorites = new self($user_id);
                $favorites->set($new_favorites);
                $changed += 1;
            }
        }
        
        return $changed;
    }
}
