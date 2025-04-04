<?php
/**
 * Test runner script
 * 
 * Runs all tests in the test directory
 */

echo "============================================\n";
echo "Running all tests for Chat Application API\n";
echo "============================================\n\n";

// Define test files to run
$testFiles = [
    'DatabaseTest.php',
    'UserTest.php',
    'GroupTest.php',
    // 'AuthenticationTest.php' // This requires a running server, so commented out by default
];

$totalTests = count($testFiles);
$passedTests = 0;
$failedTests = [];

foreach ($testFiles as $testFile) {
    echo "\n=== Running $testFile ===\n";
    
    // Execute the test file
    $command = "php " . __DIR__ . "/$testFile";
    $output = [];
    $returnCode = 0;
    
    exec($command, $output, $returnCode);
    
    // Display output
    echo implode("\n", $output) . "\n";
    
    // Check result
    if ($returnCode === 0) {
        $passedTests++;
        echo "\n✅ $testFile PASSED\n";
    } else {
        $failedTests[] = $testFile;
        echo "\n❌ $testFile FAILED\n";
    }
    
    echo "----------------------------------------\n";
}

// Summary
echo "\n============================================\n";
echo "Test Results: $passedTests/$totalTests tests passed\n";

if (!empty($failedTests)) {
    echo "Failed tests:\n";
    foreach ($failedTests as $failedTest) {
        echo "  - $failedTest\n";
    }
    exit(1);
} else {
    echo "All tests passed successfully!\n";
}
echo "============================================\n"; 