<?php

session_start();
date_default_timezone_set('Asia/Kathmandu');
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
    }
}else{
    header("location: ../login.php");
}

if($_POST){
    include("../connection.php");

    $title = $_POST["title"];
    $docid = $_POST["docid"];
    $nop = $_POST["nop"];
    $date = $_POST["date"];
    $scheduletime = $_POST["scheduletime"];
    $endtime = $_POST["endtime"];

    // Validate times
    if (strtotime($endtime) <= strtotime($scheduletime)) {
        echo "<p style='color:red; text-align:center;'>Error: Session end time must be later than start time.</p>";
        exit;
    }

    // Prepare statement
    $stmt = $database->prepare("INSERT INTO schedule (docid, title, scheduledate, scheduletime, endtime, nop) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssi", $docid, $title, $date, $scheduletime, $endtime, $nop);

    if ($stmt->execute()) {
        header("location: schedule.php?action=session-added&title=" . urlencode($title));
    } else {
        echo "Error: " . $stmt->error;
    }
}
