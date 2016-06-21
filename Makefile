TEST = phpunit --verbose --colors

build:
	gcc ./src/quack.c -o ./bin/quack

repl:
ifeq ($(mode), ast)
	cd src/repl; php QuackRepl.php --ast
else
	@echo No mode for repl
endif

test:
ifeq ($(module), lexer)
	$(TEST)  ./tests/LexerTest.php
else
ifeq ($(module), parser)
	$(TEST) ./tests/ParserTest.php
else
	@echo No module defined for testing
endif
endif

deploy:
	git add *
	git commit -m "$(message)"
	git push origin master

count_lines:
	cd src;	git ls-files | xargs wc -l

test_all:
	$(MAKE) test module=lexer
	$(MAKE) test module=parser

install:
	cp bin/quack /usr/bin
