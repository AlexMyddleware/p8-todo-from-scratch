# OpenClassrooms Projet 8 - To Do List

## Introduction 
ToDo & Co is a PHP application built with Symfony that allows users to manage their daily tasks. The application was originally developed as a Minimum Viable Product (MVP) to showcase the concept to potential investors. With the recent funding, the aim is to improve the quality of the application and introduce new features.

## Features
In this OpenClassrooms project, you can manage tasks with an intuitive user interface.
User authentication system:
    Login, Register, Reset Password functionalities.
    Email verification system.
    Role-based access: Standard users (ROLE_USER) and Administrators (ROLE_ADMIN).
Task management:
    Each task is linked to a user.
    Only the creator of a task delete a task.
    Tasks without an explicit creator can linked to an "anonymous" user via the command :anonymous-tasks.
    Only a verified user can access the tasks.
Admin features:
    User management: Create, Switch between admin and regular user, delete users.
    Only administrators (ROLE_ADMIN) can manage users.


## Installation
### Step1: Clone the project
`git clone https://github.com/AlexMyddleware/p8-todo-from-scratch.git`

go to the project folder

### Step2: Install dependencies
`composer install`

### Step3: Configure the environment variables
create a .env.local and be sure to input your database information. If you are using laragon, it should look like this
DATABASE_URL="mysql://Myusername:Mypassword@127.0.0.1:3306/Mydatabasename?serverVersion=5.7"
You need to configure you mailer dsn, here is what it should look like: 
MAILER_DSN=smtp://myfakeusername:myfakepassword@smtp.mailtrap.io:2525?encryption=tls&auth_mode=login

### Step4: Create the database
use the commands
`php bin/console doctrine:database:drop --force`
`php bin/console doctrine:database:create`
`php bin/console doctrine:schema:update --force --complete`

### Step5: Load the fixtures
`php bin/console doctrine:fixtures:load --no-interaction`
`php bin/console doctrine:fixtures:load --no-interaction --env=test`

### Step6: Run the tests
`./vendor/bin/phpunit --colors --testdox`

### Step7: Run the anonymous tasks command
`php bin/console app:anonymous-tasks`

## Usage
You can create a new user by clicking on the register button. You will receive an email to verify your account. You can then login and create tasks.
If you are an admin user, you can assign the admin role when you create a new user and switch other users between admin and regular user. You can also delete users.
You need to be verified before you can do anything related to the tasks, so don't forget to check your emails.



## Useful commands

#### Coverage report
`./vendor/bin/phpunit --colors --testdox --coverage-html coverage-report`

### Run localized test
`./vendor/bin/phpunit tests/Controller/TaskControllerTest.php --testdox`

### Run the infection test
`php infection.phar`