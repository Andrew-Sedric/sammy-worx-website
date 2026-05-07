<?php
// 1. Get the Secret Database Details from Vercel's Environment Variables
$servername = getenv('DB_HOST');
$username   = getenv('DB_USER');
$password   = getenv('DB_PASSWORD');
$dbname     = getenv('DB_NAME');

// 2. Check if the user actually clicked the submit button
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Create connection to your Cloud Database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // If the connection fails, stop and show the error
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 3. Capture the data from the form fields
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 4. Save the information into your "contact_inquiries" table
    $sql = "INSERT INTO contact_inquiries (name, email, service, message) 
            VALUES ('$name', '$email', '$service', '$message')";

    if ($conn->query($sql) === TRUE) {
        
        // 5. Send the Email to yourself
        $to = "samueltendo4@gmail.com"; 
        $subject = "New Sammy Worx Inquiry: " . $service;
        $email_content = "Name: $name\nEmail: $email\nService: $service\n\nMessage:\n$message";
        $headers = "From: webmaster@sammyworx.com\r\n" . "Reply-To: $email";

        // This sends the email
        mail($to, $subject, $email_content, $headers);

        // Success message for the user
        echo "<h1>Thank You!</h1><p>Your message has been received and saved successfully.</p>";
        echo "<a href='/'>Return Home</a>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
} else {
    echo "Access Denied";
}
?>