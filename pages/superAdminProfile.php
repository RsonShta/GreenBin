<?php require_once 'includes/header.php'; ?>
<?php
// Fetch logged-in superadmin details
$userId = $_SESSION['user_id'];
// Ensure the user is a superadmin before fetching profile details
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    header("Location: /GreenBin/login"); // Redirect to login if not superadmin
    exit;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, email_id, phone_number, created_at, role, profile_photo
                       FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}
?>

    <!-- Profile Card -->
    <main class="max-w-xl mx-auto bg-white mt-10 p-6 rounded-lg shadow">
        <div class="flex flex-col items-center">
            <?php
            // Determine the profile photo path, default if not set or if it's a placeholder
            $profilePhotoPath = !empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : '/GreenBin/frontend/img/default_profile.png'; // Assuming a default image
            ?>
            <img src="<?= $profilePhotoPath ?>" alt="Profile Photo"
                class="w-24 h-24 rounded-full border-2 border-green-600 mb-4 object-cover" />

            <h2 class="text-xl font-bold text-green-700">
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
            </h2>
            <p class="text-sm text-gray-600"><?= ucfirst($user['role']) ?></p>
        </div>
        <div class="mt-6 space-y-1 text-sm text-gray-700">
            <p>
                <span class="font-medium">Email: </span>
                <?= htmlspecialchars($user['email_id']) ?>
            </p>
            <p>
                <span class="font-medium">Phone: </span>
                <?= htmlspecialchars($user['phone_number']) ?>
            </p>
            <p>
                <span class="font-medium">Joined: </span>
                <?= date('F j, Y', strtotime($user['created_at'])) ?>
            </p>
        </div>


        <div class="mt-6 flex justify-between">
            <a href="/GreenBin/superadmin" class="text-sm bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                &#8592; Back to Superadmin Dashboard
            </a>
            <a href="/GreenBin/editProfile"
                class="text-sm bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Edit Profile
            </a>
        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>
