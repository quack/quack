non_empty_statement:
    | T_FOR '(' for_expr ';'  for_expr ';' for_expr ')' for_statement
          { $$ = Stmt\For_[['init' => $3, 'cond' => $5, 'loop' => $7, 'stmts' => $9]]; }
    | T_STATIC static_var_list ';'                          { $$ = Stmt\Static_[$2]; }
    | T_UNSET '(' variables_list ')' ';'                    { $$ = Stmt\Unset_[$3]; }
          { $$ = Stmt\Foreach_[$3, $7[0], ['keyVar' => $5, 'byRef' => $7[1], 'stmts' => $9]]; }
;

variables_list:
      variable                                              { init($1); }
    | variables_list ',' variable                           { push($1, $3); }
;

name_list:
      name                                                  { init($1); }
    | name_list ',' name                                    { push($1, $3); }
;

for_statement:
      statement                                             { $$ = toArray($1); }
;

argument_list:
      '(' ')'                                               { $$ = array(); }
    | '(' non_empty_argument_list ')'                       { $$ = $2; }
;

non_empty_argument_list:
      argument                                              { init($1); }
    | non_empty_argument_list ',' argument                  { push($1, $3); }
;

argument:
      expr                                                  { $$ = Node\Arg[$1, false, false]; }
    | '&' variable                                          { $$ = Node\Arg[$2, true, false]; }
    | T_ELLIPSIS expr                                       { $$ = Node\Arg[$2, false, true]; }
;

global_var_list:
      global_var_list ',' global_var                        { push($1, $3); }
    | global_var                                            { init($1); }
;

global_var:
      T_VARIABLE                                            { $$ = Expr\Variable[parseVar($1)]; }
    | '$' variable                                          { $$ = Expr\Variable[$2]; }
    | '$' '{' expr '}'                                      { $$ = Expr\Variable[$3]; }
;

static_var_list:
      static_var_list ',' static_var                        { push($1, $3); }
    | static_var                                            { init($1); }
;

static_var:
      T_VARIABLE                                            { $$ = Stmt\StaticVar[parseVar($1), null]; }
    | T_VARIABLE '=' static_scalar                          { $$ = Stmt\StaticVar[parseVar($1), $3]; }
;

class_statement:
    | T_USE name_list trait_adaptations                     { $$ = Stmt\TraitUse[$2, $3]; }
;

trait_adaptations:
      ';'                                                   { $$ = array(); }
    | '{' trait_adaptation_list '}'                         { $$ = $2; }
;

trait_adaptation_list:
      /* empty */                                           { init(); }
    | trait_adaptation_list trait_adaptation                { push($1, $2); }
;

trait_adaptation:
      trait_method_reference_fully_qualified T_INSTEADOF name_list ';'
          { $$ = Stmt\TraitUseAdaptation\Precedence[$1[0], $1[1], $3]; }
    | trait_method_reference T_AS member_modifier identifier ';'
          { $$ = Stmt\TraitUseAdaptation\Alias[$1[0], $1[1], $3, $4]; }
    | trait_method_reference T_AS member_modifier ';'
          { $$ = Stmt\TraitUseAdaptation\Alias[$1[0], $1[1], $3, null]; }
    | trait_method_reference T_AS T_STRING ';'
          { $$ = Stmt\TraitUseAdaptation\Alias[$1[0], $1[1], null, $3]; }
    | trait_method_reference T_AS reserved_non_modifiers ';'
          { $$ = Stmt\TraitUseAdaptation\Alias[$1[0], $1[1], null, $3]; }
;

trait_method_reference_fully_qualified:
      name T_PAAMAYIM_NEKUDOTAYIM identifier                { $$ = array($1, $3); }
;
trait_method_reference:
      trait_method_reference_fully_qualified                { $$ = $1; }
    | identifier                                            { $$ = array(null, $1); }
;

for_expr:
      /* empty */                                           { $$ = array(); }
    | expr_list                                             { $$ = $1; }
;

expr:
      variable                                              { $$ = $1; }
    | variable '=' expr                                     { $$ = Expr\Assign[$1, $3]; }
    | variable '=' '&' variable                             { $$ = Expr\AssignRef[$1, $4]; }
    | variable '=' '&' new_expr                             { $$ = Expr\AssignRef[$1, $4]; }
    | T_ISSET '(' variables_list ')'                        { $$ = Expr\Isset_[$3]; }
    | T_EMPTY '(' expr ')'                                  { $$ = Expr\Empty_[$3]; }
    | T_EVAL parentheses_expr                               { $$ = Expr\Eval_[$2]; }
    | T_INT_CAST expr                                       { $$ = Expr\Cast\Int_    [$2]; }
    | T_DOUBLE_CAST expr                                    { $$ = Expr\Cast\Double  [$2]; }
    | T_STRING_CAST expr                                    { $$ = Expr\Cast\String_ [$2]; }
    | T_ARRAY_CAST expr                                     { $$ = Expr\Cast\Array_  [$2]; }
    | T_OBJECT_CAST expr                                    { $$ = Expr\Cast\Object_ [$2]; }
    | T_BOOL_CAST expr                                      { $$ = Expr\Cast\Bool_   [$2]; }
    | T_UNSET_CAST expr                                     { $$ = Expr\Cast\Unset_  [$2]; }
    | T_EXIT exit_expr                                      { $$ = Expr\Exit_        [$2]; }
    | scalar                                                { $$ = $1; }
    | array_expr                                            { $$ = $1; }
    | scalar_dereference                                    { $$ = $1; }
;

array_expr:
    | '[' array_pair_list ']'                               { $$ = Expr\Array_[$2]; }
;

scalar_dereference:
      array_expr '[' dim_offset ']'                         { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | T_CONSTANT_ENCAPSED_STRING '[' dim_offset ']'
          { $$ = Expr\ArrayDimFetch[Scalar\String_[Scalar\String_::parse($1, false)], $3]; }
    | constant '[' dim_offset ']'                           { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | scalar_dereference '[' dim_offset ']'                 { $$ = Expr\ArrayDimFetch[$1, $3]; }
    /* alternative array syntax missing intentionally */
;

function_call:
      name argument_list                                    { $$ = Expr\FuncCall[$1, $2]; }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM identifier argument_list
          { $$ = Expr\StaticCall[$1, $3, $4]; }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM '{' expr '}' argument_list
          { $$ = Expr\StaticCall[$1, $4, $6]; }
    | static_property argument_list {
            if ($1 instanceof Node\Expr\StaticPropertyFetch) {
                $$ = Expr\StaticCall[$1->class, Expr\Variable[$1->name], $2];
            } elseif ($1 instanceof Node\Expr\ArrayDimFetch) {
                $tmp = $1;
                while ($tmp->var instanceof Node\Expr\ArrayDimFetch) {
                    $tmp = $tmp->var;
                }

                $$ = Expr\StaticCall[$tmp->var->class, $1, $2];
                $tmp->var = Expr\Variable[$tmp->var->name];
            } else {
                throw new \Exception;
            }
          }
    | variable_without_objects argument_list
          { $$ = Expr\FuncCall[$1, $2]; }
    | function_call '[' dim_offset ']'                      { $$ = Expr\ArrayDimFetch[$1, $3]; }
      /* alternative array syntax missing intentionally */
;

class_name:
      T_STATIC                                              { $$ = Name[$1]; }
    | name                                                  { $$ = $1; }
;

name:
      namespace_name_parts                                  { $$ = Name[$1]; }
    | T_NS_SEPARATOR namespace_name_parts                   { $$ = Name\FullyQualified[$2]; }
    | T_NAMESPACE T_NS_SEPARATOR namespace_name_parts       { $$ = Name\Relative[$3]; }
;

class_name_reference:
      class_name                                            { $$ = $1; }
    | dynamic_class_name_reference                          { $$ = $1; }
;

dynamic_class_name_reference:
      object_access_for_dcnr                                { $$ = $1; }
    | base_variable                                         { $$ = $1; }
;

class_name_or_var:
      class_name                                            { $$ = $1; }
    | reference_variable                                    { $$ = $1; }
;

object_access_for_dcnr:
      base_variable T_OBJECT_OPERATOR object_property
          { $$ = Expr\PropertyFetch[$1, $3]; }
    | object_access_for_dcnr T_OBJECT_OPERATOR object_property
          { $$ = Expr\PropertyFetch[$1, $3]; }
    | object_access_for_dcnr '[' dim_offset ']'             { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | object_access_for_dcnr '{' expr '}'                   { $$ = Expr\ArrayDimFetch[$1, $3]; }
;

exit_expr:
      /* empty */                                           { $$ = null; }
    | '(' ')'                                               { $$ = null; }
    | parentheses_expr                                      { $$ = $1; }
;

backticks_expr:
      /* empty */                                           { $$ = array(); }
    | T_ENCAPSED_AND_WHITESPACE
          { $$ = array(Scalar\EncapsedStringPart[Scalar\String_::parseEscapeSequences($1, '`', false)]); }
    | encaps_list                                           { parseEncapsed($1, '`', false); $$ = $1; }
;

ctor_arguments:
      /* empty */                                           { $$ = array(); }
    | argument_list                                         { $$ = $1; }
;

common_scalar:
      T_LNUMBER                                             { $$ = Scalar\LNumber[Scalar\LNumber::parse($1)]; }
    | T_DNUMBER                                             { $$ = Scalar\DNumber[Scalar\DNumber::parse($1)]; }
    | T_CONSTANT_ENCAPSED_STRING                            { $$ = Scalar\String_[Scalar\String_::parse($1, false)]; }
    | T_LINE                                                { $$ = Scalar\MagicConst\Line[]; }
    | T_FILE                                                { $$ = Scalar\MagicConst\File[]; }
    | T_DIR                                                 { $$ = Scalar\MagicConst\Dir[]; }
    | T_CLASS_C                                             { $$ = Scalar\MagicConst\Class_[]; }
    | T_TRAIT_C                                             { $$ = Scalar\MagicConst\Trait_[]; }
    | T_METHOD_C                                            { $$ = Scalar\MagicConst\Method[]; }
    | T_FUNC_C                                              { $$ = Scalar\MagicConst\Function_[]; }
    | T_NS_C                                                { $$ = Scalar\MagicConst\Namespace_[]; }
;

static_scalar:
      common_scalar                                         { $$ = $1; }
    | class_name T_PAAMAYIM_NEKUDOTAYIM identifier          { $$ = Expr\ClassConstFetch[$1, $3]; }
    | name                                                  { $$ = Expr\ConstFetch[$1]; }
    | '[' static_array_pair_list ']'                        { $$ = Expr\Array_[$2]; }
    | static_operation                                      { $$ = $1; }
;

static_operation:
      static_scalar T_BOOLEAN_OR static_scalar              { $$ = Expr\BinaryOp\BooleanOr [$1, $3]; }
    | static_scalar T_BOOLEAN_AND static_scalar             { $$ = Expr\BinaryOp\BooleanAnd[$1, $3]; }
    | static_scalar T_LOGICAL_OR static_scalar              { $$ = Expr\BinaryOp\LogicalOr [$1, $3]; }
    | static_scalar T_LOGICAL_AND static_scalar             { $$ = Expr\BinaryOp\LogicalAnd[$1, $3]; }
    | static_scalar T_LOGICAL_XOR static_scalar             { $$ = Expr\BinaryOp\LogicalXor[$1, $3]; }
    | static_scalar '|' static_scalar                       { $$ = Expr\BinaryOp\BitwiseOr [$1, $3]; }
    | static_scalar '&' static_scalar                       { $$ = Expr\BinaryOp\BitwiseAnd[$1, $3]; }
    | static_scalar '^' static_scalar                       { $$ = Expr\BinaryOp\BitwiseXor[$1, $3]; }
    | static_scalar '.' static_scalar                       { $$ = Expr\BinaryOp\Concat    [$1, $3]; }
    | static_scalar '+' static_scalar                       { $$ = Expr\BinaryOp\Plus      [$1, $3]; }
    | static_scalar '-' static_scalar                       { $$ = Expr\BinaryOp\Minus     [$1, $3]; }
    | static_scalar '*' static_scalar                       { $$ = Expr\BinaryOp\Mul       [$1, $3]; }
    | static_scalar '/' static_scalar                       { $$ = Expr\BinaryOp\Div       [$1, $3]; }
    | static_scalar '%' static_scalar                       { $$ = Expr\BinaryOp\Mod       [$1, $3]; }
    | static_scalar T_SL static_scalar                      { $$ = Expr\BinaryOp\ShiftLeft [$1, $3]; }
    | static_scalar T_SR static_scalar                      { $$ = Expr\BinaryOp\ShiftRight[$1, $3]; }
    | static_scalar T_POW static_scalar                     { $$ = Expr\BinaryOp\Pow       [$1, $3]; }
    | '+' static_scalar %prec T_INC                         { $$ = Expr\UnaryPlus [$2]; }
    | '-' static_scalar %prec T_INC                         { $$ = Expr\UnaryMinus[$2]; }
    | '!' static_scalar                                     { $$ = Expr\BooleanNot[$2]; }
    | '~' static_scalar                                     { $$ = Expr\BitwiseNot[$2]; }
    | static_scalar T_IS_IDENTICAL static_scalar            { $$ = Expr\BinaryOp\Identical     [$1, $3]; }
    | static_scalar T_IS_NOT_IDENTICAL static_scalar        { $$ = Expr\BinaryOp\NotIdentical  [$1, $3]; }
    | static_scalar T_IS_EQUAL static_scalar                { $$ = Expr\BinaryOp\Equal         [$1, $3]; }
    | static_scalar T_IS_NOT_EQUAL static_scalar            { $$ = Expr\BinaryOp\NotEqual      [$1, $3]; }
    | static_scalar '<' static_scalar                       { $$ = Expr\BinaryOp\Smaller       [$1, $3]; }
    | static_scalar T_IS_SMALLER_OR_EQUAL static_scalar     { $$ = Expr\BinaryOp\SmallerOrEqual[$1, $3]; }
    | static_scalar '>' static_scalar                       { $$ = Expr\BinaryOp\Greater       [$1, $3]; }
    | static_scalar T_IS_GREATER_OR_EQUAL static_scalar     { $$ = Expr\BinaryOp\GreaterOrEqual[$1, $3]; }
    | static_scalar '?' static_scalar ':' static_scalar     { $$ = Expr\Ternary[$1, $3,   $5]; }
    | static_scalar '?' ':' static_scalar                   { $$ = Expr\Ternary[$1, null, $4]; }
    | static_scalar '[' static_scalar ']'                   { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | '(' static_scalar ')'                                 { $$ = $2; }
;

constant:
      name                                                  { $$ = Expr\ConstFetch[$1]; }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM identifier
          { $$ = Expr\ClassConstFetch[$1, $3]; }
;

scalar:
      common_scalar                                         { $$ = $1; }
    | constant                                              { $$ = $1; }
    | '"' encaps_list '"'
          { parseEncapsed($2, '"', false); $$ = Scalar\Encapsed[$2]; }
;

static_array_pair_list:
      /* empty */                                           { $$ = array(); }
    | non_empty_static_array_pair_list optional_comma       { $$ = $1; }
;

non_empty_static_array_pair_list:
      non_empty_static_array_pair_list ',' static_array_pair { push($1, $3); }
    | static_array_pair                                      { init($1); }
;

static_array_pair:
      static_scalar T_DOUBLE_ARROW static_scalar            { $$ = Expr\ArrayItem[$3, $1,   false]; }
    | static_scalar                                         { $$ = Expr\ArrayItem[$1, null, false]; }
;

variable:
      object_access                                         { $$ = $1; }
    | base_variable                                         { $$ = $1; }
    | function_call                                         { $$ = $1; }
    | new_expr_array_deref                                  { $$ = $1; }
;

new_expr_array_deref:
      '(' new_expr ')' '[' dim_offset ']'                   { $$ = Expr\ArrayDimFetch[$2, $5]; }
    | new_expr_array_deref '[' dim_offset ']'               { $$ = Expr\ArrayDimFetch[$1, $3]; }
      /* alternative array syntax missing intentionally */
;

object_access:
      variable_or_new_expr T_OBJECT_OPERATOR object_property
          { $$ = Expr\PropertyFetch[$1, $3]; }
    | variable_or_new_expr T_OBJECT_OPERATOR object_property argument_list
          { $$ = Expr\MethodCall[$1, $3, $4]; }
    | object_access argument_list                           { $$ = Expr\FuncCall[$1, $2]; }
    | object_access '[' dim_offset ']'                      { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | object_access '{' expr '}'                            { $$ = Expr\ArrayDimFetch[$1, $3]; }
;

variable_or_new_expr:
      variable                                              { $$ = $1; }
    | '(' new_expr ')'                                      { $$ = $2; }
;

variable_without_objects:
      reference_variable                                    { $$ = $1; }
    | '$' variable_without_objects                          { $$ = Expr\Variable[$2]; }
;

base_variable:
      variable_without_objects                              { $$ = $1; }
    | static_property                                       { $$ = $1; }
;

static_property:
      class_name_or_var T_PAAMAYIM_NEKUDOTAYIM '$' reference_variable
          { $$ = Expr\StaticPropertyFetch[$1, $4]; }
    | static_property_with_arrays                           { $$ = $1; }
;

static_property_with_arrays:
      class_name_or_var T_PAAMAYIM_NEKUDOTAYIM T_VARIABLE
          { $$ = Expr\StaticPropertyFetch[$1, parseVar($3)]; }
    | class_name_or_var T_PAAMAYIM_NEKUDOTAYIM '$' '{' expr '}'
          { $$ = Expr\StaticPropertyFetch[$1, $5]; }
    | static_property_with_arrays '[' dim_offset ']'        { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | static_property_with_arrays '{' expr '}'              { $$ = Expr\ArrayDimFetch[$1, $3]; }
;

reference_variable:
      reference_variable '[' dim_offset ']'                 { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | reference_variable '{' expr '}'                       { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | T_VARIABLE                                            { $$ = Expr\Variable[parseVar($1)]; }
    | '$' '{' expr '}'                                      { $$ = Expr\Variable[$3]; }
;

dim_offset:
      /* empty */                                           { $$ = null; }
    | expr                                                  { $$ = $1; }
;

object_property:
      T_STRING                                              { $$ = $1; }
    | '{' expr '}'                                          { $$ = $2; }
    | variable_without_objects                              { $$ = $1; }
;

array_pair_list:
      /* empty */                                           { $$ = array(); }
    | non_empty_array_pair_list optional_comma              { $$ = $1; }
;

non_empty_array_pair_list:
      non_empty_array_pair_list ',' array_pair              { push($1, $3); }
    | array_pair                                            { init($1); }
;

array_pair:
    | expr T_DOUBLE_ARROW '&' variable                      { $$ = Expr\ArrayItem[$4, $1,   true]; }
    | '&' variable                                          { $$ = Expr\ArrayItem[$2, null, true]; }
;

encaps_list:
      encaps_list encaps_var                                { push($1, $2); }
    | encaps_list encaps_string_part                        { push($1, $2); }
    | encaps_var                                            { init($1); }
    | encaps_string_part encaps_var                         { init($1, $2); }
;

encaps_string_part:
      T_ENCAPSED_AND_WHITESPACE                             { $$ = Scalar\EncapsedStringPart[$1]; }
;

encaps_var:
      T_VARIABLE                                            { $$ = Expr\Variable[parseVar($1)]; }
    | T_VARIABLE '[' encaps_var_offset ']'                  { $$ = Expr\ArrayDimFetch[Expr\Variable[parseVar($1)], $3]; }
    | T_VARIABLE T_OBJECT_OPERATOR T_STRING                 { $$ = Expr\PropertyFetch[Expr\Variable[parseVar($1)], $3]; }
    | T_DOLLAR_OPEN_CURLY_BRACES expr '}'                   { $$ = Expr\Variable[$2]; }
    | T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME '}'       { $$ = Expr\Variable[$2]; }
    | T_DOLLAR_OPEN_CURLY_BRACES T_STRING_VARNAME '[' expr ']' '}'
          { $$ = Expr\ArrayDimFetch[Expr\Variable[$2], $4]; }
    | T_CURLY_OPEN variable '}'                             { $$ = $2; }
;

encaps_var_offset:
      T_STRING                                              { $$ = Scalar\String_[$1]; }
    | T_NUM_STRING                                          { $$ = Scalar\String_[$1]; }
    | T_VARIABLE                                            { $$ = Expr\Variable[parseVar($1)]; }
;
