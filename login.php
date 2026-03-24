<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/animations.css" />
  <link rel="stylesheet" href="css/main.css" />
  <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>" />
  <title>Login</title>
</head>
<body>
<?php
session_start();
$_SESSION["user"] = "";
$_SESSION["usertype"] = "";
date_default_timezone_set('Asia/Kathmandu');
$_SESSION["date"] = date('Y-m-d');
include("connection.php");

$error = "";

if ($_POST) {
    $email = $_POST['useremail'];
    $password = $_POST['userpassword'];

    $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
    if ($result->num_rows == 1) {
        $utype = $result->fetch_assoc()['usertype'];
        $query = "";
        if ($utype == 'p') {
            $query = "SELECT * FROM patient WHERE pemail='$email' AND ppassword='$password'";
            $redirect = "patient/index.php";
        } elseif ($utype == 'a') {
            $query = "SELECT * FROM admin WHERE aemail='$email' AND apassword='$password'";
            $redirect = "admin/index.php";
        } elseif ($utype == 'd') {
            $query = "SELECT * FROM doctor WHERE docemail='$email' AND docpassword='$password'";
            $redirect = "doctor/index.php";
        }

        $checker = $database->query($query);
        if ($checker->num_rows == 1) {
            $_SESSION['user'] = $email;
            $_SESSION['usertype'] = $utype;
            header("Location: $redirect");
            exit();
        } else {
            $error = "Wrong credentials: Invalid email or password.";
        }
    } else {
        $error = "We can't find an account with this email.";
    }
}
?>

<div class="container">
  <form action="" method="POST">
    <h2 class="header-text">Welcome Back!</h2>
    <p class="sub-text">Login with your details to continue</p>

    <div class="form-group">
      <label for="useremail" class="form-label">Email:</label>
      <input type="email" name="useremail" class="input-text" placeholder="Email Address" required />
    </div>

    <div class="form-group">
      <label for="userpassword" class="form-label">Password:</label>
      <input type="password" name="userpassword" class="input-text" placeholder="Password" required />
    </div>

    <?php if ($error): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <input type="submit" value="Login" class="btn btn-primary" />

    <p class="sub-text" style="margin-top: 20px;">
      Don't have an account?
      <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
    </p>
  </form>
</div>
</body>
</html>
