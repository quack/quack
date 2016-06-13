#include <stdlib.h>
#include <string.h>

int main(int argc, char** argv)
{
  char out[200];
  char src[250];

  strcpy(out, "");
  strcpy(src, "php5 /quack/quack/src/repl/QuackRepl.php --ast");
  int i;
  for (i = 1; i < argc; i++)
  {
    strcat(out, " ");
    strcat(out, argv[i]);
  }

  strcat(src, out);
  system(src);
  return 0;
}

