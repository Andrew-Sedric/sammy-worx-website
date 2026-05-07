<?php
// 1. Get Secret Database Details from Vercel's Environment Variables
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// 2. Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- START OF SSL FIX ---
    // This part creates a secure connection so TiDB doesn't block the request
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
    
    // Connect using the SSL settings
    if (!$conn->real_connect($servername, $username, $password, $dbname, 3306, NULL, MYSQLI_CLIENT_SSL)) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // --- END OF SSL FIX ---

    // 3. Capture and sanitize the data from the form fields
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 4. Save the information into your "contact_inquiries" table
    $sql = "INSERT INTO contact_inquiries (name, email, service, message) 
            VALUES ('$name', '$email', '$service', '$message')";

    if ($conn->query($sql) === TRUE) {
        
        // 5. Send the Email Notification to yourself
        $to = "samueltendo4@gmail.com"; 
        $subject = "New Sammy Worx Inquiry: " . $service;
        $email_content = "Name: $name\nEmail: $email\nService: $service\n\nMessage:\n$message";
        $headers = "From: webmaster@sammyworx.com\r\n" . "Reply-To: $email";

        // This sends the email
        mail($to, $subject, $email_content, $headers);

        // Success message for the user
        echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
        echo "<h1 style='color: #0a192f;'>Success!</h1>";
        echo "<p>Thank you, your message has been received and saved successfully.</p>";
        echo "<a href='/' style='color: #ffcc00; text-decoration: none; font-weight: bold;'>Return to Homepage</a>";
        echo "</div>";
    } else {
        echo "Error: " . $conn->error;
    }

    // Close the connection
    $conn->close();
} else {
    // If someone tries to access this file directly without posting the form
    echo "Access Denied. Please use the contact form.";
}
?>