<?php
namespace App\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\InputParameter;

class LeftFunction extends FunctionNode
{
    /** @var InputParameter firstValue */
    public $firstValue = null;
    /** @var InputParameter secondValue */
    public $secondValue = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->secondValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'LEFT(' .
            $this->firstValue->dispatch($sqlWalker) . ', ' .
            $this->secondValue->dispatch($sqlWalker) .
        ')';
    }
}
