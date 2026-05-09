<?php
// Debug Log Viewer
echo "<h1>Auth Debug Log</h1>";
echo "<hr>";

$log_file = __DIR__ . '/auth_debug.log';

if (file_exists($log_file)) {
    echo "<p><b>Last 50 log entries:</b></p>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; max-height: 500px; overflow-y: auto;'>";
    $lines = file($log_file, FILE_IGNORE_NEW_LINES);
    $last_lines = array_slice($lines, -50);
    foreach ($last_lines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    
    echo "<p><a href='#' onclick='fetch(\"auth_debug.log\").then(r => r.text()).then(t => {document.body.innerHTML = \"<pre>\" + t + \"</pre>\"})'>View Full Log</a></p>";
    
    echo "<p><a href='auth_debug.log'>Download Log File</a></p>";
    
    echo "<p><button onclick='fetch(\"/api/auth_debug.log\", {method: \"DELETE\"}).then(() => location.reload())'>Clear Log</button></p>";
} else {
    echo "<p style='color: red;'>No log file found yet. Try logging in first.</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Back to Login</a></p>";
?>
