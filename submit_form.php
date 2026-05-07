<?php
if (isset($_POST['submit'])) {
    // Collect data from your HTML form fields
    $name = $_POST['name'];
    $email = $_POST['email'];
    $service = $_POST['service'];
    $message = $_POST['message'];

    // Your details
    $to = "samueltendo4@gmail.com"; 
    $subject = "New Inquiry: " . $service;

    // Build the email content
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Service: $service\n\n";
    $email_content .= "Message:\n$message\n";

    // Headers
    $headers = "From: webmaster@sammyworx.com\r\n";
    $headers .= "Reply-To: $email";

    // Send the email
    if (mail($to, $subject, $email_content, $headers)) {
        echo "<h1>Success!</h1><p>Thank you Sammy, your message has been sent.</p>";
    } else {
        echo "<h1>Oops!</h1><p>Something went wrong on the server.</p>";
    }
} else {
    echo "Access Denied";
}
?>