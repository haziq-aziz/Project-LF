<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'staff') {
        header('Location: ../staff/dashboard.php');
    } else {
        header('Location: ../client/dashboard.php');
    }
    exit();
}

require_once '../includes/db_connection.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $phone = trim($_POST["phone"]) ?: NULL;
    $address = $_POST["address"] ?: NULL;

    if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields (except phone and address) are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists sini
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email or Username is already taken.";
    }
    $stmt->close();

    // Kalau tak error, regsiter user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $username, $email, $hashed_password, $phone, $address);

        if ($stmt->execute()) {
            header("Location: ../client/dashboard.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Login</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="/index.php" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <h2>Nabihah Ishak & CO.</h2>
                </a>
                <p class="text-center">Create an account</p>

                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                  <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" reqiured>
                  </div>
                  <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" reqiured>
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                  </div>
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone No</label>
                    <input type="number" class="form-control" id="phone" name="phone" required>
                  </div>
                  <div class="mb-4">
                    <label for="address" class="form-label">Address (Optional)</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4">Sign Up</button>
                </form>
                <div class="d-flex align-items-center justify-content-center">
                  <p class="fs-4 mb-0 fw-bold">Already have an Account?</p>
                  <a class="text-primary fw-bold ms-2" href="/auth/login.php">Sign In</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>