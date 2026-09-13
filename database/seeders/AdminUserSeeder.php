<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Create (or repair) the administrator account.
 *
 * Safe to re-run: when the email already exists the account is promoted to
 * `admin` and its password is reset, so this doubles as the recovery path when
 * nobody can sign in.
 *
 * Configuration — all optional:
 *   ADMIN_NAME      default "LMS Administrator"
 *   ADMIN_EMAIL     default "admin@ctu-lms.dev"
 *   ADMIN_PASSWORD  when absent a random one is generated and printed
 *
 * Usage:
 *   php artisan db:seed --class=AdminUserSeeder
 *   ADMIN_EMAIL=me@example.com ADMIN_PASSWORD='…' php artisan db:seed --class=AdminUserSeeder
 *
 * On Docker:
 *   docker compose exec -T backend php artisan db:seed --class=AdminUserSeeder
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) (env('ADMIN_EMAIL') ?: 'admin@ctu-lms.dev');
        $name = (string) (env('ADMIN_NAME') ?: 'LMS Administrator');

        $password = (string) env('ADMIN_PASSWORD');
        $generated = false;

        if ($password === '') {
            // No fallback default on purpose. A published, known password for a
            // privileged account on a public deployment is worse than having no
            // admin at all — so generate one and hand it to whoever ran this.
            $password = Str::password(20, true, true, false);
            $generated = true;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->forceFill([
                'name' => $existing->name ?: $name,
                'password' => Hash::make($password),
                'role' => 'admin',
            ])->save();

            $user = $existing;
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
            ]);
        }

        // No `students` row on purpose: that row belongs to the student role, and
        // AuthController::register only creates one when role === 'student'.

        $this->command->info(($existing ? 'Updated existing account to' : 'Created') . ' admin:');
        $this->command->line('  id:       ' . $user->id);
        $this->command->line('  email:    ' . $user->email);
        $this->command->line('  role:     ' . $user->role);

        if ($generated) {
            $this->command->warn('  password: ' . $password);
            $this->command->warn('  Generated just now — store it, then change it after signing in.');
        } else {
            $this->command->line('  password: (taken from ADMIN_PASSWORD)');
        }
    }
}
