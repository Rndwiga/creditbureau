<?php

namespace Rndwiga\CreditBureau\Console;

use Rndwiga\Writter\Model\WritterAuthor;
use Wink\WinkAuthor;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreditBureau:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database migrations for CreditBureau';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('migrate', [
            '--database' => config('CreditBureau.database_connection'),
            '--path' => 'vendor/rndwiga/credit_bureau/src/Database/Migrations',
        ]);
        $this->line('CreditBureau ai is ready for use.');

    }
}
