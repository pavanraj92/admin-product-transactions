<?php

namespace admin\product_transactions;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductTransactionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes, views, migrations from the package  
        $this->loadViewsFrom([
            base_path('Modules/Transactions/resources/views'), // Published module views first
            resource_path('views/admin/transaction'), // Published views second
            __DIR__ . '/../resources/views'      // Package views as fallback
        ], 'transaction');

        // Load published module config first (if it exists), then fallback to package config
        if (file_exists(base_path('Modules/Transactions/config/transaction.php'))) {
            $this->mergeConfigFrom(base_path('Modules/Transactions/config/transaction.php'), 'transaction.constants');
        } else {
            // Fallback to package config if published config doesn't exist
            $this->mergeConfigFrom(__DIR__ . '/../config/transaction.php', 'transaction.constants');
        }

        // Also register module views with a specific namespace for explicit usage
        if (is_dir(base_path('Modules/Transactions/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/Transactions/resources/views'), 'transactions-module');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Also load migrations from published module if they exist
        if (is_dir(base_path('Modules/Transactions/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Transactions/database/migrations'));
        }

        // Only publish automatically during package installation, not on every request
        // Use 'php artisan products:publish' command for manual publishing
        // $this->publishWithNamespaceTransformation();

        // Standard publishing for non-PHP files
        $this->publishes([
            __DIR__ . '/../config/' => base_path('Modules/Transactions/config/'),
            __DIR__ . '/../database/migrations' => base_path('Modules/Transactions/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/Transactions/resources/views/'),
        ], 'transaction');

        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();

        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                // Load routes from published module first, then fallback to package
                if (file_exists(base_path('Modules/Transactions/routes/web.php'))) {
                    $this->loadRoutesFrom(base_path('Modules/Transactions/routes/web.php'));
                } else {
                    $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
                }
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\product_transactions\Console\Commands\PublishProductTransactionsModuleCommand::class,
                \admin\product_transactions\Console\Commands\CheckModuleStatusCommand::class,
                \admin\product_transactions\Console\Commands\DebugProductTransactionsCommand::class,
            ]);
        }
    }

    /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        // Define the files that need namespace transformation
        $filesWithNamespaces = [
            // Controllers
            __DIR__ . '/../src/Controllers/TransactionManagerController.php' => base_path('Modules/Transactions/app/Http/Controllers/Admin/TransactionManagerController.php'),

            // Models
            __DIR__ . '/../src/Models/Transaction.php' => base_path('Modules/Transactions/app/Models/Transaction.php'),

            // Routes
            __DIR__ . '/routes/web.php' => base_path('Modules/Transactions/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                // Create destination directory if it doesn't exist
                File::ensureDirectoryExists(dirname($destination));

                // Read the source file
                $content = File::get($source);

                // Transform namespaces based on file type
                $content = $this->transformNamespaces($content, $source);

                // Write the transformed content to destination
                File::put($destination, $content);
            }
        }
    }

    /**
     * Transform namespaces in PHP files
     */
    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\product_transactions\\Controllers;' => 'namespace Modules\\Transactions\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\product_transactions\\Models;' => 'namespace Modules\\Transactions\\app\\Models;',

            // Use statements transformations
            'use admin\\product_transactions\\Controllers\\' => 'use Modules\\Transactions\\app\\Http\\Controllers\\Admin\\',
            'use admin\\product_transactions\\Models\\' => 'use Modules\\Transactions\\app\\Models\\',

            // Class references in routes
            'admin\\product_transactions\\Controllers\\TransactionManagerController' => 'Modules\\Transactions\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    /**
     * Transform controller-specific namespaces
     */
    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\product_transactions\\Models\\Transaction;',
            'use Modules\\Transactions\\app\\Models\\Transaction;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        // Any model-specific transformations
        $content = str_replace(
            'use admin\\products\\Models\\Order;',
            'use Modules\\Products\\app\\Models\\Order;',
            $content
        );
        $content = str_replace(
            'use admin\\users\\Models\\User;',
            'use Modules\\Users\\app\\Models\\User;',
            $content
        );
        return $content;
    }

    /**
     * Transform request-specific namespaces
     */
    protected function transformRequestNamespaces($content)
    {
        // Any request-specific transformations
        return $content;
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\product_transactions\\Controllers\\TransactionManagerController',
            'Modules\\Transactions\\app\\Http\\Controllers\\Admin\\TransactionManagerController',
            $content
        );

        return $content;
    }
}