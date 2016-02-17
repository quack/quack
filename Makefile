test:
ifeq ($(module), lexer)
	phpunit --verbose --colors ./tests/LexerTest.php
else
	@echo No module defined for testing
endif
