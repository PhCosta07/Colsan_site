<?php
// save_appointment.php

session_start(); // Start the session to access session variables

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Retrieve the CPF from the session
    if (!isset($_SESSION['cpf'])) {
        die("User is not logged in.");
    }

    $cpf = $_SESSION['cpf'];

    // Load the existing appointments and user data
    $appointmentsFile = 'appointments.xml';
    $usersFile = 'users.xml';

    $appointmentsXml = file_exists($appointmentsFile) ? new SimpleXMLElement(file_get_contents($appointmentsFile)) : new SimpleXMLElement('<appointments></appointments>');
    $usersXml = file_exists($usersFile) ? new SimpleXMLElement(file_get_contents($usersFile)) : new SimpleXMLElement('<users></users>');

    // Define your encryption key (must be the same as used for encryption)
    $encryptionKey = 'your-secret-key'; // Replace with your actual key

    // Function to decrypt data
    function decryptData($data, $key) {
        $method = 'aes-256-cbc';
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $ivLength);
        $encryptedData = substr($data, $ivLength);
        return openssl_decrypt($encryptedData, $method, $key, 0, $iv);
    }

    // Retrieve the user's name based on CPF and decrypt the data
    $name = '';
    $decryptedCpf = '';

    foreach ($usersXml->user as $user) {
        $storedEncryptedCpf = (string)$user->cpf;
        $storedDecryptedCpf = decryptData($storedEncryptedCpf, $encryptionKey);

        if ($storedDecryptedCpf === $cpf) {
            $encryptedName = (string)$user->nome;
            $name = decryptData($encryptedName, $encryptionKey);
            $decryptedCpf = $storedDecryptedCpf;
            break;
        }
    }

    // Function to check if a time slot is occupied
    function isTimeSlotOccupied($appointmentsXml, $date, $time) {
        foreach ($appointmentsXml->appointment as $appointment) {
            if ((string)$appointment->date == $date && (string)$appointment->time == $time) {
                return true;
            }
        }
        return false;
    }

    // Initialize new time with the provided time
    $newTime = $time;

    // Loop to find an unoccupied time slot by adding 5 minutes
    while (isTimeSlotOccupied($appointmentsXml, $date, $newTime)) {
        $newTime = date("H:i", strtotime($newTime . " +5 minutes"));
    }

    // Save the new appointment to the XML file
    $appointment = $appointmentsXml->addChild('appointment');
    $appointment->addChild('date', htmlspecialchars($date));
    $appointment->addChild('time', htmlspecialchars($newTime));
    $appointment->addChild('name', htmlspecialchars($name)); // Store the decrypted name
    $appointment->addChild('cpf', htmlspecialchars($decryptedCpf)); // Store the decrypted CPF

    // Save the appointments XML file
    $appointmentsXml->asXML($appointmentsFile);

    // Redirect back to the appointments page
    header("Location: agendamento.html");
    exit();
}
?>
