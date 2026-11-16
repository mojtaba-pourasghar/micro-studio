<?php

namespace App\Console\Commands;

use App\Models\AgentToken;
use App\Support\Installer;
use Illuminate\Console\Command;

class InstallSite extends Command
{
    protected $signature = 'ms:install {--email= : Admin email} {--password= : Admin password (min 10 chars)} {--name=مدیر سایت} {--no-seed : Do not load the template content}';

    protected $description = 'Set up Micro Studio: migrate, load the template content, create the admin user and print an agent token';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Admin email');
        $password = $this->option('password') ?: $this->secret('Admin password (min 10 chars)');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen((string) $password) < 10) {
            $this->error('A valid email and a password of at least 10 characters are required.');

            return self::FAILURE;
        }
        foreach (Installer::run($this->option('name'), $email, $password, ! $this->option('no-seed')) as $line) {
            $this->line($line);
        }
        [$token, $plain] = AgentToken::issue('Claude');
        $this->newLine();
        $this->info('Claude connector URL (shown once):');
        $this->line(url('/mcp/'.$plain));

        return self::SUCCESS;
    }
}
