/**
 * Quack Compiler and toolkit
 * Copyright (C) 2016 Marcelo Camargo <marcelocamargo@linuxmail.org> and
 * CONTRIBUTORS.
 *
 * This file is part of Quack.
 *
 * Quack is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Quack is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Quack.  If not, see <http://www.gnu.org/licenses/>.
 */

#include <stdio.h>
#include <stdlib.h>

int main(int argc, char** argv)
{
  if (argc == 1) {
    printf("Panic: no input file\n");
    return 1;
  }

  char* source = NULL;
  FILE *fp = fopen(argv[1], "r");

  if (fp != NULL) {
    // Set the pointer to the end of the file
    if (fseek(fp, 0L, SEEK_END) == 0) {
      // Get the size of the buffer
      long buffer_size = ftell(fp);
      if (buffer_size == -1) {
        printf("Panic: cannot determine file size\n");
        return 1;
      }

      // Reserve space in the memory
      source = malloc(sizeof(char) * (buffer_size + 1));

      // Back to the start
      if (fseek(fp, 0L, SEEK_SET) != 0) {
        printf("Panic: cannot go back to file start\n");
        return 1;
      }

      // Read all into the buffer
      size_t new_length = fread(source, sizeof(char), buffer_size, fp);
      if (ferror(fp) != 0) {
        printf("Panic: error reading file\n");
        return 1;
      }

      // String terminator (+ 1)
      source[new_length++] = '\0';
      printf("%s\n", source);
    } else {
      printf("Panic: cannot open file\n");
    }

    fclose(fp);
  }

  free(source);

  return 0;
}
