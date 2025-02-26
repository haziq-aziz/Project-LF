<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit();
}

$errors = [];

$caseOptions=[];
$caseQuery = "SELECT id, case_no FROM cases WHERE client_id IS NULL";
$caseResult = $conn->query($caseQuery);
if ($caseResult->num_rows > 0) {
    while ($row = $caseResult->fetch_assoc()) {
        $caseOptions[] = $row;
    }
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
    $lawyer_id = $_SESSION['user_id'];

    $emailCheck = "SELECT id FROM clients WHERE email = ?";
    $stmtCheck = $conn->prepare($emailCheck);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $errors[] = "Error: Email already exists.";
    }
    $stmtCheck->close();

    if ($password !== $confirmPassword) {
        $errors [] = "Error: Passwords do not match.";
    }

    if(empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO clients (name, gender, email, password, phone, address, country, state, city, lawyer_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $name, $gender, $email, $hashedPassword, $phone, $address, $country, $state, $city, $lawyer_id);
    
        if ($stmt->execute()) {

            $client_id = $stmt->insert_id;

            if(!empty($_POST['case_no'])) {
                $case_id = $_POST['case_no'];
                $updateCase = "UPDATE cases SET client_id = ? WHERE id = ?";
                $stmtCase = $conn->prepare($updateCase);
                $stmtCase->bind_param("ii", $client_id, $case_id);
                $stmtCase->execute();
                $stmtCase->close();
            }
        
            $_SESSION['success'] = "Client added successfully!";
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
                      <form id="addClientForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
                                      <input type="text" name="name" class="form-control" id="clientName" placeholder="Enter full name" required>
                                  </div>

                                  <div class="mb-4">
                                      <label class="form-label">Gender</label><br>
                                      <input type="radio" name="gender" value="Male" id="genderMale" required>
                                      <label for="genderMale">Male</label>
                                      <input type="radio" name="gender" value="Female" id="genderFemale" required>
                                      <label for="genderFemale">Female</label>
                                  </div>

                                  <div class="mb-4">
                                      <label for="email" class="form-label">Email Address</label>
                                      <input type="email" name="email" class="form-control" id="email" placeholder="Enter email" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="phone" class="form-label">Phone no</label>
                                      <input type="tel" name="phone" class="form-control" id="phone" placeholder="Enter phone number" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="address" class="form-label">Address</label>
                                      <textarea name="address" class="form-control" id="address" placeholder="Enter address" rows="2" required></textarea>
                                  </div>
                              </div>

                              <!-- Right Column -->
                              <div class="col-md-6">
                                  <div class="mb-4">
                                      <label for="country" class="form-label">Country</label>
                                      <select name="country" class="form-control" id="country" required>
                                          <option value="">Select Country</option>
                                          <option value="Malaysia">Malaysia</option>
                                          <option value="Singapore">Singapore</option>
                                          <option value="Indonesia">Indonesia</option>
                                      </select>
                                  </div>

                                  <div class="mb-4">
                                      <label for="state" class="form-label">State</label>
                                      <select name="state" class="form-control" id="state" required>
                                          <option value="">Select State</option>
                                          <option value="Johor">Johor</option>
                                          <option value="Selangor">Selangor</option>
                                          <option value="Sabah">Sabah</option>
                                      </select>
                                  </div>

                                  <div class="mb-4">
                                      <label for="city" class="form-label">City</label>
                                      <input type="text" name="city" class="form-control" id="city" placeholder="Enter city" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="password" class="form-label">Password</label>
                                      <input type="password" name="password" class="form-control" id="password" placeholder="Enter password" required>
                                  </div>

                                  <div class="mb-4">
                                      <label for="confirmPassword" class="form-label">Repeat Password</label>
                                      <input type="password" name="confirmPassword" class="form-control" id="confirmPassword" placeholder="Repeat password" required>
                                  </div>

                                  <div class="mb-4">
                                    <label for="case_no" class="form-label">Case No.</label>
                                    <select name="case_no" class="form-select" id="case_no">
                                        <option selected disabled>Assign Case</option>
                                        <?php foreach ($caseOptions as $case): ?>
                                            <option value="<?= $case['id']; ?>"><?= htmlspecialchars($case['case_no']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
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