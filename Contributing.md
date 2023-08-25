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

## Autentication process
### Login process
This happens in LoginController.php. You must use the template in security/login.html.twig. The form is already created and the controller is already set up. Note that src\Controller\LoginController.php is only there to provide the template. The real logic of the login comes form vendor\symfony\security-http\Authentication\AuthenticationUtils.php This dependency is injected in the index function of LoginController.php. 

### User storage
When you register, you use the src\Controller\RegistrationController.php and create a new src\Entity\User.php. This entity has its method used to store the information, for instance:

$user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

This is used to hash the password using the UserPasswordHasherInterface, which means that instead that having a plain text password in the database, you will have a hashed password. This is a an important security measure because if for some reason your database gets hacked, the hacker will not be able to see the actual password of the users.

The user is saved when the $entityManager->flush(); is called. If you want to see the user you created, you need to go to the database (using heidiSQL for instance) and look for the user table. You will see the user with all the information you provided.

It should look like this:

user
---
|  |  |  |  |  |  |  | 
| ---: | --- | --- | --- | ---: | --- | --- | 
| 67 | anonymous@gmail.com | ["ROLE_USER"] | $2y$13$slPqCRSJzU7ITw2K.9USI.zes298ofakeencodedpasswordmsQXO | 1 | John Doe | https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png | 
