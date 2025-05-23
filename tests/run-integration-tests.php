<?php

/**
 * Helper script to run QuickBooks integration tests
 * 
 * This script temporarily enables integration tests by setting the RUN_INTEGRATION_TESTS
 * environment variable to true, then runs only the integration tests.
 */

// Set environment variable to enable integration tests
putenv('RUN_INTEGRATION_TESTS=true');

// Build the PHPUnit command to run only integration tests
$command = 'vendor/bin/phpunit --filter "E3DevelopmentSolutions\\\QuickBooks\\\Tests\\\Integration\\\*" --colors=always';

// Output information
echo "Running QuickBooks integration tests...\n";
echo "Command: $command\n\n";

// Execute the command
passthru($command, $exitCode);

// Return the exit code
exit($exitCode);
