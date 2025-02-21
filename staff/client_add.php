<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST["name"];
  $gender = $_POST["gender"];
  $email = trim($_POST["email"]);
  $mobile = $_POST["mobile"];
  $address = $_POST["address"];
  $country = $_POST["country"];
  $state = $_POST["state"];
  $city = $_POST["city"];
  $password = $_POST["password"];
  $confirmPassword = $_POST["confirmPassword"];
  $lawyer_id = $_SESSION['user_id'];

  if ($password !== $confirmPassword) {
    die("Error: Passwords do not match.");
  }

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO clients (name, gender, email, password, mobile, address, country, state, city, lawyer_id, created_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
  
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssssssi", $name, $gender, $email, $hashedPassword, $mobile, $address, $country, $state, $city, $lawyer_id);

  if ($stmt->execute()) {
    echo "<script>alert('Client added succesfully!'); window.location.href='client_list.php';</script>";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nabihah Ishak & CO. - Lawyer Dashboard</title>
  <link rel="stylesheet" href="../assets/css/dashboard.min.css" />
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
              <h3 class="text-primary mb-4">Add Client</h3>
              <div class="card">
                  <div class="card-body">
                      <form id="addClientForm">
                          <div class="row">
                              <!-- Left Column -->
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="clientName" class="form-label">Name</label>
                                      <input type="text" class="form-control" id="clientName" placeholder="Enter full name" required>
                                  </div>

                                  <div class="mb-3">
                                      <label class="form-label">Gender</label><br>
                                      <input type="radio" name="gender" value="Male" id="genderMale" required>
                                      <label for="genderMale">Male</label>
                                      <input type="radio" name="gender" value="Female" id="genderFemale" required>
                                      <label for="genderFemale">Female</label>
                                  </div>

                                  <div class="mb-3">
                                      <label for="email" class="form-label">Email Address</label>
                                      <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                                  </div>

                                  <div class="mb-3">
                                      <label for="mobile" class="form-label">Mobile No</label>
                                      <input type="tel" class="form-control" id="mobile" placeholder="Enter mobile number" required>
                                  </div>

                                  <div class="mb-3">
                                      <label for="address" class="form-label">Address</label>
                                      <textarea class="form-control" id="address" placeholder="Enter address" rows="2" required></textarea>
                                  </div>
                              </div>

                              <!-- Right Column -->
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="country" class="form-label">Country</label>
                                      <select class="form-control" id="country" required>
                                          <option value="">Select Country</option>
                                          <option value="Malaysia">Malaysia</option>
                                          <option value="Singapore">Singapore</option>
                                          <option value="Indonesia">Indonesia</option>
                                      </select>
                                  </div>

                                  <div class="mb-3">
                                      <label for="state" class="form-label">State</label>
                                      <select class="form-control" id="state" required>
                                          <option value="">Select State</option>
                                          <option value="Johor">Johor</option>
                                          <option value="Selangor">Selangor</option>
                                          <option value="Sabah">Sabah</option>
                                      </select>
                                  </div>

                                  <div class="mb-3">
                                      <label for="city" class="form-label">City</label>
                                      <input type="text" class="form-control" id="city" placeholder="Enter city" required>
                                  </div>

                                  <div class="mb-3">
                                      <label for="password" class="form-label">Password</label>
                                      <input type="password" class="form-control" id="password" placeholder="Enter password" required>
                                  </div>

                                  <div class="mb-3">
                                      <label for="confirmPassword" class="form-label">Repeat Password</label>
                                      <input type="password" class="form-control" id="confirmPassword" placeholder="Repeat password" required>
                                  </div>

                                  <div class="d-grid mt-4">
                                      <button type="submit" class="btn btn-primary">Add Client</button>
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
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>