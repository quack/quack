HYPATH = ~/.local/bin/hy

test:
	$(HYPATH) ./tools/testsuite/run-tests.hy --dir tests --exe "php ./src/Quack.php %s"

rfc:
	$(HYPATH) ./tools/testsuite/run-tests.hy --dir rfc --exe "php ./src/Quack.php %s"
.PHONY: rfc

count_lines:
	@(cd src; git ls-files | xargs wc -l | sort -t '_' -k 1n | sed -E "s/([0-9]+) (.*)/| \1 - \2/g" | awk '{$$1=$$1}1;' | column)

configure:
	pip install --user hy==0.11.1
	pip install --user termcolor
	pear install PHP_CodeSniffer

lint:
	phpcs --standard=PSR2 ./src/*

todo:
	grep -r 'TODO' src/
