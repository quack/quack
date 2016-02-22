TEST = phpunit --verbose --colors

repl:
ifeq ($(mode), ast)
	cd src/repl; php QuackRepl.php --ast
else
ifeq ($(mode), python)
	cd src/repl; php QuackRepl.php --python
else
	@echo No mode for repl
endif
endif

test:
ifeq ($(module), lexer)
	$(TEST)  ./tests/LexerTest.php
else
ifeq ($(module), ast)
	$(TEST) ./tests/AstTest.php
else
ifeq ($(module), parser)
	$(TEST) ./tests/ParserTest.php
else
	@echo No module defined for testing
endif
endif
endif

deploy:
	git add *
	git commit -m "$(message)"
	git push origin master

count_lines:
	cd src;	git ls-files | xargs wc -l
