# OpenClassrooms Projet 8 - To Do List

## Introduction 
ToDo & Co is a PHP application built with Symfony that allows users to manage their daily tasks. The application was originally developed as a Minimum Viable Product (MVP) to showcase the concept to potential investors. With the recent funding, the aim is to improve the quality of the application and introduce new features.

## Features
In this OpenClassrooms project, you can manage tasks with an intuitive user interface.
User authentication system:
    Login, Register, Reset Password functionalities.
    Email verification system.
    Role-based access: Standard users (ROLE_USER) and Administrators (ROLE_ADMIN).
Ttask management:
    Each task is linked to a user.
    Only the creator of a task or an administrator can delete a task.
    Tasks without an explicit creator are linked to an "anonymous" user.
Admin features:
    User management: Create, Switch between admin and regular user, delete users.
    Only administrators (ROLE_ADMIN) can manage users.


## Installation
### Step1: Clone the project
`git clone https://github.com/your_username/todo-co.git`

`cd todo-co`

### Step2: Install dependencies
`composer install`

### Step3: Configure the environment variables
create a .env.local and be sure to input your database information. If you are using laragon, it should look like this
DATABASE_URL="mysql://Myusername:Mypassword@127.0.0.1:3306/Mydatabasename?serverVersion=5.7"

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