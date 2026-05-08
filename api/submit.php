<?php
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');
$resendKey  = getenv('RESEND_API_KEY'); // Our new secret key

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Secure Database Connection
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
    if (!$conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // 2. Capture Form Data
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 3. Save to Database
    $sql = "INSERT INTO contact_inquiries (name, email, service, message) 
            VALUES ('$name', '$email', '$service', '$message')";

    if ($conn->query($sql) === TRUE) {
        
        // 4. THE RESEND PART: Send Email via API
        $data = [
            "from" => "Sammy Worx <onboarding@resend.dev>", 
            "to" => ["samueltendo4@gmail.com"],
            "subject" => "New Inquiry: " . $service,
            "html" => "<strong>Name:</strong> $name<br><strong>Email:</strong> $email<br><br><strong>Message:</strong><br>$message"
        ];

        $ch = curl_init("https://api.resend.com/emails");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $resendKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);

        echo "<h1>Success!</h1><p>Information saved and professional email sent.</p>";
    } else {
        echo "Error: " . $conn->error;
    }
    $conn->close();
}
?>