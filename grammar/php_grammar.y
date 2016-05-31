non_empty_statement:
    | T_FOR '(' for_expr ';'  for_expr ';' for_expr ')' for_statement
          { $$ = Stmt\For_[['init' => $3, 'cond' => $5, 'loop' => $7, 'stmts' => $9]]; }
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

variable:
      object_access                                         { $$ = $1; }
    | base_variable                                         { $$ = $1; }
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
