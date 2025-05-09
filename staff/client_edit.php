<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$errors = [];
$userData = [];

// Fetch existing user data if editing
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $userQuery = "SELECT * FROM clients WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows > 0) {
        $userData = $userResult->fetch_assoc();
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: client_view.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $email = trim($_POST["email"]);
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $country = $_POST["country"];
    $state = $_POST["state"];
    $city = $_POST["city"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];
    $userId = $_POST["user_id"]; // Hidden input for user ID

    // Validate email uniqueness (if changed)
    if ($email !== $userData['email']) {
        $emailCheck = "SELECT id FROM clients WHERE email = ? AND id != ?";
        $stmtCheck = $conn->prepare($emailCheck);
        $stmtCheck->bind_param("si", $email, $userId);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $errors[] = "Error: Email already exists.";
        }
        $stmtCheck->close();
    }

    // Validate password match
    if (!empty($password) && $password !== $confirmPassword) {
        $errors[] = "Error: Passwords do not match.";
    }

    if (empty($errors)) {
        // Update user data
        $sql = "UPDATE clients SET 
                name = ?, 
                gender = ?, 
                email = ?, 
                phone = ?, 
                address = ?, 
                country = ?, 
                state = ?, 
                city = ? 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $name, $gender, $email, $phone, $address, $country, $state, $city, $userId);

        if ($stmt->execute()) {
            // Update password if provided
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updatePassword = "UPDATE clients SET password = ? WHERE id = ?";
                $stmtPassword = $conn->prepare($updatePassword);
                $stmtPassword->bind_param("si", $hashedPassword, $userId);
                $stmtPassword->execute();
                $stmtPassword->close();
            }

            $_SESSION['success'] = "User updated successfully!";
            header("Location: client_view.php");
            exit;
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Edit User</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
  <link rel="stylesheet" href="../assets/css/others.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    <!-- Include Sidebar -->
    <?php include '../includes/staff/sidebar.php'; ?>
    
    <!--  Main wrapper -->
    <div class="body-wrapper">
      
      <!-- Include Navbar -->
      <?php include '../includes/staff/navbar.php'; ?>
      
      <!-- Main Content -->
      <div class="container-fluid">
          <div class="row">
              <h3 class="text-primary mb-4">Edit User</h3>
              <div class="card">
                  <div class="card-body">
                      <form id="editUserForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                          <input type="hidden" name="user_id" value="<?= $userData['id'] ?? ''; ?>">
                          <div class="row">
                            <div>
                                <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
                                </div>
                            <?php endif; ?>

                            </div>
                              <!-- Left Column -->
                              <div class="col-md-6">
                                  <div class="mb-4">
                                      <label for="clientName" class="form-label">Name</label>
                                      <input type="text" name="name" class="form-control" id="clientName" value="<?= htmlspecialchars($userData['name'] ?? ''); ?>" required>
                                  </div>

                                  <div class="mb-4">
                                      <label class="form-label">Gender</label><br>
                                      <input type="radio" name="gender" value="Male" id="genderMale" <?= ($userData['gender'] ?? '') === 'Male' ? 'checked' : ''; ?> required>
                                      <label for="genderMale">Male</label>
                                      <input type="radio" name="gender" value="Female" id="genderFemale" <?= ($userData['gender'] ?? '') === 'Female' ? 'checked' : ''; ?> required>
                                      <label for="genderFemale">Female</label>
                                  </div>

                                  <div class="mb-4">
                                      <label for="email" class="form-label">Email Address</label>
                                      <input type="email" name="email" class="form-control" id="email" value="<?= htmlspecialchars($userData['email'] ?? ''); ?>" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="phone" class="form-label">Phone no</label>
                                      <input type="tel" name="phone" class="form-control" id="phone" value="<?= htmlspecialchars($userData['phone'] ?? ''); ?>" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="address" class="form-label">Address</label>
                                      <textarea name="address" class="form-control" id="address" rows="2" required><?= htmlspecialchars($userData['address'] ?? ''); ?></textarea>
                                  </div>
                              </div>

                              <!-- Right Column -->
                              <div class="col-md-6">
                                  <div class="mb-4">
                                      <label for="country" class="form-label">Country</label>
                                      <select name="country" class="form-control" id="country" required>
                                          <option value="">Select Country</option>
                                          <option value="Malaysia" <?= ($userData['country'] ?? '') === 'Malaysia' ? 'selected' : ''; ?>>Malaysia</option>
                                          <option value="Singapore" <?= ($userData['country'] ?? '') === 'Singapore' ? 'selected' : ''; ?>>Singapore</option>
                                          <option value="Indonesia" <?= ($userData['country'] ?? '') === 'Indonesia' ? 'selected' : ''; ?>>Indonesia</option>
                                      </select>
                                  </div>

                                  <div class="mb-4">
                                      <label for="state" class="form-label">State</label>
                                      <select name="state" class="form-control" id="state" required>
                                          <option value="">Select State</option>
                                          <option value="Johor" <?= ($userData['state'] ?? '') === 'Johor' ? 'selected' : ''; ?>>Johor</option>
                                          <option value="Selangor" <?= ($userData['state'] ?? '') === 'Selangor' ? 'selected' : ''; ?>>Selangor</option>
                                          <option value="Sabah" <?= ($userData['state'] ?? '') === 'Sabah' ? 'selected' : ''; ?>>Sabah</option>
                                      </select>
                                  </div>

                                  <div class="mb-4">
                                      <label for="city" class="form-label">City</label>
                                      <input type="text" name="city" class="form-control" id="city" value="<?= htmlspecialchars($userData['city'] ?? ''); ?>" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="password" class="form-label">Password</label>
                                      <input type="password" name="password" class="form-control" id="password" placeholder="Leave blank to keep current password">
                                  </div>

                                  <div class="mb-4">
                                      <label for="confirmPassword" class="form-label">Repeat Password</label>
                                      <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" placeholder="Repeat password">
                                  </div>

                                  <div class="d-grid mt-4">
                                      <button type="submit" class="btn btn-primary m-1">Update User</button>
                                  </div>
                                   <div class="d-grid mt-2">
                                      <a href="client_view.php" class="btn btn-danger m-1">Cancel</a>
                                  </div>
                              </div>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
      </div>  
      <!-- Include Footer -->
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>
  
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>

</html>