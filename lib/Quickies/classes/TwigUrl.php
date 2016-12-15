<?php
namespace Iekadou\Quickies;

use Twig_TokenParser;
use Twig_Node;
use Twig_Token;
use Twig_Node_Expression_Name;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_Array;
use Twig_Compiler;

class Twig_Url_TokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(Twig_Token::STRING_TYPE)->getValue();
        $arg_array = array();
        $index = 0;
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $cur_value = $parser->getExpressionParser()->parseExpression();
            if ($cur_value instanceof Twig_Node_Expression_Name) {
                array_push($arg_array, new Twig_Node_Expression_Constant($index, $token->getLine()));
                array_push($arg_array, $cur_value);
            } else {
                array_push($arg_array, new Twig_Node_Expression_Constant($index, $token->getLine()));
                array_push($arg_array, $cur_value);
            }
            $index++;
        }
        $arg_array = new Twig_Node_Expression_Array($arg_array, $token->getLine());
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new Twig_Url_Node($name, $arg_array, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'url';
    }
}

class Twig_Url_Node extends Twig_Node
{
    public function __construct($name, $value, $line, $tag = null)
    {
        parent::__construct(array('value' => $value), array('name' => $name), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->raw("echo ")
            ->raw("Iekadou\\Quickies\\UrlsPy::get_url('")
            ->raw($this->getAttribute('name'))
            ->raw("',")
            ->subcompile($this->getNode('value'))
            ->raw(");\n")
        ;
    }
}