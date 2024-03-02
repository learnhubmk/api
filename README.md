# LearnHub.mk Backend API

#### Laravel Sail (Docker) installation
1. Install Docker and Docker Compose for the operating system of your choice.
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

