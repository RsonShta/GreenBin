<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bin_id = $_POST['bin_id'];
    $bin_type = $_POST['bin_type'];
    $specific_location = $_POST['specific_location'];
    $province = $_POST['province'];
    $district = $_POST['district'];
    $municipality = $_POST['municipality'];
    $ward = $_POST['ward'];
    $capacity = $_POST['capacity'];

    // Insert into bins table
    $stmt = $pdo->prepare("INSERT INTO bins (bin_id, bin_type, specific_location, province, district, municipality, ward, capacity) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$bin_id, $bin_type, $specific_location, $province, $district, $municipality, $ward, $capacity]);

    echo "Bin created successfully!";
}
?>

<form method="POST">
    Bin ID: <input type="text" name="bin_id" required><br>
    Bin Type: 
    <select name="bin_type">
        <option value="organic">Organic</option>
        <option value="recyclable">Recyclable</option>
        <option value="hazardous">Hazardous</option>
        <option value="general">General</option>
    </select><br>
    Specific Location: <input type="text" name="specific_location" required><br>
    Province: <input type="text" name="province" required><br>
    District: <input type="text" name="district" required><br>
    Municipality: <input type="text" name="municipality" required><br>
    Ward: <input type="number" name="ward" required><br>
    Capacity: <input type="number" name="capacity" required><br>
    <button type="submit">Create Bin</button>
</form>
