# ![logo](https://clanaod.net/tracker/images/logo_v2.png) AOD Tracker v3

[![Tracker v3 CI](https://github.com/ClanAODDev/tracker_v3/actions/workflows/CI.yml/badge.svg)](https://github.com/ClanAODDev/tracker_v3/actions/workflows/CI.yml)

The AOD Tracker is a member and organizational unit management system. It is specifically built to support AOD
processes, and as such makes some assumptions about the characteristics of an organization:

- Games are divided into *divisions*
- Divisions consist of *platoons*, *Commanders* and *Executive officers*
- Platoons consist of *squads* and *platoon leaders*
- Squads consist of *members* and *squad leaders*

There are many other analytical tools built into the tracker to provide basic statistics about recruiting, retention,
and activity that derive from outside data.

The Tracker is considered a consumer of member data. The only concrete data it generates on its own are member notes for
historical purposes.

## Local Installation

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

# the example should contain enough to do local development
cp .env.example .env
```

#### Building the docker images

Laravel comes baked with `Sail`, an opinionated Docker configuration for Laravel applications. I have swapped out my custom Dockerfiles/compose files in place of this.

The tracker and website should not use conflicting database and web server ports.

```bash
sail up -d

sail artisan migrate:fresh \
&& sail artisan db:seed \
&& sail artisan db:seed --class=ClanSeeder
```

Create a user for yourself to authenticate as. Users must be associated with a member who has an active division.

Since we are using Sail for docker configuration, we can use it to interact with the container rather than exec'ing into it manually.

```shell
sail tinker
```

Then we need to provision an admin user for ourselves. The Tracker will automatically log into the first (and only) user.

```php
\App\Models\User::factory([
    'name' => 'Your name'
])->admin()->create();
```
