<?php
date_default_timezone_set('Asia/Kathmandu');  // or your actual timezone
session_start();

if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p' || $_SESSION["user"] == "") {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

// Fetch patient info
$useremail = $_SESSION["user"];
$sqlmain = "SELECT * FROM patient WHERE pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$userfetch = $result->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];
date_default_timezone_set('Asia/Kathmandu'); 
$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/animations.css" />
    <link rel="stylesheet" href="../css/main.css" />
    <link rel="stylesheet" href="../css/admin.css" />
    <title>Sessions</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="menu">
        <!-- Your menu HTML here -->
    </div>

    <div class="dash-body">
        <table border="0" width="100%" style="border-spacing: 0; margin:0; padding:0; margin-top:25px;">
            <tr>
                <td width="13%">
                    <a href="schedule.php">
                        <button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px; padding-bottom:11px; margin-left:20px; width:125px;">
                            <font class="tn-in-text">Back</font>
                        </button>
                    </a>
                </td>
                <td>
                    <form action="schedule.php" method="post" class="header-search">
                        <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email or Date (YYYY-MM-DD)" list="doctors" />
                        &nbsp;&nbsp;
                        <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("SELECT DISTINCT * FROM doctor;");
                        $list12 = $database->query("SELECT DISTINCT * FROM schedule GROUP BY title;");
                        while ($row00 = $list11->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row00["docname"]) . "'>";
                        }
                        while ($row00 = $list12->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row00["title"]) . "'>";
                        }
                        echo '</datalist>';
                        ?>
                        <input type="submit" value="Search" class="login-btn btn-primary btn" style="padding: 10px 25px;" />
                    </form>
                </td>
                <td width="15%" style="text-align: right;">
                    <p style="font-size: 14px; color: rgb(119, 119, 119); padding: 0; margin: 0;">Today's Date</p>
                    <p class="heading-sub12" style="padding: 0; margin: 0;"><?php echo $today; ?></p>
                </td>
                <td width="10%">
                    <button class="btn-label" style="display: flex; justify-content: center; align-items: center;">
                        <img src="../img/calendar.svg" width="100%" />
                    </button>
                </td>
            </tr>

            <tr>
                <td colspan="4" style="padding-top:10px; width: 100%;"></td>
            </tr>

            <tr>
                <td colspan="4">
                    <center>
                        <div class="abc scroll">
                            <table width="100%" class="sub-table scrolldown" border="0" style="padding: 50px; border:none;">
                                <tbody>
<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    // Get session + doctor info
    $sqlmain = "SELECT schedule.*, doctor.docname, doctor.docemail FROM schedule 
                INNER JOIN doctor ON schedule.docid = doctor.docid 
                WHERE schedule.scheduleid = ? 
                ORDER BY schedule.scheduledate DESC";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo "<tr><td colspan='4'>Session not found.</td></tr>";
    } else {
        $scheduleid = $row["scheduleid"];
        $title = $row["title"];
        $docname = $row["docname"];
        $docemail = $row["docemail"];
        $scheduledate = $row["scheduledate"];
        $scheduletime = $row["scheduletime"];
        $nop = $row["nop"];

        // Calculate end time (assuming each slot 15 mins)
       $endTime = $row['endtime'];  // Use stored endtime

        // Check if user already booked this session
        $stmt = $database->prepare("SELECT * FROM appointment WHERE pid=? AND scheduleid=?");
        $stmt->bind_param("ii", $userid, $scheduleid);
        $stmt->execute();
        $existing = $stmt->get_result();
        if ($existing->num_rows > 0) {
            echo "<script>alert('You have already booked this session.');window.location.href='schedule.php';</script>";
            exit();
        }

        // Calculate next appointment number
        $sql2 = "SELECT * FROM appointment WHERE scheduleid = ?";
        $stmt2 = $database->prepare($sql2);
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result12 = $stmt2->get_result();
        $apponum = $result12->num_rows + 1;

        echo '
        <form action="booking-complete.php" method="post">
            <input type="hidden" name="scheduleid" value="' . htmlspecialchars($scheduleid) . '" />
            <input type="hidden" name="apponum" value="' . htmlspecialchars($apponum) . '" />
            <input type="hidden" name="date" value="' . htmlspecialchars($scheduledate) . '" />
        ';

        echo '
        <tr>
            <td style="width: 50%;" rowspan="2">
                <div class="dashboard-items search-items">
                    <div style="width:100%">
                        <div class="h1-search" style="font-size:25px;">Session Details</div><br><br>
                        <div class="h3-search" style="font-size:18px; line-height:30px;">
                            Doctor name: <b>' . htmlspecialchars($docname) . '</b><br>
                            Doctor Email: <b>' . htmlspecialchars($docemail) . '</b>
                        </div><br>
                        <div class="h3-search" style="font-size:18px;">
    Session Title: ' . htmlspecialchars($title) . '<br>
    Scheduled Date: ' . htmlspecialchars($scheduledate) . '<br>
    Session Starts: ' . (!empty($scheduletime) && $scheduletime != '00:00:00' ? date("h:i A", strtotime($scheduletime)) : 'Not set') . '<br>
Session Ends: ' . (!empty($endTime) && $endTime != '00:00:00' ? date("h:i A", strtotime($endTime)) : 'Not set') . '<br>

</div><br>

                    </div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="dashboard-items search-items">
                    <div style="width:100%; padding-top: 15px; padding-bottom: 15px;">
                        <div class="h1-search" style="font-size:20px; text-align:center;">Your Appointment Number</div>
                        <center>
                            <div class="dashboard-icons" style="font-size:70px; font-weight:800; color:var(--btnnictext); background-color: var(--btnice);">
                                ' . htmlspecialchars($apponum) . '
                            </div>
                        </center>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" class="login-btn btn-primary btn btn-book" style="margin-left:10px; width:95%;" value="Book now" name="booknow" />
            </td>
        </tr>
        </form>
        ';
    }
}
?>
                                </tbody>
                            </table>
                        </div>
                    </center>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
