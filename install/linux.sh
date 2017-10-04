#!/usr/bin/env bash

green="\033[0;32m"
nc="\033[0m"
check="\u2714"

bundle="https://raw.githubusercontent.com/quack/quack/jsx-parser/bin/quack"
compiler_source=$(wget -qO- $bundle)
shebang="#!$(which php5)"
bin=/usr/bin/quack

touch $bin
chmod +x $bin
echo $shebang > $bin
echo "$compiler_source" >> $bin

echo -e "${green}[${check}] Wow! You got quack installed under ${bin}! Go there, run \`quack'${nc}"
