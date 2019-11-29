<?php
namespace App\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\InputParameter;

class CollateFunction extends FunctionNode
{
    /** @var InputParameter expressionToCollate */
    public $expressionToCollate = null;
    public $collation = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->expressionToCollate = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->collation = $lexer->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('%s COLLATE %s', $this->expressionToCollate->dispatch($sqlWalker), $this->collation);
    }
}
