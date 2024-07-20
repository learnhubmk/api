# LearnHub.mk API

- [Setup using Docker](#setup-using-docker)
- [Regular Setup](#regular-setup)
- [Packages](#packages)

### Setup using Docker
1. Install [Docker](https://docs.docker.com/engine/install/) and [Docker Compose](https://docs.docker.com/desktop/install/windows-install/).

> - Be mindful of which terminal you are using.
> - Tags matching the terminal are provided inside the documentation.
> > - [Windows Only] OS Terminal - the terminal native to Windows.
> > - [WSL] WSL Terminal - the terminal inside the virtual machine.
> > - [Linux / MacOS] OS Terminal - the terminal native to your operating system.

2. [Windows Only] Install the Linux distro 
```
wsl --install -d Ubuntu-22.04
```
3. Enable Ubuntu from Docker Desktop
![enable ubuntu on docker desktop](https://i.postimg.cc/vYZRKKfL/docker-desktop-ubuntu-enable.jpg)

4. [Windows Only] Connect to the WSL Distro Terminal 
```
 wsl --distribution Ubuntu-22.04
```
5. [WSL / Linux / MacOS] Go to your home directory, create a folder for your projects (if you don't have one already) and get into it:
```
cd  ~
mkdir Projects
cd Projects
```
6. [WSL / Linux / MacOS] Clone the repository
```
git clone git@github.com:learnhubmk/api.git
```
> - If you get the `permission denied (public key)` error while cloning the repository, you will have to [add your public SSH key to your GitHub SSH keys settings](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/adding-a-new-ssh-key-to-your-github-account).

7. [WSL / Linux / MacOS] Get into your project directory 
```
 cd api
```
8. [WSL / Linux / MacOS] Create an ```.env``` file by copying ```.env.docker.example```
```
cp .env.docker.example .env
```
9. [WSL / Linux / MacOS] Build up and start the Docker containers
```
docker compose up --build -d
```
10. [WSL / Linux / MacOS] Enter the docker `app` docker container
```
docker exec -it LearnHub_app sh 
```
11. [Docker Container] Install the Composer dependencies
```
composer install
```
12. [Docker Container] Run all migrations and database seeders
```
php artisan migrate --seed
```
13. [Docker Container] Generate the application key (`APP_KEY`)
```
php artisan key:generate
```
14. Access the site using [http://localhost:8000](http://localhost:8000) in your browser

15. Additional useful links that you may want to know about:
- [The Laravel API](http://localhost:8000) - the Laravel API project
- [Mailpit](http://localhost:8025) - the email client, all emails will come here when the API sends an email
- [Laravel Horizon](http://localhost:8000/horizon) - The queue monitoring system for Redis queues
- [Laravel Telescope](http://localhost:8000/telescope) - The monitoring system for your Laravel API, you can debug everything here.
- [Open API v3 Documentation](http://localhost:8000/docs) - the Open API v3 documentation for the project.
- [PHPMyAdmin](http://localhost:8080) - the famous database UI.

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

For the local emails that the API may send, you may want to use something like [Mailtrap](https://mailtrap.io)

For the database UI, you can use PHPMyAdmin, TablePlus, HeidiSQL, or MySQL Workbench.

### Packages 

> - Laravel Socialite: [SOCIALITE.md](SOCIALITE.md)
> - Laravel Scribe: [SCRIBE.md](SCRIBE.md)
> - Laravel Modular: [MODULAR.md](MODULAR.md)
