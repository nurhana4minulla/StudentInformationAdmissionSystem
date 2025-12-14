<?php
ob_start();
require_once "../classes/database.php";
ob_clean(); 

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $response = [];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        
        $typo_traps = [
            'gmail.com' => ['gmil.com', 'gmal.com', 'gmaill.com', 'gmai.com', 'gml.co', 'gmail.co', 'gmil.co'],
            'yahoo.com' => ['yaho.com', 'yahooo.com', 'yhoo.com', 'yaho.co'],
            'hotmail.com' => ['hotmil.com', 'hotmal.com', 'hotmail.co'],
            'outlook.com' => ['outlok.com', 'outlook.co']
        ];

        foreach ($typo_traps as $real => $fakes) {
            if (in_array($domain, $fakes)) {
                echo json_encode(['status' => 'invalid_domain', 'suggestion' => $real]);
                exit;
            }
            if ($domain !== $real && strpos($domain, 'gmail') !== false && levenshtein($domain, $real) < 3) {
                 echo json_encode(['status' => 'invalid_domain', 'suggestion' => $real]);
                 exit;
            }
        }

        if (checkdnsrr($domain, "MX")) {
            $domainValid = true;
        } else {
            $domainValid = false;
        }

        if (!$domainValid) {
            echo json_encode(['status' => 'invalid_domain']);
            exit;
        }

    } else {
        echo json_encode(['status' => 'invalid_format']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT student_id FROM student WHERE email_address = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'taken';
        } else {
            $response['status'] = 'available';
        }
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}
?>