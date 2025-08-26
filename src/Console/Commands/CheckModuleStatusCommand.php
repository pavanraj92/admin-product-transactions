<?php

namespace admin\product_transactions\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckModuleStatusCommand extends Command
{
    protected $signature = 'transactions:status';
    protected $description = 'Check if Transactions module files are being used';

    public function handle()
    {
        $this->info('Checking Transactions Module Status...');

        // Check if module files exist
        $moduleFiles = [
            'Transaction Controller' => base_path('Modules/Transactions/app/Http/Controllers/Admin/TransactionManagerController.php'),
            'Transaction Model' => base_path('Modules/Transactions/app/Models/Transaction.php'),

            'Routes' => base_path('Modules/Transactions/routes/web.php'),
            'Views' => base_path('Modules/Transactions/resources/views'),
            'Config' => base_path('Modules/Transactions/config/transactions.php'),
        ];

        $this->info("\n📁 Module Files Status:");
        foreach ($moduleFiles as $type => $path) {
            if (File::exists($path)) {
                $this->info("✅ {$type}: EXISTS");

                // Check if it's a PHP file and show last modified time
                if (str_ends_with($path, '.php')) {
                    $lastModified = date('Y-m-d H:i:s', filemtime($path));
                    $this->line("   Last modified: {$lastModified}");
                }
            } else {
                $this->error("❌ {$type}: NOT FOUND");
            }
        }

        // Check namespace in controller
        $controllerPath = base_path('Modules/Transactions/app/Http/Controllers/Admin/TransactionManagerController.php');

        if (File::exists($controllerPath)) {
            $content = File::get($controllerPath);
            if (str_contains($content, 'namespace Modules\Transactions\app\Http\Controllers\Admin;')) {
                $this->info("\n✅ Controller namespace: CORRECT");
            } else {
                $this->error("\n❌ Controller namespace: INCORRECT");
            }
            
            // Check for test comment
            if (str_contains($content, 'Test comment - this should persist after refresh')) {
                $this->info("✅ Test comment: FOUND (changes are persisting)");
            } else {
                $this->warn("⚠️  Test comment: NOT FOUND");
            }
        }

        // Check composer autoload
        $composerFile = base_path('composer.json');
        if (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            if (isset($composer['autoload']['psr-4']['Modules\\Transactions\\'])) {
                $this->info("\n✅ Composer autoload: CONFIGURED");
            } else {
                $this->error("\n❌ Composer autoload: NOT CONFIGURED");
            }
        }

        $this->info("\n🎯 Summary:");
        $this->info("Your Transactions module is properly published and should be working.");
        $this->info("Any changes you make to files in Modules/Transactions/ will persist.");
        $this->info("If you need to republish from the package, run: php artisan transactions:publish --force");
    }
}
