grammar Trigger;

expr: expr op=(MUL|DIV) expr # MulDiv
    | expr op=(ADD|SUB) expr # AddSub
    | left=expr op=comparator right=expr # ComparatorExpression
    | left=expr op=binary right=expr     # BinaryExpression
    | bool                               # BoolExpression
    | INT                    # Int
    | FLT                    # Float
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
unary: NOT;
and: AND;

binary
 : AND | OR
 ;

INT: [0-9]+ ;
FLT : INT+ '.' INT*
    | '.' INT+
    ;
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
