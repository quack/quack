TEST = phpunit --verbose --colors
HYPATH = ~/.local/bin/hy

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
	$(TEST) ./tests/LexerTest.php
else
ifeq ($(module), parser)
	$(TEST) ./tests/ParserTest.php
else
	@echo No module defined for testing
endif
endif

deploy:
	git add .
	git commit -m "$(message)"
	git push origin master

count_lines:
	cd src; git ls-files | xargs wc -l

install:
	cp bin/quack /usr/bin

dev_dependencies:
	pip install --user hy
	pip install --user termcolor

qtest:
	$(HYPATH) ./tools/testsuite/run-tests.hy --dir tests --exe "php5 ./src/Quack.php %s"

dev_test:
	$(MAKE) dev_dependencies
	$(MAKE) test module=lexer
	$(MAKE) qtest

todo:
	grep -r 'TODO' src/
