<?php

namespace App\Providers;

use App\Models\User;
use App\Support\SupabaseS3Client;
use Carbon\CarbonImmutable;
use Illuminate\Filesystem\AwsS3V3Adapter as LaravelS3Adapter;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter as FlysystemS3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Visibility;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::define('manage-kamus', fn (User $user): bool => $user->isAdmin());

        Storage::extend('supabase-s3', function (Application $app, array $config): LaravelS3Adapter {
            foreach (['key', 'secret', 'region', 'bucket', 'endpoint'] as $requiredSetting) {
                if (blank($config[$requiredSetting] ?? null)) {
                    throw new InvalidArgumentException(
                        'Configure Supabase S3 endpoint, region, bucket, and generated access keys.',
                    );
                }
            }

            $s3Config = $config + ['version' => 'latest'];
            $s3Config['credentials'] = array_filter([
                'key' => $config['key'],
                'secret' => $config['secret'],
                'token' => $config['token'] ?? null,
            ], fn (mixed $value): bool => $value !== null);
            $s3Config['use_path_style_endpoint'] = true;

            $client = new SupabaseS3Client($s3Config);
            $adapter = new FlysystemS3Adapter(
                $client,
                $s3Config['bucket'],
                (string) ($s3Config['root'] ?? ''),
                new PortableVisibilityConverter($config['visibility'] ?? Visibility::PRIVATE),
                null,
                $config['options'] ?? [],
                $s3Config['stream_reads'] ?? false,
            );
            $flysystem = new Flysystem($adapter, Arr::only($config, [
                'directory_visibility',
                'disable_asserts',
                'retain_visibility',
                'temporary_url',
                'url',
                'visibility',
            ]));

            return new LaravelS3Adapter($flysystem, $adapter, $s3Config, $client);
        });

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
