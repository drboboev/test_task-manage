TEST APP || TASK MANAGE
==================================

# Install #

````
$ git clone https://github.com/drboboev/test_task-manage app
$ cd app
$ composer install
````
Edit ````DATABASE_URL```` in ````.env```` file.

Then run:
````
$ php bin/console doctrine:schema:create
$ php bin/console doctrine:fixtures:load
````
If you don't have webserver on your machine, run:
````
$ composer require server
$ php bin/console server:run
````

# How to run #

Open ````http://localhost:8000```` in your browser.
