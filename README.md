# ![logo](https://clanaod.net/tracker/images/logo_v2.png) AOD Tracker v3

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

## Contributing

The Tracker is a large project that has been in development since 2015, and is the result of quite a few contributors over the years. If you have an interest in helping develop a feature, fix bugs, or even just provide general feedback, we welcomee you! 

The best way to contribute code changes is by way of a PR. First, make a fork of this repo, clone the fork to your local development environment, and follow the steps for local installation. Then when you are ready, submit a pull request of the changes from your fork. Please ensure you pay attention to the code style section, and explain any signiicant alterations your PR may make.

## Local Installation

#### Building the laravel environment

You will need to ensure, at a minimum, that you have [Docker](https://www.docker.com/) 
installed. If you don't have PHP installed (or don't want to), you can use the following `docker run` command to get 
things built.

```shell script
# installs application dependencies to allow you to run Sail
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

If you have the correct version of PHP installed, and you have composer, you can skip the `docker run` command, and
simply build it with Composer using `composer install`

```
# the example should contain enough to do local development
cp .env.example .env

# generate framework key
php artisan key:generate
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


### Enforcing code style
Everyone has their own preferences for how code should look. For cases where there are wild differences, we use `.
./vendor/bin/pint` to make things consistent throughout the repo. Feel free to run this before committing!

Eventually we'll add this to a GitHub Action so it happens automatically....
