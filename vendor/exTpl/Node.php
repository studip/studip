<?php
/*
 * Node.php - template parser node interface and classes
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace exTpl;

require_once 'Context.php';
require_once 'Expression.php';

/**
 * Basic interface for nodes in the template parse tree. The only
 * required method is "render" to render a node and its children.
 */
interface Node
{
    /**
     * Returns a string representation of this node.
     *
     * @param Context $context  symbol table
     */
    public function render($context);
}

/**
 * TextNode represents a verbatim text segment.
 */
class TextNode implements Node
{
    protected $text;

    /**
     * Initializes a new Node instance with the given text.
     *
     * @param string $text      verbatim text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Returns a string representation of this node.
     *
     * @param Context $context  symbol table
     */
    public function render($context)
    {
        return $this->text;
    }
}

/**
 * ExpressionNode represents an expression tag: "{...}".
 */
class ExpressionNode implements Node
{
    protected $expr;

    /**
     * Initializes a new Node instance with the given expression.
     *
     * @param Expression $expr  expression object
     */
    public function __construct(Expression $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Returns a string representation of this node.
     *
     * @param Context $context  symbol table
     */
    public function render($context)
    {
        return $this->expr->value($context);
    }
}

/**
 * ArrayNode represents a sequence of arbitrary nodes.
 */
class ArrayNode implements Node
{
    protected $nodes = array();

    /**
     * Adds a child node to this sequence node.
     *
     * @param Node $node        child node to add
     */
    public function addChild(Node $node)
    {
        $this->nodes[] = $node;
    }

    /**
     * Returns a string representation of this node.
     *
     * @param Context $context  symbol table
     */
    public function render($context)
    {
        $result = '';

        foreach ($this->nodes as $node) {
            $result .= $node->render($context);
        }

        return $result;
    }
}

/**
 * IteratorNode represents a single iterator tag:
 * "{foreach ARRAY}...{endforeach}".
 */
class IteratorNode extends ArrayNode
{
    protected $expr;

    /**
     * Initializes a new Node instance with the given expression.
     *
     * @param Expression $expr  expression object
     */
    public function __construct(Expression $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Returns a string representation of this node. The IteratorNode
     * renders the node sequence for each value in the expression list.
     *
     * @param Context $context  symbol table
     */
    public function render($context)
    {
        $values = $this->expr->value($context);
        $result = '';

        if (is_array($values) && is_int(key($values))) {
            $bindings = array('index' => &$key, 'this' => &$value);
            $context = new Context($bindings, $context);

            foreach ($values as $key => $value) {
                $result .= parent::render(new Context($value, $context));
            }
        } else if (is_array($values) && count($values)) {
            return parent::render(new Context($values, $context));
        } else if ($values) {
            return parent::render($context);
        }

        return $result;
    }
}

/**
 * ConditionNode represents a single condition tag:
 * "{if CONDITION}...{else}...{endif}".
 */
class ConditionNode extends ArrayNode
{
    protected $condition;
    protected $else_node;

    /**
     * Initializes a new Node instance with the given expression.
     *
     * @param Expression $condition     expression object
     */
    public function __construct(Expression $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Adds an else block to this condition node.
     */
    public function addElse()
    {
        $this->else_node = new ArrayNode();
    }

    /**
     * Adds a child node to this condition node.
     *
     * @param Node $node        child node to add
     */
    public function addChild(Node $node)
    {
        if ($this->else_node) {
            $this->else_node->addChild($node);
        } else {
            parent::addChild($node);
        }
    }

    /**
     * Returns a string representation of this node.
     *
     * @param Context $context  symbol table
     */
    public function render($context)
    {
        if ($this->condition->value($context)) {
            return parent::render($context);
        }

        return $this->else_node ? $this->else_node->render($context) : '';
    }
}
