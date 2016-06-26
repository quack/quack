arr_program=(git phpcs)

for program in ${arr_program[@]} 
do
  condition=$(which $program 2>/dev/null | grep -v "not found" | wc -l)
  if [ $condition -eq 0 ] ; then
      echo "You are required to install '$program'"
      exit
  fi
done

git clone https://github.com/luizperes/pre-commit.git 
mv pre-commit/pre-commit ../../.git/hooks/pre-commit
chmod +x ../../.git/hooks/pre-commit
rm -rf pre-commit
