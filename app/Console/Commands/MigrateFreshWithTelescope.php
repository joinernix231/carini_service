<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateFreshWithTelescope extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:fresh-telescope {--seed : Indicates if the seed task should be re-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta migrate:fresh y luego las migraciones de Telescope automáticamente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Ejecutando migrate:fresh...');
        $this->call('migrate:fresh');

        $this->info('📊 Ejecutando migraciones de Telescope...');
        
        // Ejecutar las migraciones de Telescope desde el vendor
        $telescopeMigrationsPath = base_path('vendor/laravel/telescope/database/migrations');
        
        if (is_dir($telescopeMigrationsPath)) {
            $this->call('migrate', [
                '--path' => 'vendor/laravel/telescope/database/migrations',
                '--force' => true,
            ]);
            
            $this->info('✅ Migraciones de Telescope ejecutadas correctamente.');
        } else {
            $this->warn('⚠️ No se encontraron las migraciones de Telescope en: ' . $telescopeMigrationsPath);
            $this->info('💡 Intentando publicar migraciones de Telescope...');
            $this->call('vendor:publish', [
                '--tag' => 'telescope-migrations',
                '--force' => true,
            ]);
            
            // Intentar ejecutar las migraciones publicadas
            $this->call('migrate', [
                '--force' => true,
            ]);
        }

        if ($this->option('seed')) {
            $this->info('🌱 Ejecutando seeders...');
            $this->call('db:seed', ['--force' => true]);
        }

        $this->info('✅ ¡Proceso completado! Telescope está listo para usar.');
        
        return Command::SUCCESS;
    }
}

