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

version = 'Quack test toolkit v0.0.1-alpha-compat'
file_pattern = '*.qtest'
tmp_folder = 'tmp'

def get_params(args):
    """
    Returns the parameters passed to the script (parsed)
    """
    return getopt(args, 'v', ['dir=', 'exe='])[0]

def version():
    """
    Test suite version
    """
    print(version)

def throw_error(message):
    """
    Handles the received errors while receiving input
    """
    print(message)
    exit(666)

def main(args):
    """
    Entry point
    """
    try:
        params = get_params(args)
    except GetoptError as e:
        throw_error(e)
    else:
        print(params)

if __name__ == '__main__':
    main(argv[1:])
