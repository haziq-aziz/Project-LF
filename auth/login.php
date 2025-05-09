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

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require '../includes/db_connection.php';

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Try staff (users table) first
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: ../staff/dashboard.php');
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        // Try client (clients table)
        $stmt = $conn->prepare("SELECT id, name, email, password FROM clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $client = $result->fetch_assoc();
            if (password_verify($password, $client['password'])) {
                $_SESSION['user_id'] = $client['id'];
                $_SESSION['username'] = $client['name'];
                $_SESSION['role'] = 'client';
                header('Location: ../client/dashboard.php');
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Account not found.";
        }
    }
}
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
                <p class="text-center">Your Trusted Legal Partner</p>
                
                <!-- Login error message sini -->
                <?php if($error): ?>
                    <p class="text-danger text-center"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="login.php" method="POST">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4">Sign In</button>
                </form>
                <div class="d-flex align-items-center justify-content-center">
                  <p class="fs-4 mb-0 fw-bold">Don't have an account?</p>
                  <a class="text-primary fw-bold ms-2" href="/auth/register.php">Create an account</a>
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