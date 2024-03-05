# LearnHub.mk Backend API

#### Laravel Sail (Docker) installation
1.0. Install Docker and Docker Compose for the operating system of your choice.
### The following 3 steps apply only if you do Laravel setup for the first time. Otherwise, jump to point 2.
  1.1. Install Linux distro
        ```wsl --install -d Ubuntu-22.04```
  1.2. Enable Ubuntu from Docker Desktop, see screenshot
  ![enable ubuntu on docker desktop](https://i.postimg.cc/vYZRKKfL/docker-desktop-ubuntu-enable.jpg)
  1.3. Use the newly installed terminal from Ubuntu, or SSH into Ubutu from other terminal,such as Windows Terminal etc. 
  Then run the curl command:

        ````curl -s https://laravel.build/example-app | bash```

2. Get into your project directory (`cd api`)
3. Build the docker containers using `./vendor/bin/sail build`
4. Run the containers using `./vendor/bin/sail up -d`
5. Access the PHP container using `./vendor/bin/sail shell`
6. Run `composer install` to install of the composer dependencies.
7. Rename the docker example `.env` file using `cp .env.example .env`
8. Run `php artisan key:generate` to generate an application key (`APP_KEY`)
9. Run `php artisan migrate` to run all migrations and database seeders
10. Access the site using `learnhub.test:8000` in your browser

#### Installation (without Docker)
0. Install the neccessary software that Laravel requires in order to use them (check their documentation respectively)
1. Clone the repository
2. Get into your project directory (`cd api`)
3. Run `composer install` to install of the composer dependencies.
4. Rename the docker example `.env` file using `cp .env.example .env`
5. Run `php artisan key:generate` to generate an application key (`APP_KEY`)
6. Run `php artisan migrate --seed` to run all migrations and database seeders
7. Run `php artisan serve` to start the PHP server.
8. Access the site using `localhost:8000` in your browser


#### Working with Scribe API Documentation

Access the documentation:

- With Docker:
  - `learnhub.test:8000/docs`
- Without Docker
  - `localhost:8000/docs`

In order to generate documentation for APIs use: `php artisan scribe:generate`

