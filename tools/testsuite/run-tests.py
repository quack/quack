# Quack Compiler and toolkit
# Copyright (C) 2015-2017 Quack and CONTRIBUTORS
#
# This file is part of Quack.
#
# Quack is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Quack is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Quack.  If not, see <http://www.gnu.org/licenses/>.
#

# This is a compatibility hack for `run-tests.hy', as long as Hy was
# using a moduled called `Astor', which had a minor change that broke
# the API because some people don't know what a minor change should be,
# so Travis was installing a "compatible" version what wasn't truly
# compatible. Therefore, we'll no longer use Hy in this project and
# I want to give a special thanks for the maintainers of `Astor'.
# Without you, I wouldn't have to waste time rewriting this (yes, I'm
# a bit angry and sad now).
#
# @author Marcelo Haskell Camargo <marcelocamargo@linuxmail.org>

from os import listdir, walk, linesep, makedirs, popen
from os.path import isfile, isdir, join, exists
from sys import argv, exit
from getopt import getopt, GetoptError
from glob import iglob
import fnmatch
from shutil import rmtree
from ntpath import basename
from termcolor import colored, cprint
import difflib

__version__ = 'Quack test toolkit v0.0.1-alpha-compat'
__file_pattern__ = '*.qtest'
__tmp_folder__ = 'tmp'

def get_params(args):
    """
    Returns the parameters passed to the script (parsed)
    """
    return getopt(args, 'v', ['dir=', 'exe='])[0]

def version():
    """
    Test suite version
    """
    print(__version__)

def throw_error(message):
    """
    Handles the received errors while receiving input
    """
    print(message)
    exit(666)

def get_all_test_files(dir):
    """
    Lists the path + filename of all the files in the directory, but
    recursively
    """
    if not isdir(dir):
        throw_error('Directory not found')
    matches = []
    for (root, dirname, filenames) in walk(dir):
        for filename in fnmatch.filter(filenames, __file_pattern__):
            matches.append(join(root, filename))
    return matches

def file_get_contents(file):
    """
    Returns the clean content of a file
    """
    with open(file) as f:
        return f.read()

def group_sections(input):
    """
    Receives an input and groups the sections
    """
    tok = 'none'
    describe = [] # File description
    source = []   # Source code
    expect = []   # Expected output
    command = []  # Command to run
    lines = input.split(linesep)
    # Parser is cumulative. You can have multiple and isolated sections
    for line in lines:
        if line == '%%command':
            tok = 'command'
        elif line == '%%source':
            tok = 'source'
        elif line == '%%comments':
            tok = 'none'
        elif line == '%%describe':
            tok = 'describe'
        elif line == '%%expect':
            tok = 'expect'
        else:
            if tok == 'command':
                command.append(line)
                tok = 'none'
            elif tok == 'describe':
                describe.append(line)
            elif tok == 'source':
                source.append(line)
            elif tok == 'expect':
                expect.append(line)
    joiner = lambda lst: linesep.join(lst)
    return {
        'describe': joiner(describe),
        'source': joiner(source),
        'expect': joiner(expect),
        'command': joiner(command) if command else "php ./src/Main.php %s --disable-typechecker --disable-scope"
    }

def create_tmp_folder():
    """
    Creates the temp folder for the tests
    """
    if not exists(__tmp_folder__):
        makedirs(__tmp_folder__)

def delete_tmp_files():
    """
    Deletes the test folder and its contents recursively
    """
    if exists(__tmp_folder__):
        rmtree(__tmp_folder__)

# Gets a grouped section and saes the input result to a temp file
def persist_source(name, source):
    with open(join(__tmp_folder__, name + '.tmp.qk'), 'w') as f:
        f.write(source)

def run_tests(generator):
    """
    The test suite!
    """
    tests = 0
    failed = 0
    passed = 0

    # start by creating the folder to store our tests and then feed the compiler
    create_tmp_folder()

    for file in generator:
        filename = basename(file)
        section = group_sections(file_get_contents(file))
        exe = section['command']
        # Store the source for future queries
        persist_source(filename, section['source'])
        command = exe.replace('%s', join(__tmp_folder__, filename + '.tmp.qk'))
        output = popen(command).read().strip()
        stripped_to_compare = section['expect'].strip()
        # We have enough data to give the results

        tests = tests + 1
        if output == stripped_to_compare:
            passed = passed + 1
            cprint('PASS', 'white', 'on_green', attrs=['bold'], end=' ')
            print(file + ' - ' + section['describe'])
        else:
            failed = failed + 1
            cprint('FAIL', 'white', 'on_red', attrs=['bold'], end=' ')
            print(file + ' - ' + section['describe'])
            print('Difference:')
            output_list = output.split(linesep)
            expected_list = stripped_to_compare.split(linesep)
            d = difflib.Differ()
            diff = d.compare(output_list, expected_list)
            print(linesep.join(diff))

    print(colored('\nResults: ', attrs=['bold', 'underline']))
    print(colored('Run:  ' + str(tests), attrs=['bold']))
    print(colored('Pass: ' + str(passed), attrs=['bold']))
    print(colored('Fail: ' + str(failed), attrs=['bold']))

    # Dump garbage
    delete_tmp_files()
    return failed

def tuple_contains_key(needle, haystack):
    """
    Tells if a tuple contains a key. Returns (False, None) if not.
    Returns (True, value) if it does
    """
    fst = False
    snd = None
    for key, value in haystack:
        if key == needle:
            fst = True
            snd = value
            break
    return fst, snd

def main(args):
    """
    Entry point
    """
    try:
        params = get_params(args)
    except GetoptError as e:
        throw_error(e)
    else:
        if tuple_contains_key('-v', params)[0]:
            version()
        dir_tuple = tuple_contains_key('--dir', params)
        if dir_tuple[0]:
            dir = dir_tuple[1]
            result = run_tests(get_all_test_files(dir))
            if result > 0:
                exit(666)
        else:
            throw_error('--dir is obligatory')

if __name__ == '__main__':
    main(argv[1:])
