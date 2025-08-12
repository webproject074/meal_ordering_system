<?php
// This script will create a new users.json file with proper password hashes
require_once 'includes/functions.php';

// Create users array
$users = [
    [
        "id" => "admin001",
        "username" => "admin",
        "password" => password_hash("password", PASSWORD_DEFAULT),
        "role" => "admin"
    ],
    [
        "id" => "canteen001",
        "username" => "canteen",
        "password" => password_hash("password", PASSWORD_DEFAULT),
        "role" => "canteen"
    ],
    [
        "id" => "dept001",
        "username" => "hr",
        "password" => password_hash("password", PASSWORD_DEFAULT),
        "role" => "department",
        "department_name" => "Human Resources"
    ],
    [
        "id" => "dept002",
        "username" => "finance",
        "password" => password_hash("password", PASSWORD_DEFAULT),
        "role" => "department",
        "department_name" => "Finance"
    ]
];

// Write to users.json
$result = file_put_contents('data/users.json', json_encode($users, JSON_PRETTY_PRINT));

if ($result) {
    echo "Users file created successfully!";
} else {
    echo "Error creating users file!";
}
?>
