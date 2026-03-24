<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Appointments</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    

    //import database
    include("../connection.php");

    $query = "SELECT pid, COUNT(*) AS visit_count FROM appointment GROUP BY pid";
    $result = $database->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [$row['pid'], $row['visit_count']];
    }

    file_put_contents("visit_data.csv", "pid,visit_count\n");
    foreach ($data as $row) {
        file_put_contents("visit_data.csv", implode(",", $row) . "\n", FILE_APPEND);
    }

    
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@edoc.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor ">
                        <a href="doctors.php" class="non-style-link-menu "><div><p class="menu-text">Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule ">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Appointment</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient_cluster.php" class="non-style-link-menu"><div><p class="menu-text">Patients Cluster</p></a></div>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%" >
                    <a href="appointment.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Patinet Cluster</p>
                                           
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 

                        date_default_timezone_set('Asia/Kolkata');

                        $today = date('Y-m-d');
                        echo $today;

                        $list110 = $database->query("select  * from  appointment;");

                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
                
                
                <tr>
    <td colspan="4" style="padding-top: 20px;">
        <table border="1" cellspacing="0" cellpadding="10" width="100%">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Visits</th>
                    <th>Cluster</th>
                </tr>
            </thead>
            <tbody>
                    
                <?php
                    exec("python3 ../ml/kmeans_clustering.py");
                    $clustered = array_map('str_getcsv', file('clustered_patients.csv'));

                    
                    foreach ($clustered as $index => $row) {
                        if ($index == 0) continue; ?>

                        <tr>  
                            <?php 
                            
                            $patient = $database->query("select pname from patient WHERE pid=".$row[0].";");
                            $data = $patient->fetch_assoc();
                            
                            ?>  
                            <td><?=$data['pname']?></td>
                            <td><?=$row[1]?></td>
                            <td><?=$row[2]?></td>
                        </tr>
                    <?php } ?>
            </tbody>

        </table>
    </td>
</tr>
                
                


            
                  
                    
                        
            </table>
        </div>
    </div>
   <?php

if ($_GET) {
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? null;

    if ($action == 'add-session' && $id) {
        // Add New Session popup
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <a class="close" href="schedule.php">&times;</a> 
                    <div style="display: flex;justify-content: center;">
                        <div class="abc">
                            <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                <tr>
                                    <td class="label-td" colspan="2"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Add New Session.</p><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <form action="add-session.php" method="POST" class="add-new-form">
                                            <label for="title" class="form-label">Session Title : </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="text" name="title" class="input-text" placeholder="Name of this Session" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="docid" class="form-label">Select Doctor: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <select name="docid" id="" class="box" >
                                            <option value="" disabled selected hidden>Choose Doctor Name from the list</option><br/>';
        
        $list11 = $database->query("select  * from  doctor;");
        for ($y=0; $y<$list11->num_rows; $y++){
            $row00=$list11->fetch_assoc();
            $sn=$row00["docname"];
            $id00=$row00["docid"];
            echo "<option value=".$id00.">$sn</option><br/>";
        }

        echo '                      </select><br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="nop" class="form-label">Number of Patients/Appointment Numbers : </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="number" name="nop" class="input-text" min="0"  placeholder="The final appointment number for this session depends on this number" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="date" class="form-label">Session Date: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="date" name="date" class="input-text" min="'.date('Y-m-d').'" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <label for="time" class="form-label">Schedule Time: </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        <input type="time" name="time" class="input-text" placeholder="Time" required><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="submit" value="Place this Session" class="login-btn btn-primary btn" name="shedulesubmit">
                                    </td>
                                </tr>
                                </form>
                            </table>
                        </div>
                    </div>
                </center>
                <br><br>
            </div>
        </div>
        ';
    } 
    elseif ($action == 'session-added') {
        $titleget = $_GET["title"] ?? '';
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <br><br>
                    <h2>Session Placed.</h2>
                    <a class="close" href="schedule.php">&times;</a>
                    <div class="content">
                        '.htmlspecialchars(substr($titleget,0,40)).' was scheduled.<br><br>
                    </div>
                    <div style="display: flex;justify-content: center;">
                        <a href="schedule.php" class="non-style-link">
                            <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                <font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font>
                            </button>
                        </a>
                        <br><br><br><br>
                    </div>
                </center>
            </div>
        </div>
        ';
    }
    elseif ($action == 'drop' && $id) {
        $nameget = $_GET["name"] ?? '';
        $session = $_GET["session"] ?? '';
        $apponum = $_GET["apponum"] ?? '';
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <h2>Are you sure?</h2>
                    <a class="close" href="appointment.php">&times;</a>
                    <div class="content">
                        You want to delete this record<br><br>
                        Patient Name: &nbsp;<b>'.htmlspecialchars(substr($nameget,0,40)).'</b><br>
                        Appointment number &nbsp; : <b>'.htmlspecialchars(substr($apponum,0,40)).'</b><br><br>
                    </div>
                    <div style="display: flex;justify-content: center;">
                        <a href="delete-appointment.php?id='.$id.'" class="non-style-link">
                            <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                <font class="tn-in-text">&nbsp;Yes&nbsp;</font>
                            </button>
                        </a>&nbsp;&nbsp;&nbsp;
                        <a href="appointment.php" class="non-style-link">
                            <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                                <font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font>
                            </button>
                        </a>
                    </div>
                </center>
            </div>
        </div>
        ';
    }
    elseif ($action == 'view' && $id) {
        $sqlmain= "select * from doctor where docid='$id'";
        $result= $database->query($sqlmain);
        $row=$result->fetch_assoc();
        $name=$row["docname"];
        $email=$row["docemail"];
        $spe=$row["specialties"];

        $spcil_res= $database->query("select sname from specialties where id='$spe'");
        $spcil_array= $spcil_res->fetch_assoc();
        $spcil_name=$spcil_array["sname"];
        $nic=$row['docnic'];
        $tele=$row['doctel'];
        echo '
        <div id="popup1" class="overlay">
            <div class="popup">
                <center>
                    <h2></h2>
                    <a class="close" href="doctors.php">&times;</a>
                    <div class="content">
                        eDoc Web App<br>
                    </div>
                    <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.htmlspecialchars($name).'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.htmlspecialchars($email).'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.htmlspecialchars($nic).'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.htmlspecialchars($tele).'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Specialties: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.htmlspecialchars($spcil_name).'<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="doctors.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </center>
                <br><br>
            </div>
        </div>
        ';
    }
}
?>
