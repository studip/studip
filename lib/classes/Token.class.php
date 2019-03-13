<?php
/**
 * Token.class.php - Token class
 *
 * @author  Marco Diedrich <mdiedric@uos.de>
 * @license GPL2 or any later version
 * @copyright authors
 */
class Token
{
    private $user_id;
    private $token;
    private $expiration_time;

    function __construct($user_id, $duration_validity = 30)
    {
        $this->user_id = $user_id;
        $this->expiration_time = $this->calculate_expiration_time($duration_validity);
        $this->token = self::generate_token();
        $this->save();
    }

    public static function generate_token()
    {
        $query = "SELECT 1 FROM user_token WHERE token = ?";
        $statement = DBManager::get()->prepare($query);

        // Ensure token is unique
        do {
            $token = md5(uniqid(__CLASS__, true));

            $statement->execute([$token]);
            $exists = (bool) $statement->fetchColumn();
        } while ($exists);

        return $token;
    }

    public function get_token()
    {
        return $this->token;
    }

    public function get_string()
    {
        return $this->get_token();
    }

    public function __toString()
    {
        return $this->token;
    }

    public function save()
    {
        $query = "INSERT INTO user_token (user_id, token, expiration) VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);
        return $statement->execute(array($this->user_id, $this->token, $this->expiration_time));
    }

    public function calculate_expiration_time($duration_validity = 30)
    {
        return strtotime(sprintf("+ %u seconds", $duration_validity));
    }

    public static function generate($user_id, $duration_validity = 30)
    {
        return new Token($user_id, $duration_validity);
    }

    public static function remove_expired()
    {
        $query = "DELETE FROM user_token WHERE expiration < UNIX_TIMESTAMP()";
        DBManager::get()->exec($query);
    }

    public static function remove($token)
    {
        $query = "DELETE FROM user_token WHERE token = ?";
        $statement = DBManager::get()->prepare($query);
        return $statement->execute(array($token));
    }

    public static function time_expired($expiration)
    {
        return time() > $expiration;
    }

    public static function is_valid($token)
    {
        $query = "SELECT user_id, expiration FROM user_token WHERE token = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($token));
        $token_info = $statement->fetch(PDO::FETCH_ASSOC);

        // No db entry for token
        if (!$token_info) {
            return null;
        }

        // Token is expired
        if (self::time_expired($token_info['expiration'])) {
            self::remove_expired();
            return null;
        }

        // Token is valid
        self::remove($token);
        return $token_info['user_id'];
    }
}

# $token = new Token('38f32d5c0b1d16450408bb11c4089930', 1);
# var_dump($token);
# $token->save();
# var_dump($token->is_valid($token->get_string()));
