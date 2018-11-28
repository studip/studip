<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO

/*
 * Token.class.php - Token class
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
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
        return md5(uniqid(__CLASS__, true));
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
        return strtotime(sprintf("+ %u seconds", $duration_validity), time());
    }

    public static function generate($user_id, $duration_validity = 30)
    {
        return new Token($user_id, $duration_validity);
    }

    public static function remove_expired()
    {
        DBManager::get()->exec("DELETE FROM user_token WHERE expiration < UNIX_TIMESTAMP()");
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
        if (Token::time_expired($token_info['expiration'])) {
            Token::remove_expired();
            return null;
        }

        // Token is valid
        Token::remove($token);
        return $token_info['user_id'];
    }
}

# $token = new Token('38f32d5c0b1d16450408bb11c4089930', 1);
# var_dump($token);
# $token->save();
# var_dump($token->is_valid($token->get_string()));


