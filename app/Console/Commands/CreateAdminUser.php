<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {email? : The email address of the admin} 
                            {--name= : The full name of the admin} 
                            {--password= : The admin password (omit to prompt securely or auto-generate)} 
                            {--phone= : Optional contact phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely create a new production admin account without overwriting existing accounts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Enter admin email', env('ADMIN_EMAIL', 'admin@vaishnavitours.com'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: [{$email}]");

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->warn("User with email [{$email}] already exists (Role: {$existing->role}). Creation aborted to prevent overwrite.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: $this->ask('Enter admin name', 'Vaishnavi Tours Admin');
        $phone = $this->option('phone') ?: null;

        $password = $this->option('password');
        if (empty($password)) {
            if ($this->input->isInteractive()) {
                $password = $this->secret('Enter admin password');
            }
            if (empty($password)) {
                $password = Str::random(16);
                $this->info('Auto-generated temporary password for admin (displaying once):');
                $this->line($password);
            }
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'admin',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->info("Admin user [{$email}] created successfully.");

        return self::SUCCESS;
    }
}
