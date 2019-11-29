<?php
namespace App\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\InputParameter;

class DistanceFunction extends FunctionNode
{
    /** @var InputParameter longitudeColumn */
    public $longitudeColumn = null;
    /** @var InputParameter latitudeColumn */
    public $latitudeColumn = null;
    /** @var InputParameter longitudeValue */
    public $longitudeValue = null;
    /** @var InputParameter latitudeValue */
    public $latitudeValue = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->longitudeColumn = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->latitudeColumn = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->longitudeValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->latitudeValue = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'st_distance(' .
            'point(' .
                $this->longitudeColumn->dispatch($sqlWalker) . ', ' .
                $this->latitudeColumn->dispatch($sqlWalker) .
            '), point(' .
                $this->longitudeValue->dispatch($sqlWalker) . ', ' .
                $this->latitudeValue->dispatch($sqlWalker) .
            ')' .
        ')';
    }
}
