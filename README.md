# LearnHub.mk Backend API

- [Setup using Docker](#setup-using-docker)
- [Regular Setup](#regular-setup)
- [Packages](#packages)



### Setup using Docker
1. Install [Docker](https://docs.docker.com/engine/install/) and [Docker Compose](https://docs.docker.com/desktop/install/windows-install/).


> - Be mindful of which terminal you are using.
> - Tags matching the terminal are provided inside the documentation.
> > - [OS] OS Terminal - the terminal native to your operating system. Always available.
> > - [WSL] WSL Terminal - the terminal inside the virtual machine.
> > - [SAIL] Sail Terminal - the terminal available after the `sail shell` command is called.

2. [OS] Install the Linux distro 

        wsl --install -d Ubuntu-22.04

3. Enable Ubuntu from Docker Desktop
![enable ubuntu on docker desktop](https://i.postimg.cc/vYZRKKfL/docker-desktop-ubuntu-enable.jpg)

4. [OS] Connect to the WSL Distro Terminal 

         wsl --distribution Ubuntu-22.04


5. [OS] Clone the repository

        git clone https://github.com/learnhubmkd/api.git

6. [OS] Get into your project directory 

         cd api

7. Create an ```.env``` file by copying ```.env.example```
   - Windows:

          xcopy .env.docker.example .env /y /f
   - Linux/Mac/WSL:

          cp .env.docker.example .env
    
> - Note: The MySQL container will fail if the DB_PASSWORD value is empty
> - You can modify the `.env` file to test different configurations.

 

8. [WSL] Install sail dependencies
        
        docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs
> - You can copy the command from the official documentation on [Sail dependencies](https://laravel.com/docs/10.x/sail#installing-composer-dependencies-for-existing-projects)
9. [WSL] Build the docker containers

        ./vendor/bin/sail build
10. [WSL]  Run the containers

        ./vendor/bin/sail up -d
11. [WSL] Access the PHP container

        ./vendor/bin/sail shell

12. [SAIL] Generate an application key (`APP_KEY`)

        php artisan key:generate  
13. [SAIL] Run all migrations and database seeders

        php artisan migrate --seed
14. Access the site using [http://localhost:8000](http://localhost:8000) in your browser

### Regular Setup
0. Install [PHP 8.3](https://windows.php.net/download/) or later
> Currently, [XAMPP](https://www.apachefriends.org/download.html) contains PHP 8.2, and may not work.
1. Install [Composer 2.7.1](https://getcomposer.org/download/#manual-download) or later

2. Clone the repository

        git clone https://github.com/learnhubmkd/api.git
 
3. Get into your project directory

        cd api
4. Create an ```.env``` file by copying ```.env.example```
- Windows:

         xcopy .env.example .env /y /f
- Linux/Mac: 
     
         cp .env.example .env

> - You can modify the `.env` file to test different configurations.


5. Install composer dependencies

         composer install

6. Generate an application key (`APP_KEY`)

        php artisan key:generate  
7. Run all migrations and database seeders

        php artisan migrate --seed
8. Start the PHP server

        php artisan serve

9. Access the site using [http://localhost:8000](http://localhost:8000) in your browser


### Packages 

> - Laravel Socialite: [SOCIALITE.md](SOCIALITE.md)
> - Laravel Scribe: [SCRIBE.md](SCRIBE.md)
> - Laravel Modular: [MODULAR.md](MODULAR.md)
