# LearnHub.mk Backend API

### First Time Setup
1. Install [Docker](https://docs.docker.com/engine/install/) and [Docker Compose](https://docs.docker.com/desktop/install/windows-install/).


> - Be mindful of which terminal you are using.
> - Tags matching the terminal are provided inside the documentation.
> > - [OS] OS Terminal - the terminal native to your operating system.
> > - [WSL] WSL Terminal - the terminal inside the virtual machine.

2. [OS] Install the Linux distro 
> `wsl --install -d Ubuntu-22.04`

3. Enable Ubuntu from Docker Desktop
![enable ubuntu on docker desktop](https://i.postimg.cc/vYZRKKfL/docker-desktop-ubuntu-enable.jpg)
4. [OS] Connect to the WSL Distro Terminal 
>  `wsl --distribution Ubuntu-22.04`

### Regular Setup
2. [OS] Clone the repository
> ```git clone https://github.com/learnhubmkd/api.git```
3. [OS] Get into your project directory 
> ```cd api```
4. [OS] Create a local environment `.env` file 
> - `cp .env.example .env`
> - You can modify the `.env` file to test different configurations.
> - Related: [Scribe Socialite](/.scribe/SCRIBE.md)
5. [WSL] Install sail dependencies
> `docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs`
> - You can copy the command from the official documentation on [Sail dependencies](https://laravel.com/docs/10.x/sail#installing-composer-dependencies-for-existing-projects)
5. [WSL] Build the docker containers
> `./vendor/bin/sail build`
6. [WSL]  Run the containers
> `./vendor/bin/sail up -d`
7. [WSL] Access the PHP container 
> `./vendor/bin/sail shell`
8. [WSL] Generate an application key (`APP_KEY`)
> `php artisan key:generate`  
9. [WSL] Run all migrations and database seeders
> `php artisan migrate` 
10. Access the site using [learnhub.test:8000](http://learnhub.test:8000) in your browser
