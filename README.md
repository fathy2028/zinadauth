# zinadauth

## Install the project

1. clone the project.
2. cd to the root directory.
3. copy the .env.example file (in the root directory) and fill it.
```shell
cp .env.example .env
```
4. copy the .env.example file (in the backend directory) and fill it.
```shell
cd backned

cp .env.example .env
```
5. install composer dependencies.
```shell
composer install
```
6. build and up the docker images.
```shell
docker-compose build

docker-compose up -d
```
7. run the application:
  - the backend: [http://localhost:8000](http://localhost:8000)
  - the frontend: [http://localhost:4200](http://localhost:4200)