<p align="center"><a href="https://laravel.com" target="_blank"></a> <b>Project Instruction</b>
<p align="center" style="color: #136173">
In the case of the project, it can be said that the question and answer is held in the console at the same time as the question is created, the second developer can immediately answer the questions. To advance the object orientation of the work and also use the strategy design pattern for different types of questions that we may have in the future. 
</p>



## Services
- Nginx
- Redis
- Mysql
- PHP (8.1.12)
- PhpMyAdmin(mysql:8.0.13)
- Composer
- Npm
- Cron

## Installation
```sh
First Of all Please going to this url: https://github.com/abbassmortazavi/docker-laravel  clone my docker file after that continue :
step 1 : go to the docker-compose.yml and change all default directory in volums like this => ../TriviaGame
step 2 : docker-compose up -d --build
step 3 : if you want down all service use this command : docker-compose up -d down
```

## Composer update or Install
```sh
docker-compose run --rm composer i or in your root project use this command : composer i
docker-compose run --rm composer u
```

## Migrate
```sh
if you want migrate your all migrations please do this before migrate :
you have to config your server i mean you sholud find your mysql ip and set in your env file.
please doing all steps ;
step 1 : docker ps => this command show all run container with id we want mysql id this is my mysql id : 6f773f07c8e3
step 2 : docker inspect 6f773f07c8e3 => copy your mysql id after inspect after run this you access your mysql ip address like this => "IPAddress": "172.19.0.3"
step 3 : copy mysql ip address in env file => DATABASE_URL="mysql://root:root@172.19.0.3:3306/trivia_game"
step 4 : docker-compose exec php sh => this command cause you run all console command in php container
step 5 : php bin/console doctrine:database:create
step 6 : php bin/console doctrine:migrations:migrate
step 7 : php bin/console trivia:start => this command start game
```