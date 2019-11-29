<?php

namespace Rndwiga\CreditBureau\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreditBureau:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the CreditBureau resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing CreditBureau Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'CreditBureau-config']);
        $this->info('CreditBureau configs published.');

        $this->comment('Publishing CreditBureau env variables...');
        $this->addVariables();
        $this->info(count(config('CreditBureau.environment_configs'))." ::env variables added for CreditBureau");
    }


    private function addVariables(){
        $envFilePath = app()->environmentFilePath();
        $contents = file_get_contents($envFilePath);
        foreach (config('CreditBureau.environment_configs') as $key => $value) {
            //TODO:: There is a bug here when it comes to updating existing existing keys. It only updates the first one
            if ($oldValue = $this->getOldValue($contents, $key)) {
                $contents = str_replace("{$key}={$oldValue}", "{$key}={$value}", $contents);
                $this->writeFile($envFilePath, $contents);
                return $this->info("Environment variable with key '{$key}' has been changed from '{$oldValue}' to '{$value}'");
            }
            $contents = $contents . "\n{$key}={$value}";
            $this->writeFile($envFilePath, $contents);
        }
    }


    /**
     * Overwrite the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    protected function writeFile(string $path, string $contents): bool
    {
        $file = fopen($path, 'w');
        fwrite($file, $contents);
        return fclose($file);
    }

    /**
     * Get the old value of a given key from an environment file.
     *
     * @param string $envFile
     * @param string $key
     * @return string
     */
    protected function getOldValue(string $envFile, string $key): string
    {
        // Match the given key at the beginning of a line
        preg_match("/^{$key}=[^\r\n]*/m", $envFile, $matches);
        if (count($matches)) {
            return substr($matches[0], strlen($key) + 1);
        }
        return '';
    }
}
