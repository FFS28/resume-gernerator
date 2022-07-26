<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\InputParameter;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class ToCharFunction extends FunctionNode
{
    /** @var InputParameter firstValue */
    public $firstValue = null;

    /** @var InputParameter secondValue */
    public $secondValue = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstValue = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondValue = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'TO_CHAR(' .
            $this->firstValue->dispatch($sqlWalker) . ', ' .
            $this->secondValue->dispatch($sqlWalker) .
            ')';
    }
}
