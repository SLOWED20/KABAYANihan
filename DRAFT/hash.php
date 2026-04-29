<?php
// Example password
$password = "2112558";

// Create a password hash using the default algorithm (currently BCRYPT)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Original Password: " . $password . "\n";
echo "Hashed Password: " . $hashedPassword . "\n";

// Verify the password against the hash
if (password_verify($password, $hashedPassword)) {
    echo "Password is valid!";
} else {
    echo "Invalid password!";
}
