<?php
/*
 * Context.php - template parser symbol table
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
 * A Context object represents the symbol table used to resolve
 * symbol names to their values in the local scope. Each context
 * may inherit symbol definitions from its parent context.
 */
class Context
{
    private $bindings;
    private $parent;

    /**
     * Initializes a new Context instance with the given bindings.
     *
     * @param array $bindings   symbol table
     * @param Context $parent   parent context (or NULL)
     */
    public function __construct($bindings, Context $parent = NULL)
    {
        $this->bindings = $bindings;
        $this->parent = $parent;
    }

    /**
     * Looks up the value of a symbol in this context and returns it.
     * The reserved symbol "this" is an alias for the current context.
     *
     * @param string $key       symbol name
     */
    public function lookup($key)
    {
        if (isset($this->bindings[$key])) {
            return $this->bindings[$key];
        } else if ($this->parent) {
            return $this->parent->lookup($key);
        }

        return NULL;
    }
}
