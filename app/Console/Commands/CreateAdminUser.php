<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'krishihat:create-admin {email? : Admin email address} {--password= : Admin password} {--name= : Admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision or promote a user to administrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Enter admin email address', 'admin@demo.krishihat.test');
        $name = $this->option('name') ?: 'সিস্টেম অ্যাডমিন (System Administrator)';
        $password = $this->option('password') ?: 'Password123!';

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->role = 'admin';
            if ($this->option('password')) {
                $user->password = Hash::make($password);
            }
            $user->save();
            $this->info("User [{$email}] promoted to administrator.");
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $user->role = 'admin';
            $user->save();
            $this->info("Administrator account created for [{$email}].");
        }

        return self::SUCCESS;
    }
}
