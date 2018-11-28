<?php
/*
 * Scanner.php - template parser lexical scanner
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace exTpl;

/**
 * Simple wrapper class around the Zend engine's lexical scanner. It
 * automatically skips whitespace and offers an interator interface.
 */
class Scanner
{
    private $tokens;
    private $token_type;
    private $token_value;

    /**
     * Initializes a new Scanner instance for the given text.
     *
     * @param string $text      string to parse
     */
    public function __construct($text)
    {
        $this->tokens = token_get_all('<?php ' . $text);
    }

    /**
     * Advances the scanner to the next token and returns its token type.
     * The valid token types are those defined for token_get_all() in the
     * PHP documentation. Returns false when the end of input is reached.
     */
    public function nextToken()
    {
        do {
            $token = next($this->tokens);
            $key = key($this->tokens);

            while ($token[0] === T_STRING &&
                   $this->tokens[++$key] === '-' &&
                   $this->tokens[++$key][0] === T_STRING) {
                $token[1] .= '-' . $this->tokens[$key][1];
                next($this->tokens);
                next($this->tokens);
            }
        } while (is_array($token) && $token[0] === T_WHITESPACE);

        if (is_string($token) || $token === false) {
            $this->token_type = $token;
            $this->token_value = NULL;
        } else {
            $this->token_type = $token[0];

            switch ($token[0]) {
                case T_CONSTANT_ENCAPSED_STRING:
                    $this->token_value = stripcslashes(substr($token[1], 1, -1));
                    break;
                case T_DNUMBER:
                    $this->token_value = (double) $token[1];
                    break;
                case T_LNUMBER:
                    $this->token_value = (int) $token[1];
                    break;
                default:
                    $this->token_value = $token[1];
            }
        }

        return $this->token_type;
    }

    /**
     * Returns the current token type. The valid token types are
     * those defined for token_get_all() in the PHP documentation.
     */
    public function tokenType()
    {
        return $this->token_type;
    }

    /**
     * Returns the current token value if the token type supports
     * a value (T_STRING, T_LNUMBER etc.). Returns NULL otherwise.
     */
    public function tokenValue()
    {
        return $this->token_value;
    }
}
