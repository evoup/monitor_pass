grammar Trigger;

expr: expr op=('*'|'/') expr # MulDiv
    | expr op=('+'|'-') expr # AddSub
//    | expr op=('<'|'>') expr # LtGt
//  可以直接left出来，不必之后再int left = visit(ctx.expr(0));
    | left=expr op=comparator right=expr # ComparatorExpression
    | left=expr op=binary right=expr     # BinaryExpression
    | bool                               # BoolExpression
    | INT                    # Int
//    | '('expr')'             # Parens
    | LPAREN expr RPAREN     # Parens
    | MONSTR                 # Monstr
    | NOT expr               # NotExpression
    ;

comparator
 : GT | GE | LT | LE | EQ
 ;

bool
 : TRUE | FALSE
 ;

nestedCondition : LPAREN condition+ RPAREN (binary nestedCondition)*;
condition: predicate (binary predicate)*
            | predicate (binary component)*;
component: predicate | multiAttrComp;
multiAttrComp : LPAREN predicate (and predicate)+ RPAREN;
predicate : INT comparator INT;
//comparator : GT | GE | LT | LE | EQ ;
//binary: AND | OR ;
unary: NOT;
and: AND;

binary
 : AND | OR
 ;

INT: [0-9]+ ;
MUL: '*' ;
DIV: '/' ;
ADD: '+' ;
SUB: '-' ;
GT:  '>' ;
GE: '>=' ;
LT:  '<' ;
LE: '<=' ;
EQ: '=' ;
WS : [ \t\r\n]+ -> skip ;
LPAREN     : '(' ;
RPAREN     : ')' ;
TRUE       : 'TRUE' ;
FALSE      : 'FALSE' ;
MONSTR     : '{' .*? '}' ;
COMMENT : '/*' .*? '*/' -> skip ;
AND        : 'AND' ;
OR         : 'OR' ;
