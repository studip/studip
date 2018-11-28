<?php
/*
 * Expression.php - template parser expression interface and classes
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
 * Basic interface for expressions in the template parse tree. The
 * only required method is "value" to get the expression's value.
 */
interface Expression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context);
}

/**
 * ConstantExpression represents a literal value.
 */
class ConstantExpression implements Expression
{
    protected $value;

    /**
     * Initializes a new Expression instance.
     *
     * @param mixed $value      expression value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        return $this->value;
    }
}

/**
 * SymbolExpression represents a symbol (template variable).
 */
class SymbolExpression implements Expression
{
    protected $name;

    /**
     * Initializes a new Expression instance.
     *
     * @param string $name      symbol name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        return $context->lookup($this->name);
    }
}

/**
 * UnaryExpression represents a unary operator.
 */
abstract class UnaryExpression implements Expression
{
    protected $expr;

    /**
     * Initializes a new Expression instance.
     *
     * @param Expression $expr  expression object
     */
    public function __construct(Expression $expr)
    {
        $this->expr = $expr;
    }
}

/**
 * MinusExpression represents the unary minus operator ('-').
 */
class MinusExpression extends UnaryExpression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        return -$this->expr->value($context);
    }
}

/**
 * NotExpression represents the logical negation operator ('!').
 */
class NotExpression extends UnaryExpression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        return !$this->expr->value($context);
    }
}

/**
 * BinaryExpression represents a binary operator.
 */
abstract class BinaryExpression implements Expression
{
    protected $left, $right;
    protected $operator;

    /**
     * Initializes a new Expression instance.
     *
     * @param Expression $left  left operand
     * @param Expression $right right operand
     * @param mixed $operator   operator token
     */
    public function __construct(Expression $left, Expression $right, $operator)
    {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }
}

/**
 * ArithExpression represents an arithmetic operator.
 */
class ArithExpression extends BinaryExpression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        $left = $this->left->value($context);
        $right = $this->right->value($context);

        switch ($this->operator) {
            case '+': return $left + $right;
            case '-': return $left - $right;
            case '*': return $left * $right;
            case '/': return $left / $right;
            case '%': return $left % $right;
            case '~': return $left . $right;
        }
    }
}

/**
 * IndexExpression represents the array index operator.
 */
class IndexExpression extends BinaryExpression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        $left = $this->left->value($context);
        $right = $this->right->value($context);

        return $left[$right];
    }
}

/**
 * BooleanExpression represents a boolean operator.
 */
class BooleanExpression extends BinaryExpression
{
    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        $left = $this->left->value($context);
        $right = $this->right->value($context);

        switch ($this->operator) {
            case T_IS_EQUAL           : return $left == $right;
            case T_IS_NOT_EQUAL       : return $left != $right;
            case '<'                  : return $left <  $right;
            case T_IS_SMALLER_OR_EQUAL: return $left <= $right;
            case '>'                  : return $left >  $right;
            case T_IS_GREATER_OR_EQUAL: return $left >= $right;
            case T_BOOLEAN_AND        : return $left && $right;
            case T_BOOLEAN_OR         : return $left || $right;
        }
    }
}

/**
 * ConditionExpression represents the conditional operator ('?:').
 */
class ConditionExpression implements Expression
{
    protected $condition;
    protected $left, $right;

    /**
     * Initializes a new Expression instance.
     *
     * @param Expression $condition expression
     * @param Expression $left      left alternative
     * @param Expression $right     right alternative
     */
    public function __construct($condition, $left, $right)
    {
        $this->condition = $condition;
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Returns the value of this expression.
     *
     * @param Context $context  symbol table
     */
    public function value($context)
    {
        return $this->condition->value($context) ?
                    $this->left->value($context) : $this->right->value($context);
    }
}
