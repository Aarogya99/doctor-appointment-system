<?php
session_start();
date_default_timezone_set('Asia/Kathmandu');

if (!isset($_SESSION["user"]) || $_SESSION["usertype"] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

// Get patient info
$sqlmain = "SELECT * FROM patient WHERE pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$userfetch = $result->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];

// Handle cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    $stmt = $database->prepare("DELETE FROM appointment WHERE appoid = ? AND pid = ?");
    $stmt->bind_param("ii", $cancel_id, $userid);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        header("Location: schedule.php?msg=cancelled");
    } else {
        header("Location: schedule.php?msg=notfound");
    }
    exit();
}

$today = date('Y-m-d');
$insertkey = "";
$searchtype = "All";
$q = "";

if ($_POST && !empty($_POST["search"])) {
    $keyword = $_POST["search"];
    $sqlmain = "SELECT 
        schedule.scheduleid, 
        schedule.title, 
        doctor.docname, 
        specialties.sname AS specialty, 
        schedule.scheduledate, 
        schedule.scheduletime, 
        schedule.endtime,
        schedule.nop,
        COUNT(appointment.appoid) AS booked
    FROM schedule 
    INNER JOIN doctor ON schedule.docid = doctor.docid  
    LEFT JOIN specialties ON doctor.specialties = specialties.id
    LEFT JOIN appointment ON schedule.scheduleid = appointment.scheduleid
    WHERE schedule.scheduledate >= ? 
    AND (
        doctor.docname LIKE ? OR
        schedule.title LIKE ? OR
        schedule.scheduledate LIKE ?
    )
    GROUP BY schedule.scheduleid
    ORDER BY schedule.scheduledate ASC";

    $likeKeyword = "%$keyword%";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("ssss", $today, $likeKeyword, $likeKeyword, $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();

    $insertkey = $keyword;
    $searchtype = "Search Results for";
    $q = '"';
} else {
    $sqlmain = "SELECT 
        schedule.scheduleid, 
        schedule.title, 
        doctor.docname, 
        specialties.sname AS specialty, 
        schedule.scheduledate, 
        schedule.scheduletime, 
        schedule.endtime,
        schedule.nop,
        COUNT(appointment.appoid) AS booked
    FROM schedule 
    INNER JOIN doctor ON schedule.docid = doctor.docid  
    LEFT JOIN specialties ON doctor.specialties = specialties.id
    LEFT JOIN appointment ON schedule.scheduleid = appointment.scheduleid
    WHERE schedule.scheduledate >= ?
    GROUP BY schedule.scheduleid
    ORDER BY schedule.scheduledate ASC";

    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
}


// Fetch patient's booked appointments: scheduleid => appoid
$patientBookedAppointments = [];
$apptStmt = $database->prepare("SELECT appoid, scheduleid FROM appointment WHERE pid = ?");
$apptStmt->bind_param("i", $userid);
$apptStmt->execute();
$apptResult = $apptStmt->get_result();
while ($apptRow = $apptResult->fetch_assoc()) {
    $patientBookedAppointments[$apptRow['scheduleid']] = $apptRow['appoid'];
}
$apptStmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Sessions</title>
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        /* Your existing styles here */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 20px 40px;
        }
        .dash-body {
            width: 100%;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .back-btn button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #3498db;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn button:hover {
            background-color: #2980b9;
        }
        .header-search {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .header-search input[type="text"] {
            flex: 1;
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .header-search input[type="submit"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .header-search input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .session-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }
        .session-card {
            flex: 1 1 calc(33.33% - 20px);
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: 0.3s ease;
            min-width: 280px;
            max-width: 100%;
        }
        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        .h1-search {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        .h3-search {
            font-size: 16px;
            color: #666;
            margin-bottom: 8px;
        }
        .h4-search {
            font-size: 14px;
            color: #555;
            line-height: 1.4;
            margin-bottom: 12px;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border-radius: 5px;
        }
        @media screen and (max-width: 992px) {
            .session-card {
                flex: 1 1 calc(50% - 20px);
            }
        }
        @media screen and (max-width: 600px) {
            .session-card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dash-body">

        <!-- Back Button -->
        <div class="back-btn">
            <a href="index.php"><button>← Back to Portal</button></a>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['msg'])): ?>
            <p style="color:<?php echo ($_GET['msg']=='cancelled') ? 'green' : 'red'; ?>;">
                <?php 
                    echo ($_GET['msg'] == 'cancelled') 
                    ? 'Appointment cancelled successfully.' 
                    : 'Failed to cancel appointment.';
                ?>
            </p>
        <?php endif; ?>

        <!-- Search -->
        <form method="post" class="header-search">
            <input type="text" name="search" value="<?php echo htmlspecialchars($insertkey) ?>" placeholder="Search doctor, title or date" list="doctors">

            <datalist id="doctors">
                <?php
                    $dlist1 = $database->query("SELECT DISTINCT docname FROM doctor;");
                    $dlist2 = $database->query("SELECT DISTINCT title FROM schedule;");
                    while ($d = $dlist1->fetch_assoc()) echo "<option value='" . htmlspecialchars($d['docname']) . "'>";
                    while ($d = $dlist2->fetch_assoc()) echo "<option value='" . htmlspecialchars($d['title']) . "'>";
                ?>
            </datalist>
            <input type="submit" value="Search">
        </form>

        <h2><?php echo $searchtype . ' <em>' . $q . htmlspecialchars($insertkey) . $q . '</em>'; ?></h2>
        <p style="color:#777;">Total sessions: <strong><?php echo $result->num_rows; ?></strong></p>

        <!-- Session Grid -->
        <div class="session-grid">
            <?php
            if ($result->num_rows == 0) {
                echo '<div style="flex: 1 1 100%; text-align: center;"><img src="../img/notfound.svg" width="20%"><p>No sessions found.</p></div>';
            } else {
                while ($row = $result->fetch_assoc()) {
                    $scheduleid = $row["scheduleid"];
                    $title = $row["title"];
                    $docname = $row["docname"];
                    $scheduledate = $row["scheduledate"];
                    $scheduletime = $row["scheduletime"];
                    $nop = $row["nop"];
                    $booked = $row["booked"];
                    $available = $nop - $booked;
                    $endtime = $row["endtime"];
                    // Check if patient booked this schedule
                    if (isset($patientBookedAppointments[$scheduleid])) {
                        $appoid = $patientBookedAppointments[$scheduleid];
                        $btn = '<a href="schedule.php?cancel=' . $appoid . '" onclick="return confirm(\'Are you sure you want to cancel your booking?\');"><button class="login-btn btn-danger-soft">Cancel Booking</button></a>';
                    } else {
                        if ($available > 0) {
                            $btn = '<a href="booking.php?id=' . $scheduleid . '"><button class="login-btn btn-primary-soft">Book Now</button></a>';
                        } else {
                            $btn = '<button class="login-btn btn-primary-soft" disabled>Fully Booked</button>';
                        }
                    }

                    echo '
                <div class="session-card">
    <div class="h1-search">' . htmlspecialchars($title) . '</div>
   <div class="h3-search">Dr. ' . htmlspecialchars($docname) . 
    (!empty($row["specialty"]) ? ' (' . htmlspecialchars($row["specialty"]) . ')' : '') . 
'</div>

    <div class="h4-search">
        Date: ' . htmlspecialchars($scheduledate) . '<br>
        Starts at: <b>' . 
            ($scheduletime && $scheduletime != "00:00:00" 
                ? date("H:i", strtotime($scheduletime)) 
                : 'Not set') . 
        '</b><br>
        Ends at: <b>' . 
            ($endtime && $endtime != "00:00:00" 
                ? date("H:i", strtotime($endtime)) 
                : 'Not set') . 
        '</b><br>
        <span style="color:green;">Available: ' . $available . '</span><br>
        <span style="color:red;">Booked: ' . $booked . '</span>
    </div>

    ' . $btn . '
</div>';

                }
            }
            ?>
        </div>
    </div>
</div>

</body>
</html>
