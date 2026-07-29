<?php

namespace App\Providers;

use App\Models\Submission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGates();
    }

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

    protected function configureGates(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->role === 'admin');
        Gate::define('dosen', fn (User $user): bool => $user->role === 'dosen');
        Gate::define('mahasiswa', fn (User $user): bool => $user->role === 'mahasiswa');
        Gate::define('download-submission', function (User $user, Submission $submission): bool {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'dosen') {
                return $submission->schedule()->whereHas('dosens', fn ($query) => $query->where('users.id', $user->id))->exists()
                    || $submission->user_id === $user->id;
            }

            return $submission->user_id === $user->id;
        });
    }
}
