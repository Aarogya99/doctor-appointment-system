<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

session_start();
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

include("../connection.php");

if (isset($_GET['id'])) {
    $appoid = intval($_GET['id']);
    
    // Approve the appointment
    $stmt = $database->prepare("UPDATE appointment SET status = 'approved' WHERE appoid = ?");
    $stmt->bind_param("i", $appoid);
    $stmt->execute();
    
    // Fetch appointment and patient details for email
    $stmt2 = $database->prepare("SELECT patient.pname, patient.pemail, schedule.title, schedule.scheduledate, schedule.scheduletime 
                                FROM appointment 
                                INNER JOIN patient ON appointment.pid = patient.pid
                                INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                WHERE appointment.appoid = ?");
    $stmt2->bind_param("i", $appoid);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        $patientName = $row['pname'];
        $patientEmail = $row['pemail'];
        $sessionTitle = $row['title'];
        $sessionDate = $row['scheduledate'];

        // FORMAT TIME PROPERLY HERE
        $sessionTimeRaw = $row['scheduletime'];
        $sessionTime = date("h:i A", strtotime($sessionTimeRaw));

        // Send email notification
        $mail = new PHPMailer(true);
        try {
            // SMTP server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';                // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'arogyaadhikari1@gmail.com'; // Your Gmail address
            $mail->Password = 'grzubmbgxyaqzspl';          // Your Gmail app password (16 chars)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email details
            $mail->setFrom('arogyaadhikari1@gmail.com', 'DAMS Clinic');
            $mail->addAddress($patientEmail, $patientName);
            $mail->Subject = 'Appointment Approved - ' . $sessionTitle;
            
            $mailBody = "Dear $patientName,\n\n".
                        "Your appointment for the session '$sessionTitle' has been approved.\n".
                        "Date: $sessionDate\n".
                        "Time: $sessionTime\n\n".
                        "Thank you for choosing our service.\n\n".
                        "Best Regards,\nDAMS Clinic";
            
            $mail->Body = $mailBody;

            $mail->send();

            // Redirect or show success message
            header("Location: appointment.php?msg=approved_email_sent");
            exit();
        } catch (Exception $e) {
            // Could not send email, log or show error
            header("Location: appointment.php?msg=email_error");
            exit();
        }
    } else {
        // Appointment details not found
        header("Location: appointment.php?msg=appointment_not_found");
        exit();
    }
} else {
    header("Location: appointment.php");
    exit();
}
?>
