<?php
namespace Iekadou\Quickies;

use Twig_TokenParser;
use Twig_Node;
use Twig_Token;
use Twig_Compiler;

class Twig_Time_TokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new Twig_Time_Node($token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'rendering_time';
    }
}

class Twig_Time_Node extends Twig_Node
{
    public function __construct($line, $tag = null)
    {
        parent::__construct(array(), array(), $line, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->raw('global $RENDERING_START; echo ')
            ->raw('(microtime(true) - $RENDERING_START)')
            ->raw(';')
        ;
    }
}