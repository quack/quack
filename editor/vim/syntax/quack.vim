" Vim syntax file
" Language: Quack
" Maintainer: Marcelo Camargo <marcelocamargo@linuxmail.org>
" Last Change: 2016 Aug 6i
"
if exists("b:current_syntax")
    finish
endif

syn cluster quackNotTop contains=quackTodo,quackDocComment

syn match quackComment '(\*\_.*\*)' contains=quackTodo
syn keyword quackTodo TODO NOTE FIXME contained

syn keyword quackBoolean true false nil
syn keyword quackKeyword if switch case end when unless do while for foreach begin
syn keyword quackKeyword impl struct trait enum member else where then fn
syn keyword quackInclude import as module

syn keyword quackOperator and or not xor

hi def link quackComment  Comment
hi def link quackKeyword  Keyword
hi def link quackInclude  Include
hi def link quackBoolean  Boolean
hi def link quackTodo     Todo
hi def link quackOperator Operator
