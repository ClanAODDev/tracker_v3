<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Listeners\CheckApplicationHealth;
use App\Models\Division;
use App\Models\Member;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Settings\UserSettings;
use Filament\Actions\Action;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Node\Block\Paragraph;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Paginator::useBootstrap();

        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class . '@handle');
        Event::listen(DiagnosingHealth::class, CheckApplicationHealth::class);

        Route::bind('division', function ($division) {
            return Division::where('slug', strtolower($division))->first();
        });

        Route::bind('username', fn ($username) => User::whereName($username)->firstOrFail());

        Route::model('member', Member::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureMarkdown();

        if (! class_exists(\Filament\Tables\Actions\Action::class)) {
            class_alias(Action::class, \Filament\Tables\Actions\Action::class);
        }

        $this->app->singleton(UserSettings::class, fn () => auth()->user()->settings());

        $this->app->bind(\Filament\Auth\Http\Responses\Contracts\LogoutResponse::class, LogoutResponse::class);

        if ($this->app->environment('local', 'testing')) {
            $this->app->register(FakerServiceProvider::class);
        }
    }

    private function configureMarkdown(): void
    {
        $this->app->singleton('markdown.environment', function (Container $app): Environment {
            $config = $app['config']->get('markdown');

            $config['embed']['adapter']                     = new OscaroteroEmbedAdapter;
            $config['default_attributes'][Paragraph::class] = [
                'class' => static fn (Paragraph $paragraph) => $paragraph->previous() instanceof Heading && $paragraph->previous()->getLevel() === 1 ? 'lead' : null,
            ];

            $environment = new Environment(Arr::except($config, ['extensions', 'views']));

            foreach ((array) Arr::get($config, 'extensions') as $extension) {
                $environment->addExtension($app->make($extension));
            }

            return $environment;
        });
    }
}
