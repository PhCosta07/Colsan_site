<?php
// login.php

session_start(); // Inicia a sessão

function descriptografarDados($dados, $chave) {
    $metodo = 'aes-256-cbc';
    $dados = base64_decode($dados);
    $comprimentoIv = openssl_cipher_iv_length($metodo);
    $iv = substr($dados, 0, $comprimentoIv);
    $dadosCriptografados = substr($dados, $comprimentoIv);
    return openssl_decrypt($dadosCriptografados, $metodo, $chave, 0, $iv);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpfEntrada = $_POST['CPF'];
    $senhaEntrada = $_POST['Senha'];
    $chaveCriptografia = 'chave_teste'; // Use a mesma chave para descriptografia

    // Carrega o arquivo XML contendo os dados dos usuários
    $arquivoXml = 'usuarios.xml';
    if (file_exists($arquivoXml)) {
        $xml = simplexml_load_file($arquivoXml);

        // Flag para indicar se o login foi bem-sucedido
        $loginBemSucedido = false;

        foreach ($xml->usuario as $usuario) {
            $nomeArmazenado = (string)$usuario->nome;
            $cpfArmazenado = (string)$usuario->cpf;
            $senhaArmazenada = (string)$usuario->senha;

            // Descriptografa os dados armazenados
            $nomeDescriptografado = descriptografarDados($nomeArmazenado, $chaveCriptografia);
            $cpfDescriptografado = descriptografarDados($cpfArmazenado, $chaveCriptografia);
            $senhaDescriptografada = descriptografarDados($senhaArmazenada, $chaveCriptografia);

            if ($cpfDescriptografado === $cpfEntrada && $senhaDescriptografada === $senhaEntrada) {
                $loginBemSucedido = true;
                $_SESSION['cpf'] = $cpfDescriptografado; // Armazena o CPF na sessão
                break;
            }
        }

        if ($loginBemSucedido) {
            header("Location: questionário.html"); // Redireciona para a próxima página
            exit();
        } else {
            $erro = "CPF ou Senha inválidos.";
        }
    } else {
        $erro = "Arquivo de dados do usuário não encontrado.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colsan - Login</title>
    <link rel="stylesheet" href="Style login.css">
    <script src="https://kit.fontawesome.com/cf3f0d8b33.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/x-icon" href="img/sangue.png">
</head>
<body>

    <div id="form">
        <form action="login.php" method="POST">
            <h2 class="title">Login</h2>

            <label for="CPF">CPF</label>
            <div class="input">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i>
                <input id="CPF" name="CPF" placeholder="CPF" type="text" required="">
            </div>

            <label for="Senha">Senha</label>
            <div class="input">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                <input id="Senha" name="Senha" placeholder="Senha" type="password" required="">
            </div>

            <div id="btn">
                <button type="submit">Login</button>
            </div>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
        </form>
    </div>

</body>
</html>
