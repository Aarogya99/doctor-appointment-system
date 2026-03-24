<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
<?php



session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');

$_SESSION["date"]=$date;


//import database
include("connection.php");





if($_POST){

    $result= $database->query("select * from webuser");

    $fname=$_SESSION['personal']['fname'];
    $lname=$_SESSION['personal']['lname'];
    $name=$fname." ".$lname;
    $address=$_SESSION['personal']['address'];
    $nic=$_SESSION['personal']['nic'];
    $dob=$_SESSION['personal']['dob'];
    $email=$_POST['newemail'];
    $tele=$_POST['tele'];
    $newpassword=$_POST['newpassword'];
    $cpassword=$_POST['cpassword'];
    
if ($newpassword == $cpassword) {
    // Check if email already exists
    $sqlmain = "SELECT * FROM webuser WHERE email=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $error = '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">
                    Already have an account for this Email address.
                  </label>';
    } else {
        // ✅ Check if NIC already exists in patient table
        $sql_nic = "SELECT * FROM patient WHERE pnic = ?";
        $stmt_nic = $database->prepare($sql_nic);
        $stmt_nic->bind_param("s", $nic);
        $stmt_nic->execute();
        $result_nic = $stmt_nic->get_result();

        if ($result_nic->num_rows > 0) {
            $error = '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">
                        NIC already exists. Please use a unique NIC.
                      </label>';
        } else {
            // ✅ All validations passed — insert user
            $database->query("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel)
                              VALUES('$email','$name','$newpassword','$address','$nic','$dob','$tele');");

            $database->query("INSERT INTO webuser VALUES('$email','p')");

            $_SESSION["user"] = $email;
            $_SESSION["usertype"] = "p";
            $_SESSION["username"] = $fname;

            header('Location: patient/index.php');
            exit();
        }
    }
} else {
    $error = '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">
                Password confirmation does not match. Please re-enter your password.
              </label>';
}

}else{
    //header('location: signup.php');
    $error='<label for="promter" class="form-label"></label>';
}

?>


    <center>
    <div class="container">
        <table border="0" style="width: 69%;">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">It's Okay, Now Create User Account.</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="newemail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                </td>
                
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="tele" class="form-label">Mobile Number: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
               <input type="tel" name="tele" class="input-text" 
       placeholder="e.g. 9812345678" 
       pattern="9[6-8][0-9]{8}" 
       maxlength="10" 
       required
       title="Enter a 10-digit Nepali mobile number starting with 96, 97, or 98">


                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="newpassword" class="form-label">Create New Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="cpassword" class="form-label">Confirm Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
                </td>
            </tr>
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
</body>
</html>