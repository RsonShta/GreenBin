<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $area = $_POST['area'];
    $street = $_POST['street'];
    $title = $_POST['title'];
    $priority = $_POST['priority'];

    // Insert into complaints table
    $stmt = $pdo->prepare("INSERT INTO complaints (name, date, area, street, title, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $date, $area, $street, $title, $priority, 'New']);

    echo "Complaint added successfully!";
}
?>

<form method="POST">
    Name: <input type="text" name="name" required><br>
    Date: <input type="date" name="date" required><br>
    Area: <input type="text" name="area" required><br>
    Street: <input type="text" name="street" required><br>
    Title: <input type="text" name="title" required><br>
    Priority: 
    <select name="priority">
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select><br>
    <button type="submit">Submit Complaint</button>
</form>
