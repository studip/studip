<?php
/*
 * Template.php - expression template parser
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace exTpl;

require_once 'Scanner.php';
require_once 'Node.php';

/**
 * The Template class is the only externally visible API of this
 * template implementation. It can be used to create and render
 * template objects.
 */
class Template
{
    private static $tag_start = '{';
    private static $tag_end = '}';
    private $template;

    /**
     * Sets the delimiter strings used for the template tags, the
     * default delimiters are: $tag_start = '{', $tag_end = '}'.
     *
     * @param string $tag_start tag start marker
     * @param string $tag_end   tag end marker
     */
    public static function setTagMarkers($tag_start, $tag_end)
    {
        self::$tag_start = $tag_start;
        self::$tag_end = $tag_end;
    }

    /**
     * Initializes a new Template instance from the given string.
     *
     * @param string $string    template text
     */
    public function __construct($string)
    {
        $this->template = new ArrayNode();
        self::parseTemplate($this->template, $string, 0);
    }

    /**
     * Renders the template to a string using the given array of
     * bindings to resolve symbol references inside the template.
     *
     * @param array $bindings   symbol table
     *
     * @return string   string representation of the template
     */
    public function render(array $bindings)
    {
        return $this->template->render(new Context($bindings));
    }

    /**
     * Skips tokens until the end of the current tag is reached.
     *
     * @param string $string    template text
     * @param int    $pos       offset in string
     *
     * @return int      new offset in the string
     */
    private static function skipTokens($string, $pos)
    {
        for ($len = strlen($string); $pos < $len &&
                substr_compare($string, self::$tag_end, $pos, strlen(self::$tag_end)); ++$pos) {
            $chr = $string[$pos];
            if ($chr === '"' || $chr === "'") {
                while (++$pos < $len && $string[$pos] !== $chr) {
                    if ($string[$pos] === '\\') {
                        ++$pos;
                    }
                }
            }
        }

        return $pos;
    }

    /**
     * Parses a template string into a template node tree, starting
     * at the specified offset. All created nodes are added to the
     * given sequence node.
     *
     * @param ArrayNode $node   template node to build
     * @param string    $string string to parse
     * @param int       $pos    offset in string
     *
     * @return int      new offset in the string
     */
    private static function parseTemplate(ArrayNode $node, $string, $pos)
    {
        $len = strlen($string);

        while ($pos < $len) {
            $next_pos = strpos($string, self::$tag_start, $pos);

            if ($next_pos === false) {
                $child = new TextNode(substr($string, $pos));
                $node->addChild($child);
                break;
            }

            if ($next_pos > $pos) {
                $child = new TextNode(substr($string, $pos, $next_pos - $pos));
                $node->addChild($child);
            }

            $pos = $next_pos + strlen(self::$tag_start);
            $next_pos = self::skipTokens($string, $pos);
            $scanner = new Scanner(substr($string, $pos, $next_pos - $pos));
            $pos = $next_pos + strlen(self::$tag_end);

            switch ($scanner->nextToken()) {
                case T_FOREACH:
                    $scanner->nextToken();
                    $child = new IteratorNode(self::parseExpr($scanner));
                    $pos = self::parseTemplate($child, $string, $pos);
                    $node->addChild($child);
                    break;
                case T_ENDFOREACH:
                    return $pos;
                case T_IF:
                    $scanner->nextToken();
                    $child = new ConditionNode(self::parseExpr($scanner));
                    $pos = self::parseTemplate($child, $string, $pos);
                    $node->addChild($child);
                    break;
                case T_ELSEIF:
                    $scanner->nextToken();
                    $child = new ConditionNode(self::parseExpr($scanner));
                    $node->addElse();
                    $node->addChild($child);
                    return self::parseTemplate($child, $string, $pos);
                case T_ELSE:
                    $scanner->nextToken();
                    $node->addElse();
                    break;
                case T_ENDIF:
                    return $pos;
                default:
                    $child = new ExpressionNode(self::parseExpr($scanner));
                    $node->addChild($child);
            }

            if ($scanner->tokenType() !== false) {
                throw new TemplateParserException('syntax error', $scanner);
            }
        }

        return $pos;
    }

    /**
     * value: NUMBER | STRING | SYMBOL | '(' expr ')'
     */
    private static function parseValue(Scanner $scanner)
    {
        switch ($scanner->tokenType()) {
            case T_CONSTANT_ENCAPSED_STRING:
            case T_DNUMBER:
            case T_LNUMBER:
                $result = new ConstantExpression($scanner->tokenValue());
                break;
            case T_STRING:
                $result = new SymbolExpression($scanner->tokenValue());
                break;
            case '(':
                $scanner->nextToken();
                $result = self::parseExpr($scanner);

                if ($scanner->tokenType() !== ')') {
                    throw new TemplateParserException('missing ")"', $scanner);
                }
                break;
            default:
                throw new TemplateParserException('syntax error', $scanner);
        }

        $scanner->nextToken();
        return $result;
    }

    /**
     * index: value | index '[' expr ']' | index '.' SYMBOL
     */
    private static function parseIndex(Scanner $scanner)
    {
        $result = self::parseValue($scanner);
        $type = $scanner->tokenType();

        while ($type === '[' || $type === '.') {
            $scanner->nextToken();

            if ($type === '[') {
                $expr = self::parseExpr($scanner);

                if ($scanner->tokenType() !== ']') {
                    throw new TemplateParserException('missing "]"', $scanner);
                }
            } else if ($scanner->tokenType() === T_STRING) {
                $expr = new ConstantExpression($scanner->tokenValue());
            } else {
                throw new TemplateParserException('symbol expected', $scanner);
            }

            $scanner->nextToken();
            $result = new IndexExpression($result, $expr, $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * sign: '!' sign | '+' sign | '-' sign | index
     */
    private static function parseSign(Scanner $scanner)
    {
        switch ($scanner->tokenType()) {
            case '!':
                $scanner->nextToken();
                $result = new NotExpression(self::parseSign($scanner));
                break;
            case '+':
                $scanner->nextToken();
                $result = self::parseSign($scanner);
                break;
            case '-':
                $scanner->nextToken();
                $result = new MinusExpression(self::parseSign($scanner));
                break;
            default:
                $result = self::parseIndex($scanner);
        }

        return $result;
    }

    /**
     * product: sign | product '*' sign | product '/' sign | product '%' sign
     */
    private static function parseProduct(Scanner $scanner)
    {
        $result = self::parseSign($scanner);
        $type = $scanner->tokenType();

        while ($type === '*' || $type === '/' || $type === '%') {
            $scanner->nextToken();
            $result = new ArithExpression($result, self::parseSign($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * sum: product | sum '+' product | sum '-' product | sum '~' product
     */
    private static function parseSum(Scanner $scanner)
    {
        $result = self::parseProduct($scanner);
        $type = $scanner->tokenType();

        while ($type === '+' || $type === '-' || $type === '~') {
            $scanner->nextToken();
            $result = new ArithExpression($result, self::parseProduct($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * lt_gt: sum | lt_gt '<' concat | lt_gt IS_SMALLER_OR_EQUAL concat
     *            | lt_gt '>' concat | lt_gt IS_GREATER_OR_EQUAL concat
     */
    private static function parseLtGt(Scanner $scanner)
    {
        $result = self::parseSum($scanner);
        $type = $scanner->tokenType();

        while ($type === '<' || $type === T_IS_SMALLER_OR_EQUAL ||
               $type === '>' || $type === T_IS_GREATER_OR_EQUAL) {
            $scanner->nextToken();
            $result = new BooleanExpression($result, self::parseSum($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * cmp: lt_gt | cmp IS_EQUAL lt_gt | cmp IS_NOT_EQUAL lt_gt
     */
    private static function parseCmp(Scanner $scanner)
    {
        $result = self::parseLtGt($scanner);
        $type = $scanner->tokenType();

        while ($type === T_IS_EQUAL || $type === T_IS_NOT_EQUAL) {
            $scanner->nextToken();
            $result = new BooleanExpression($result, self::parseLtGt($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * and: cmp | and BOOLEAN_AND cmp
     */
    private static function parseAnd(Scanner $scanner)
    {
        $result = self::parseCmp($scanner);
        $type = $scanner->tokenType();

        while ($type === T_BOOLEAN_AND) {
            $scanner->nextToken();
            $result = new BooleanExpression($result, self::parseCmp($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * or: and | or BOOLEAN_OR and
     */
    private static function parseOr(Scanner $scanner)
    {
        $result = self::parseAnd($scanner);
        $type = $scanner->tokenType();

        while ($type === T_BOOLEAN_OR) {
            $scanner->nextToken();
            $result = new BooleanExpression($result, self::parseAnd($scanner), $type);
            $type = $scanner->tokenType();
        }

        return $result;
    }

    /**
     * expr: or | or '?' expr ':' expr
     */
    private static function parseExpr(Scanner $scanner)
    {
        $result = self::parseOr($scanner);

        if ($scanner->tokenType() === '?') {
            $scanner->nextToken();
            $expr = self::parseExpr($scanner);

            if ($scanner->tokenType() !== ':') {
                throw new TemplateParserException('missing ":"', $scanner);
            }

            $scanner->nextToken();
            $result = new ConditionExpression($result, $expr, self::parseExpr($scanner));
        }

        return $result;
    }
}

/**
 * Exception class used to report template parse errors.
 */
class TemplateParserException extends \Exception
{
    public function __construct($message, $scanner)
    {
        $type = $scanner->tokenType();
        $value = is_int($type) ? $scanner->tokenValue() : $type;

        return parent::__construct("$message at \"$value\"");
    }
}
