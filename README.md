# LearnHub.mk Backend API

#### Laravel Sail (Docker) installation
1.0. Install Docker and Docker Compose for the operating system of your choice.
### The following 3 steps apply only if you do Laravel setup for the first time. Otherwise, jump to point 2.
  1.1. Install Linux distro
    
    wsl --install -d Ubuntu-22.04
        
  1.2. Enable Ubuntu from Docker Desktop, see screenshot
  ![enable ubuntu on docker desktop](https://i.postimg.cc/vYZRKKfL/docker-desktop-ubuntu-enable.jpg)
  1.3. Use the newly installed terminal from Ubuntu, or SSH into Ubutu from other terminal,such as Windows Terminal etc....
   Then run the curl command:

    curl -s https://laravel.build/example-app | bash

2. Clone the repository
3. Get into your project directory (`cd api`)
4. Build the docker containers using `./vendor/bin/sail build`
5. Run the containers using `./vendor/bin/sail up -d`
6. Access the PHP container using `./vendor/bin/sail shell`
7. Run `composer install` to install of the composer dependencies.
8. Rename the docker example `.env` file using `cp .env.example .env`
9. Run `php artisan key:generate` to generate an application key (`APP_KEY`)
10. Run `php artisan migrate` to run all migrations and database seeders
11. Access the site using `learnhub.test:8000` in your browser

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

#### Working with Modular Structure

For documentation and reference use the following package:

https://github.com/InterNACHI/modular?tab=readme-ov-file

Initially our system has 3 modules:
- `Website` - everything related to the website will be here (blog, contact, newsletter etc.)
- `Platform` - The members area
- `Admin` - The admin area

To work with `artisan` commands for each module check the documentation:

https://github.com/InterNACHI/modular?tab=readme-ov-file#commands

Note: All modules are placed under `app-modules` folder
#### Working with Socialite 

##### Github

- Go to https://github.com/settings/developers and create new application. 
- You need to provide app name, callback url and website name. 
- Callback url should be the redirect url to your application for e.g. `localhost/login/github`
- From the dashboard get the `APP ID` and paste into `GITHUB_CLIENT_ID`, and `APP SECRET` into `GITHUB_CLIENT_SECRET`. 
- `GITHUB_REDIRECT_URI` should be the callback url provided in previous step. 

Developer Guide:
https://socialiteproviders.com/GitHub/#installation-basic-usage

https://laravel.com/docs/10.x/socialite#configuration

##### Google

- Go to https://console.cloud.google.com/projectcreate?pli=1 and create new project. 
- Go to  https://console.cloud.google.com/apis/credentials/consent and choose External. Fill the required data and 
after that go to https://console.cloud.google.com/apis/credentials to get the credentials. 
- Click on `Create 
Credentials` and from the popup choose `OAuth clientID`. 
- Set the application type to `Web application` and set the 
`Authorized Redirect URL` to your redirect url for e.g. `localhost/login/google`. 
- Next copy the `CLIENT ID` and 
paste into `GOOGLE_CLIENT_ID` and `CLIENT SECRET` into `GOOGLE_CLIENT_SECRET`. `GOOGLE_REDIRECT_URI` should be the 
same as your Authorized Redirect URL. 

Developers Guide:
https://socialiteproviders.com/Google-Plus/

##### LinkedIn

- Go to https://www.linkedin.com/developers/apps and create new application.
- Fill the form with the requested data
- Provide your callback url for e.g. `localhost/login/linkedin`
- Copy `Client ID` and paste it into `LINKEDIN_CLIENT_ID`and `Client Secret` into `LINKEDIN_CLIENT_SECRET`.
- `LINKEDIN_REDIRECT_URI` should be the redirect url provided in previous step.
- Go to Products and select `Sign In with LinkedIn`

Developers Guide:
https://socialiteproviders.com/LinkedIn/#linkedin


