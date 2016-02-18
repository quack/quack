TEST = phpunit --verbose --colors

test:
ifeq ($(module), lexer)
	$(TEST)  ./tests/LexerTest.php
else
ifeq ($(module), ast)
	$(TEST) ./tests/AstTest.php
else
	@echo No module defined for testing
endif
endif
