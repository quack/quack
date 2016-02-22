syn keyword quackStatement class def open init forach if let super in
syn keyword quackType string integer boolean array nil

syn keyword quackConditional When

syn region quackComment start="(\*" end="\*)"

syn region quackString start="\"" end="\""
syn region quackString start="'" end="'"

syn match quackNumber "\<\d\+\>"

syn match quackDelimiters "[\[\]]\|[{}]\|[||]"

syn match  quackIdent  "\<[a,b,c,d,l,n,o,u,x][A-Z][A-Za-z0-9_]*\>"
syn match  quackIdent  "\<[a-z]\>"

syn match quackOperator ":-"
syn match quackOperator ":="
syn match quackOperator ":"
syn match quackOperator ","
syn match quackOperator "\."
syn match quackOperator "++"
syn match quackOperator "<<<"
syn match quackOperator "=="
syn match quackOperator "!"

hi def link quackStatement Statement
hi def link quackComment Comment
hi def link quackType Type
hi def link quackNumber Number
hi def link quackOperator Special
hi def link quackConditional Conditional
hi def link quackNumber Number
hi def link quackDelimiters Delimiter
hi def link quackIdent Identifier
