## Table of Contents

1. [General info](#general-info)
2. [Technologies](#technologies)
3. [Project Team](#project-team)
4. [Installation](#installation)

## General info

This project is the backend part of the tracking tutoring project.

## Technologies

* [Laravel](https://laravel.com/): Version 10.8 

## Project Team

- [Henry Ibrahima Vincent Coly](https://gitlab.com/VICH7)

## Installation

- Clone project with: `git clone https://github.com/tracking-tutoring/tracktu-back.git`
- Run on your cmd or terminal : `composer install`
- Create an __env__ file and copy everything in the __.env.example__ file into the __.env__ file.
Open your __.env__ file and change the database name (__DB_DATABASE__) to whatever you have, username (__DB_USERNAME__) and password (__DB_PASSWORD__) field correspond to your configuration.
- Run: `php artisan key:generate`
- Run: `npm install`
- Run: `php artisan migrate`
- Run: `php artisan db:seed`
- Run: `php artisan serve`