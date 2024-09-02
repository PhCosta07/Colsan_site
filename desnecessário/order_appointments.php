<?php
$xmlFile = 'appointments.xml';

// Load the existing appointments XML file
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);

    // Convert SimpleXMLElement to array, including name and cpf
    $appointments = [];
    foreach ($xml->appointment as $appointment) {
        $appointments[] = [
            'date' => (string)$appointment->date,
            'time' => (string)$appointment->time,
            'name' => (string)$appointment->name,
            'cpf' => (string)$appointment->cpf,
        ];
    }

    // Sort appointments by date and time
    usort($appointments, function($a, $b) {
        $dateTimeA = strtotime($a['date'] . ' ' . $a['time']);
        $dateTimeB = strtotime($b['date'] . ' ' . $b['time']);
        return $dateTimeA <=> $dateTimeB;
    });

    // Clear the old XML structure
    $xml = new SimpleXMLElement('<appointments></appointments>');

    // Add sorted appointments back to XML
    foreach ($appointments as $appointment) {
        $appointmentNode = $xml->addChild('appointment');
        $appointmentNode->addChild('date', htmlspecialchars($appointment['date']));
        $appointmentNode->addChild('time', htmlspecialchars($appointment['time']));
        $appointmentNode->addChild('name', htmlspecialchars($appointment['name']));
        $appointmentNode->addChild('cpf', htmlspecialchars($appointment['cpf']));
    }

    // Save the XML file
    $xml->asXML($xmlFile);
}

header('Location: appointments.php');
exit();
