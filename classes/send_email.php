<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function getEmailTemplate($title, $bodyContent) {
    
    $logoUrl = 'https://images.seeklogo.com/logo-png/35/1/western-mindanao-state-university-logo-png_seeklogo-352457.png'; 

    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; line-height: 1.6; background-color: #f4f4f4;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
            <tr>
                <td align="center">
                    <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; margin-top: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        
                        <tr>
                            <td align="center" style="padding: 20px 0;">
                                <img src="' . $logoUrl . '" alt="WMSU Logo" style="max-width: 150px; height: auto;">
                                <h1 style="color: #333; margin-top: 10px; margin-bottom: 0; font-size: 24px;">Western Mindanao State University</h1>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 30px 40px;">
                                <h2 style="color: #004a99; margin-top: 0;">' . htmlspecialchars($title) . '</h2>
                                <div style="color: #555; font-size: 16px;">
                                    ' . $bodyContent . '
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td style="background-color: #f9f9f9; padding: 20px 40px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                <p style="color: #888; font-size: 12px; margin: 0;">
                                    &copy; ' . date('Y') . ' Western Mindanao State University. All rights reserved.<br>
                                    This is an automated email. Please do not reply.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="height: 30px;">&nbsp;</td>
            </tr>
        </table>
    </body>
    </html>
    ';
}

function sendConfirmationEmail($studentEmail, $studentName, $program, $dateSubmitted) {
    $mail = new PHPMailer(true); 

    try {
        // server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        
        //  CREDENTIALS  
        $mail->Username   = 'hanacasan9@gmail.com'; // Gmail address
        $mail->Password   = 'daue pojp rmgw hjet'; //  Gmail "App Password"

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom($mail->Username, 'WMSU Admissions'); // Set 'From' to your email
        $mail->addAddress($studentEmail, $studentName);
        $mail->addReplyTo('no-reply@wmsu.edu.ph', 'No Reply');

        $title = "Application Confirmation";
        
        $body = '
            <p>Dear ' . htmlspecialchars($studentName) . ',</p>
            <p>Thank you for submitting your application to Western Mindanao State University. We have successfully received your information.</p>
            <p><strong>Application Details:</strong></p>
            <ul style="list-style-type: none; padding-left: 0;">
                <li><strong>Program:</strong> ' . htmlspecialchars($program) . '</li>
                <li><strong>Date Submitted:</strong> ' . htmlspecialchars($dateSubmitted) . '</li>
            </ul>
            <p>Your application will now be reviewed by our admissions team. Please keep an eye on your email for any further updates.</p>
            <p>Thank you for choosing WMSU!</p>
            <br>
            <p><strong>The WMSU Admissions Team</strong></p>
        ';
        
        $htmlBody = getEmailTemplate($title, $body);

        $altBody = "Dear $studentName,\n\n" .
                   "Thank you for submitting your application to Western Mindanao State University. We have successfully received your information.\n\n" .
                   "Application Details:\n" .
                   "- Program: $program\n" .
                   "- Date Submitted: $dateSubmitted\n\n" .
                   "Your application will now be reviewed by our admissions team. Please keep an eye on your email for any further updates.\n\n" .
                   "Thank you for choosing WMSU!\n\n" .
                   "The WMSU Admissions Team";

        $mail->isHTML(true);
        $mail->Subject = 'WMSU Admission Application Confirmation';
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();
        
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}


function sendAdminNotificationEmail($studentName, $program, $admissionType, $dateSubmitted) {
    $mail = new PHPMailer(true);

    // ADMIN EMAIL
    $adminEmail = 'hanacasan9@gmail.com'; 

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // CREDENTIALS - same as above
        $mail->Username   = 'hanacasan9@gmail.com'; //  full Gmail address
        $mail->Password   = 'daue pojp rmgw hjet'; //  Gmail "App Password"


        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom($mail->Username, 'WMSU Admission System');
        $mail->addAddress($adminEmail, 'WMSU Admin');

        $title = "New Student Application";
        
        $body = '
            <p>A new student application has been submitted to the admission system.</p>
            <p><strong>Applicant Details:</strong></p>
            <ul style="list-style-type: none; padding-left: 0;">
                <li><strong>Name:</strong> ' . htmlspecialchars($studentName) . '</li>
                <li><strong>Program:</strong> ' . htmlspecialchars($program) . '</li>
                <li><strong>Admission Type:</strong> ' . htmlspecialchars($admissionType) . '</li>
                <li><strong>Date Submitted:</strong> ' . htmlspecialchars($dateSubmitted) . '</li>
            </ul>
            <p>Please log in to the admin dashboard to review the full application.</p>
            <br>
            <p style="text-align: center;">
                <a href="http://localhost/mysystem/admin/login.php" style="padding: 12px 20px; background-color: #004a99; color: #ffffff; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Go to Admin Dashboard
                </a>
            </p>
        ';

        $htmlBody = getEmailTemplate($title, $body);

        $altBody = "A new student application has been submitted.\n\n" .
                   "Applicant Details:\n" .
                   "- Name: $studentName\n" .
                   "- Program: $program\n" .
                   "- Admission Type: $admissionType\n" .
                   "- Date Submitted: $dateSubmitted\n\n" .
                   "Please log in to the admin dashboard to review the full application.";

        $mail->isHTML(true);
        $mail->Subject = "New Student Application: $studentName";
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();
        
    } catch (Exception $e) {
        error_log("Admin notification could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

function sendEnrollmentNotification($studentEmail, $studentName, $program) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'hanacasan9@gmail.com'; 
        $mail->Password   = 'daue pojp rmgw hjet'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom($mail->Username, 'WMSU Admissions');
        $mail->addAddress($studentEmail, $studentName);

        // Content
        $title = "Admission Status Update: Enrolled";
        
        $body = '
            <p>Dear ' . htmlspecialchars($studentName) . ',</p>
            <p>Congratulations! We are pleased to inform you that your application for <strong>' . htmlspecialchars($program) . '</strong> at Western Mindanao State University has been approved.</p>
            <p><strong>Status: <span style="color:green;">ENROLLED</span></strong></p>
            <p>You may now proceed with the next steps of your enrollment process at the university. Please bring your original documents for verification.</p>
            <br>
            <p>Welcome to WMSU!</p>
        ';
        
        $htmlBody = getEmailTemplate($title, $body);

        $mail->isHTML(true);
        $mail->Subject = 'WMSU Admission Approved - Enrolled';
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Enrollment email failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendNewApplicantNotifications($studentName, $program, $college) {
    $mail = new PHPMailer(true); 
    
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'hanacasan9@gmail.com'; 
    $mail->Password   = 'daue pojp rmgw hjet'; // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->setFrom('hanacasan9@gmail.com', 'WMSU Admission System');

    try {
        // EMAIL 1: To Super Admin (General Notif)
        $mail->addAddress('hanacasan9@gmail.com'); 
        
        $adminSubject = "New Student Application: $studentName";
        $adminContent = "
            <p>A new student has submitted their application.</p>
            <ul style='background-color: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; list-style: none;'>
                <li style='margin-bottom: 8px;'><strong>Name:</strong> $studentName</li>
                <li style='margin-bottom: 8px;'><strong>Program:</strong> $program</li>
                <li><strong>College:</strong> $college</li>
            </ul>
            <p>Please login to the Admin Dashboard to review.</p>
            <br>
            <p style='text-align: center;'>
                <a href='http://localhost/mysystem/admin/login.php' style='padding: 10px 20px; background-color: #A40404; color: #ffffff; text-decoration: none; border-radius: 5px; display: inline-block;'>
                    Go to Admin Dashboard
                </a>
            </p>
        ";
        
        $mail->isHTML(true);
        $mail->Subject = $adminSubject;
        $mail->Body    = getEmailTemplate("New Application Received", $adminContent);
        $mail->send();
        
        $mail->clearAddresses(); 

        // EMAIL 2: To Admissions Office
        $mail->addAddress('hanacasan9@gmail.com'); 
        
        $aoSubject = "[AO - $college] New Applicant for Review";
        $aoContent = "
            <p><strong>Attention: Admissions Officer ($college)</strong></p>
            <p>You have a new applicant pending review in your department.</p>
            <div style='background-color: #f9f9f9; padding: 15px; border-left: 5px solid #A40404; margin: 20px 0;'>
                <strong>Applicant:</strong> $studentName <br>
                <strong>Program:</strong> $program
            </div>
            <p>Please check your specific college dashboard to evaluate this student.</p>
        ";

        $mail->Subject = $aoSubject;
        $mail->Body    = getEmailTemplate("Applicant for Review", $aoContent); 
        $mail->send();

        return true;

    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}