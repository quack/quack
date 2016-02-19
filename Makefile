TEST = phpunit --verbose --colors

repl:
ifeq ($(mode), ast)
	cd src/repl; php UranoRepl.php --ast
else
ifeq ($(mode), python)
	cd src/repl; php UranoRepl.php --python
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
