# ![logo](https://clanaod.net/tracker/images/logo_v2.png) AOD Tracker v3

[![CI](https://github.com/ClanAODDev/tracker_v3/actions/workflows/laravel.yml/badge.svg?branch=main)](https://github.com/ClanAODDev/tracker_v3/actions/workflows/laravel.yml)

The AOD Tracker is a member and organizational unit management system. It is specifically built to support AOD processes, and as such makes some assumptions about the characteristics of an organization:

- Games are divided into *divisions*
- Divisions consist of *platoons*, *Commanders* and *Executive officers*
- Platoons consist of *squads* and *platoon leaders*
- Squads consist of *members* and *squad leaders*

There are many other analytical tools built into the tracker to provide basic statistics about recruiting, retention, and activity that derive from outside data.

The Tracker is considered a consumer of member data. The only concrete data it generates on its own are member notes for historical purposes.

## Local Installation

There is a docker configuration provided to create a basic ngnix, mysql stack. You must have a pre-seeded copy of the production database to have a fully-operational copy of the tracker (for now). You will also need to follow Laravel's project guidelines.

#### Building the laravel environment

```shell script
# install php depdencies
~ $ composer install

# install front-end dependencies
~ $ npm install

# compile front-end assets
~ $ gulp

# generate framework key
~ $ php artisan key:generate

# configure as necessary
~ $ cp .env.example .env
```

#### Building the docker images

```shell script
~ $ cd .docker
~ $ docker-compose up -d
```
