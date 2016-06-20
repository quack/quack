non_empty_statement:
    | T_UNSET '(' variables_list ')' ';'                    { $$ = Stmt\Unset_[$3]; }
          { $$ = Stmt\Foreach_[$3, $7[0], ['keyVar' => $5, 'byRef' => $7[1], 'stmts' => $9]]; }
;

expr:
    | T_ISSET '(' variables_list ')'                        { $$ = Expr\Isset_[$3]; }
    | T_EMPTY '(' expr ')'                                  { $$ = Expr\Empty_[$3]; }
    | T_EVAL parentheses_expr                               { $$ = Expr\Eval_[$2]; }
    | T_EXIT exit_expr                                      { $$ = Expr\Exit_        [$2]; }
    | scalar                                                { $$ = $1; }
    | scalar_dereference                                    { $$ = $1; }
;

scalar_dereference:
      array_expr '[' dim_offset ']'                         { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | T_CONSTANT_ENCAPSED_STRING '[' dim_offset ']'
          { $$ = Expr\ArrayDimFetch[Scalar\String_[Scalar\String_::parse($1, false)], $3]; }
    | constant '[' dim_offset ']'                           { $$ = Expr\ArrayDimFetch[$1, $3]; }
    | scalar_dereference '[' dim_offset ']'                 { $$ = Expr\ArrayDimFetch[$1, $3]; }
    /* alternative array syntax missing intentionally */
;
