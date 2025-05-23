<?php

namespace E3DevelopmentSolutions\QuickBooks\Tests\TestHelpers;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;

class TestMigrationRepository extends DatabaseMigrationRepository
{
    /**
     * Get all of the migration files.
     *
     * @return array
     */
    public function getMigrationFiles($path)
    {
        $files = parent::getMigrationFiles($path);
        
        // Filter out the add_quickbooks_fields_to_users_table migration
        return array_filter($files, function ($file) {
            return !str_contains($file, 'add_quickbooks_fields_to_users_table');
        });
    }
}
