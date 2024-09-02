<?php
// register.php

function encryptData($data, $key) {
    $method = 'aes-256-cbc';
    $ivLength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encryptedData = openssl_encrypt($data, $method, $key, 0, $iv);
    return base64_encode($iv . $encryptedData);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['Nome'];
    $cpf = $_POST['CPF'];
    $senha = $_POST['Senha']; // Get the password from the form
    $encryptionKey = 'your-secret-key'; // Use a secure, unique key for encryption

    // Encrypt the data
    $encryptedName = encryptData($nome, $encryptionKey);
    $encryptedCpf = encryptData($cpf, $encryptionKey);
    $encryptedPassword = encryptData($senha, $encryptionKey);

    // Define the XML file to store user data
    $xmlFile = 'users.xml';

    // Load or create the XML file
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
    } else {
        $xml = new SimpleXMLElement('<users></users>');
    }

    // Add new user data
    $user = $xml->addChild('user');
    $user->addChild('nome', htmlspecialchars($encryptedName));
    $user->addChild('cpf', htmlspecialchars($encryptedCpf));
    $user->addChild('senha', htmlspecialchars($encryptedPassword)); // Save encrypted password

    // Save the XML file
    $xml->asXML($xmlFile);

    // Redirect or show a success message
    header("Location: login.php");
    exit();
}
?>
