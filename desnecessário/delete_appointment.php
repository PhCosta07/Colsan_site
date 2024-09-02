<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    $xmlFile = 'appointments.xml';
    
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        $appointments = $xml->appointment;
        
        foreach ($appointments as $appointment) {
            if ($appointment->date == $date && $appointment->time == $time) {
                $dom = dom_import_simplexml($appointment);
                $dom->parentNode->removeChild($dom);
                break;
            }
        }
        
        $xml->asXML($xmlFile);
    }
    
    header('Location: appointments.php'); // Redirect to the appointments page
    exit();
}
?>
