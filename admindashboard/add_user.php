<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];

    // Insert into users table
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $status]);

    echo "User added successfully!";
}
?>

<form method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Phone: <input type="text" name="phone"><br>
    Status: 
    <select name="status">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select><br>
    <button type="submit">Add User</button>
</form>
