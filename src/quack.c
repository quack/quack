#include <stdlib.h>

int main(int argc, char** argv)
{
  chdir("../");
  system("make repl mode=ast");
  return 0;
}
