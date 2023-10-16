# Contributing

## How to contribute
step 1 : clone the project
step 1.5 : fetch the origin and pull the master branch (to make sure that you have the latest version)
step 2 : create a branch
step 3 : make your changes
step 4 : push your branch
step 5 : create a pull request

If your changes are accepted you will be able to merge your branch with the master branch.

## Requirements for a valid pull request
Test coverage: your test must cover the new features you added. To test for coverage, it is recommended to use the following command:

./vendor/bin/phpunit --colors --testdox --coverage-html coverage-report

this way you can determine the coverage on top of being sure that all your tests pass.

## Code quality
Your code must be clean and follow the PSR-12 standard.
follow the documentation here
https://www.php-fig.org/psr/psr-12/

As a general rule, your code should be easy to read and understand. You should refactor it to avoid too much duplication. If you are not sure about the quality of your code, remember that you can always ask for help and advice. 