<?php
date_default_timezone_set('Asia/Kathmandu');
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION["usertype"] != 'p') {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

$useremail = $_SESSION["user"];

// Fetch logged-in patient info
$sqlpatient = "SELECT * FROM patient WHERE pemail = ?";
$stmt = $database->prepare($sqlpatient);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();

$userid = $userfetch["pid"];
$username = $userfetch["pname"];

// Prepare main SQL query for appointments with schedule and doctor info
$sqlmain = "SELECT 
    appointment.appoid, 
    appointment.apponum, 
    appointment.appodate, 
    appointment.status, 
    schedule.scheduleid, 
    schedule.title, 
    schedule.scheduledate, 
    schedule.scheduletime, 
    schedule.nop, 
    doctor.docname 
FROM appointment
INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
INNER JOIN doctor ON schedule.docid = doctor.docid
WHERE appointment.pid = ?";

// Apply date filter if POSTed
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["sheduledate"])) {
    $sheduledate = $_POST["sheduledate"];
    $sqlmain .= " AND schedule.scheduledate = ?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("is", $userid, $sheduledate);
} else {
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("i", $userid);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <title>Appointments</title>
    <link rel="stylesheet" href="../css/animations.css" />
    <link rel="stylesheet" href="../css/main.css" />
    <link rel="stylesheet" href="../css/admin.css" />
    
    <style>
        html, body {
    margin: 0; 
    padding: 0;
    overflow-x: hidden; /* prevent horizontal scroll */
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    background: #f9f9f9;
}

        /* Popup overlay covers entire viewport */
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .popup {
            background: white;
            border-radius: 10px;
            padding: 25px 30px;
            max-width: 400px;
            width: 100%;
            animation: transitionIn-Y-bottom 0.5s;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            position: relative;
        }

        /* Close button style */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 30px;
            text-decoration: none;
            color: #333;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }

        /* Lock body scroll when popup open */
        body.popup-open {
            overflow: hidden;
        }

        /* Back button fixed top-left */
        .back-button-container {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 10000;
        }

        .back-button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 8px 16px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: background-color 0.3s ease;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }

        /* Container to fit screen and avoid overflow */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 15px 30px; /* top padding accounts for back button */
            box-sizing: border-box;
            min-height: 100vh;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
        }

        .dash-body {
            flex-grow: 1;
            overflow-y: auto;
        }

        /* Appointments wrapper uses flexbox */
        .appointments-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        /* Single appointment card */
        .dashboard-items {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            width: 300px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease;
        }
        .dashboard-items:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .dashboard-items .h3-search,
        .dashboard-items .h4-search,
        .dashboard-items .h1-search {
            margin: 6px 0;
            color: #333;
        }
        .dashboard-items .h1-search {
            font-size: 1.3em;
            font-weight: 700;
            color: #1e73be;
        }
        .dashboard-items .h3-search {
            font-weight: 600;
            font-size: 14px;
        }
        .dashboard-items .h4-search {
            font-size: 13px;
            color: #555;
        }

        /* Status colors */
        .status-approved {
            font-weight: bold;
            color: green;
        }
        .status-pending {
            font-weight: bold;
            color: orange;
        }
        .status-cancelled {
            font-weight: bold;
            color: red;
        }

        /* Cancel button */
        .login-btn.btn-primary-soft.btn {
            padding: 11px 0;
            width: 100%;
            background-color: #e74c3c;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }
        .login-btn.btn-primary-soft.btn:hover {
            background-color: #c0392b;
        }

        /* Responsive for smaller screens */
        @media (max-width: 768px) {
            .dashboard-items {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="back-button-container">
    <button onclick="window.location.href='schedule.php'" class="back-button">&#8592; Back</button>
</div>

<div class="container">
    <div class="dash-body">
        <div class="appointments-wrapper">
            <?php
            if ($result->num_rows == 0) {
                echo '<p style="text-align:center; width: 100%;">No appointments found.</p>';
            } else {
                while ($row = $result->fetch_assoc()) {
                    $scheduleid = $row["scheduleid"];
                    $title = htmlspecialchars($row["title"]);
                    $docname = htmlspecialchars($row["docname"]);
                    $scheduledate = $row["scheduledate"];
                    $scheduletime = $row["scheduletime"];
                    $nop = (int)$row["nop"];

                    $apponum = $row["apponum"];
                    $appodate = $row["appodate"];
                    $appoid = $row["appoid"];
                    $status = $row["status"];

                    $startTimestamp = strtotime($scheduledate . ' ' . $scheduletime);
                    $endTimestamp = $startTimestamp + ($nop * 15 * 60);

                    // Status class for color
                    $statusClass = ($status == "approved") ? "status-approved" : (($status == "pending") ? "status-pending" : "status-cancelled");

                    echo '
                    <div class="dashboard-items search-items">
                        <div>
                            <div class="h3-search">
                                Booking Date: ' . htmlspecialchars(substr($appodate, 0, 30)) . '<br>
                                Reference Number: OC-000-' . htmlspecialchars($appoid) . '
                            </div>
                            <div class="h1-search">' . htmlspecialchars_decode(mb_strimwidth($title, 0, 21, "...")) . '</div>


                            <div class="h3-search">
                                Appointment Number: <div class="h1-search">0' . htmlspecialchars($apponum) . '</div>
                            </div>
                            <div class="h3-search">' . htmlspecialchars(substr($docname, 0, 30)) . '</div>
                            <div class="h4-search">
                                Scheduled Date: ' . htmlspecialchars($scheduledate) . '<br>
                                Starts: <b>@' . date("h:i A", $startTimestamp) . '</b>
                            </div>
                            <div class="h4-search" style="margin-top: 5px;">
                                Ends: <b>@' . date("h:i A", $endTimestamp) . '</b>
                            </div>
                            <div class="h4-search ' . $statusClass . '" style="margin-top: 5px;">
                                Status: ' . ucfirst(htmlspecialchars($status)) . '
                            </div>
                            <a href="?action=drop&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '">
                                <button class="login-btn btn-primary-soft btn">
                                    <font class="tn-in-text">Cancel Booking</font>
                                </button>
                            </a>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
    </div>
</div>

<?php
// Handle popup modals based on GET parameters
if ($_GET) {
    $id = $_GET["id"] ?? '';
    $action = $_GET["action"] ?? '';

    if ($action == 'booking-added') {
        echo '
        <div class="overlay">
            <div class="popup">
                <a class="close" href="appointment.php">&times;</a>
                <center>
                    <br><br>
                    <h2>Appointment Booked Successfully</h2>
                    <br><br>
                    <a href="appointment.php"><button class="login-btn btn-primary-soft btn">Ok</button></a>
                    <br><br>
                </center>
            </div>
        </div>';
    } elseif ($action == 'drop' && !empty($id)) {
        $title = htmlspecialchars($_GET["title"] ?? '');
        $doc = htmlspecialchars($_GET["doc"] ?? '');

        echo '
<div class="overlay" onclick="closePopup(event)">
    <div class="popup" onclick="event.stopPropagation()">
        <a class="close" href="appointment.php">&times;</a>
        <center>
            <br><br>
            <h2>Cancel Booking?</h2>
            <p>Are you sure you want to cancel the appointment for <b>' . $title . '</b> with Dr. <b>' . $doc . '</b>?</p>
            <br>
<a href="delete-appointment.php?id=' . urlencode($id) . '">
    <button class="login-btn btn-primary-soft btn">
        Cancel Booking
    </button>
</a>




            <a href="appointment.php">
                <button class="login-btn btn-primary-soft btn">No</button>
            </a>
            <br><br>
        </center>
    </div>
</div>
<script>
    function closePopup(e) {
        if(e.target.classList.contains("overlay")) {
            window.location.href = "appointment.php";
        }
    }
    document.body.classList.add("popup-open");
</script>';
    }
}
?>

</body>
</html>
