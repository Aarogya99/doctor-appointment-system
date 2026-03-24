<?php
session_start();

// Check if user is logged in and usertype is patient ('p')
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    // No appointment ID provided, redirect back
    header("location: appointment.php?message=noid");
    exit();
}

include("../connection.php");

$id = intval($_GET["id"]); // Sanitize ID as integer

if ($id <= 0) {
    // Invalid appointment ID
    header("location: appointment.php?message=invalidid");
    exit();
}

$useremail = $_SESSION["user"];

// Get patient ID from email
$stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    // Patient not found (shouldn't happen if session is correct)
    header("location: ../login.php");
    exit();
}

$userfetch = $res->fetch_assoc();
$userid = $userfetch["pid"];

// Check if appointment belongs to this patient
$stmt = $database->prepare("SELECT appoid FROM appointment WHERE appoid = ? AND pid = ?");
$stmt->bind_param("ii", $id, $userid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    // Appointment not found or doesn't belong to this user
    header("location: appointment.php?message=notfound");
    exit();
}

// Delete the appointment
$stmt = $database->prepare("DELETE FROM appointment WHERE appoid = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Successfully deleted
    header("location: appointment.php?message=deleted");
} else {
    // Failed to delete for some reason
    header("location: appointment.php?message=fail");
}

$stmt->close();
exit();
?>
