<?php
require_once 'includes/user_header.php';

// Fetch current user details for the form
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT u.first_name, u.last_name, u.email_id, u.phone_number, u.profile_photo, u.role,
           ad.ward, ad.nagarpalika, ad.address
    FROM users u
    LEFT JOIN admin_details ad ON u.id = ad.user_id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Initialize variables for messages
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = ucfirst(strtolower(trim($_POST['first_name'])));
    $last_name = ucfirst(strtolower(trim($_POST['last_name'])));
    $phone_number = trim($_POST['phone_number']);
    $profile_photo = $user['profile_photo']; // keep old if not changed

    // Validate phone number
    if (!preg_match('/^(98|97)\d{8}$/', $phone_number)) {
        $error = $lang === 'np' ? "फोन नम्बर सही छैन।" : "Invalid phone number.";
    } else {
        // Handle photo upload if new one provided
        if (!empty($_FILES['profile_photo']['name'])) {
            $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/GreenBin/uploads/profile_photo/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . "_" . basename($_FILES["profile_photo"]["name"]);
            $targetFile = $targetDir . $fileName;

            // Optional: Validate file type and size here if you want

            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
                $profile_photo = $fileName;
            } else {
                $error = $lang === 'np' ? "फोटो अपलोड असफल भयो।" : "Failed to upload photo.";
            }
        }

        if (!$error) {
            $pdo->beginTransaction();
            try {
                $update = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone_number=?, profile_photo=? WHERE id=?");
                $update->execute([$first_name, $last_name, $phone_number, $profile_photo, $_SESSION['user_id']]);

                if ($user['role'] === 'admin') {
                    $ward = trim($_POST['ward']);
                    $nagarpalika = trim($_POST['nagarpalika']);
                    $address = trim($_POST['address']);

                    $adminUpdate = $pdo->prepare("
                        INSERT INTO admin_details (user_id, ward, nagarpalika, address)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE ward = VALUES(ward), nagarpalika = VALUES(nagarpalika), address = VALUES(address)
                    ");
                    $adminUpdate->execute([$userId, $ward, $nagarpalika, $address]);
                }

                $pdo->commit();

                $_SESSION['user_name'] = $first_name . " " . $last_name; // update session name
                $success = $lang === 'np' ? "प्रोफाइल सफलतापूर्वक अपडेट भयो!" : "Profile updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred.";
            }

            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['phone_number'] = $phone_number;
            $user['profile_photo'] = $profile_photo;
        }
    }
}
?>

    <main class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6 mt-2 mb-10">
        <?php
        $dashboardLink = '/GreenBin/dashboard';
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $dashboardLink = '/GreenBin/adminDashboard';
        }
        ?>
        <a href="<?= $dashboardLink ?>"
            class="inline-block text-green-700 hover:text-white hover:bg-green-700 transition text-xs border border-green-700 rounded px-1.5 py-0.5 mb-4">
            <?= $lang === 'np' ? 'ड्यासबोर्डमा फर्कनुहोस्' : '<-- Back to Dashboard' ?>
        </a>
        <h2 class="text-xl font-bold mt-2 mb-6">
            <?= $lang === 'np' ? 'प्रोफाइल सम्पादन गर्नुहोस्' : 'Edit Your Profile' ?>
        </h2>

        <?php if ($error): ?>
            <p class="text-red-600 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="text-green-600 text-sm mb-4"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- First Name -->
            <div>
                <label for="first_name" class="block text-sm font-medium mb-1">
                    <?= $lang === 'np' ? 'नाम' : 'First Name' ?>
                </label>
                <input type="text" id="first_name" name="first_name" required
                    value="<?= htmlspecialchars($user['first_name']) ?>"
                    class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
            </div>

            <!-- Last Name -->
            <div>
                <label for="last_name" class="block text-sm font-medium mb-1">
                    <?= $lang === 'np' ? 'थर' : 'Last Name' ?>
                </label>
                <input type="text" id="last_name" name="last_name" required
                    value="<?= htmlspecialchars($user['last_name']) ?>"
                    class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
            </div>

            <!-- Email (disabled) -->
            <div>
                <label for="email" class="block text-sm font-medium mb-1">
                    Email (<?= $lang === 'np' ? 'परिवर्तन गर्न मिल्दैन' : 'Cannot be changed' ?>)
                </label>
                <input type="email" id="email" disabled value="<?= htmlspecialchars($user['email_id']) ?>"
                    class="w-full border border-gray-200 bg-gray-100 p-2 rounded-md cursor-not-allowed" />
            </div>

            <!-- Phone -->
            <div>
                <label for="phone_number" class="block text-sm font-medium mb-1">
                    <?= $lang === 'np' ? 'फोन नम्बर' : 'Phone Number' ?>
                </label>
                <input type="text" id="phone_number" name="phone_number" required
                    value="<?= htmlspecialchars($user['phone_number']) ?>"
                    class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" />
            </div>

            <!-- Admin Fields -->
            <?php if ($user['role'] === 'admin'): ?>
                <div>
                    <label for="ward" class="block text-sm font-medium mb-1">Ward</label>
                    <input type="text" id="ward" name="ward" value="<?= htmlspecialchars($user['ward'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md">
                </div>
                <div>
                    <label for="nagarpalika" class="block text-sm font-medium mb-1">Nagarpalika</label>
                    <input type="text" id="nagarpalika" name="nagarpalika" value="<?= htmlspecialchars($user['nagarpalika'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md">
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium mb-1">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" class="w-full border border-gray-300 p-2 rounded-md">
                </div>
            <?php endif; ?>

            <!-- Profile Photo -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    <?= $lang === 'np' ? 'प्रोफाइल फोटो' : 'Profile Photo' ?>
                </label>
                <div class="flex items-center gap-4">
                    <img id="preview"
                        src="/GreenBin/uploads/profile_photo/<?= htmlspecialchars($user['profile_photo'] ?: 'default.png') ?>"
                        alt="Profile" class="w-16 h-16 rounded-full border object-cover" />
                    <input type="file" name="profile_photo" accept="image/*" id="profile_photo"
                        class="w-full border border-gray-300 p-1 rounded-md cursor-pointer" />
                </div>
            </div>

            <button type="submit"
                class="bg-green-700 hover:bg-green-800 text-white w-full py-2 rounded-md font-semibold transition">
                <?= $lang === 'np' ? 'परिवर्तनहरू सुरक्षित गर्नुहोस्' : 'Save Changes' ?>
            </button>
        </form>
    </main>

<?php require_once 'includes/user_footer.php'; ?>
