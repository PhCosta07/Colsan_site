<?php
// cadastro.php

// Define a sua chave de criptografia (deve ser a mesma usada para criptografia)
$chaveCriptografia = 'chave_teste'; // Substitua pela sua chave real

// Função para criptografar dados
function criptografarDados($dados, $chave) {
    $metodo = 'aes-256-cbc';
    $comprimentoIv = openssl_cipher_iv_length($metodo);
    $iv = openssl_random_pseudo_bytes($comprimentoIv);
    $dadosCriptografados = openssl_encrypt($dados, $metodo, $chave, 0, $iv);
    return base64_encode($iv . $dadosCriptografados);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['Nome'];
    $cpf = $_POST['CPF'];
    $senha = $_POST['Senha']; // Obtém a senha do formulário

    // Criptografa os dados
    $nomeCriptografado = criptografarDados($nome, $chaveCriptografia);
    $cpfCriptografado = criptografarDados($cpf, $chaveCriptografia);
    $senhaCriptografada = criptografarDados($senha, $chaveCriptografia);

    // Define o arquivo XML para armazenar os dados do usuário
    $arquivoXml = 'usuarios.xml';

    // Carrega ou cria o arquivo XML
    if (file_exists($arquivoXml)) {
        $xml = simplexml_load_file($arquivoXml);
    } else {
        $xml = new SimpleXMLElement('<usuarios></usuarios>');
    }

    // Adiciona novos dados de usuário
    $usuario = $xml->addChild('usuario');
    $usuario->addChild('nome', htmlspecialchars($nomeCriptografado));
    $usuario->addChild('cpf', htmlspecialchars($cpfCriptografado));
    $usuario->addChild('senha', htmlspecialchars($senhaCriptografada)); // Salva a senha criptografada

    // Salva o arquivo XML
    $xml->asXML($arquivoXml);

    // Redireciona ou exibe uma mensagem de sucesso
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colsan - Cadastro</title>
    <link rel="icon" type="image/x-icon" href="img/sangue.png">
    <link rel="stylesheet" href="Style cadastro.css">
    <script src="https://kit.fontawesome.com/cf3f0d8b33.js" crossorigin="anonymous"></script>
</head>
<body>
    <div id="form">
        <!-- Changed form action to submit to itself -->
        <form action="cadastro.php" method="POST">
            <h2 class="title">Cadastrar</h2>

            <label for="Nome">Nome completo</label>
            <div class="input">
                <i class="fa-regular fa-user"></i>
                <input id="nome" name="Nome" placeholder="Nome" type="text" required>
            </div>

            <label for="Data">Data Nascimento</label>
            <div class="input">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <input id="data" name="data" placeholder="data" type="date" max="2008-09-01"required>
            </div>

            <label for="CPF">CPF</label>
            <div class="input">
                <i class="fa-regular fa-id-card"></i>
                <input id="CPF" name="CPF" placeholder="CPF" type="text" required>
            </div>

            <label for="sexo">Sexo</label>
            <div class="input">
                <div class="radio-btn">
                    <input type="radio" class="form_new_input" id="masculino" name="sexo" value="Masculino" required="required">
                    <label for="masculino" class="radio_label form_label"> <span class="radio_new_btn"></span> Masculino</label>
                    <hr class="featurette-divider">
                    <input type="radio" class="form_new_input" id="feminino" name="sexo" value="Feminino" required="required">
                    <label for="feminino" class="radio_label form_label"> <span class="radio_new_btn"></span> Feminino</label>
                </div>
            </div>

            <label for="nacionalidade">Nacionalidade</label>
            <div class="input">
                <i class="fa-solid fa-location-dot"></i>
                <input id="nacionalidade" name="nacionalidade" placeholder="nacionalidade" type="text" required>
            </div>

            <label for="endereco">Endereço</label>
            <div class="input">
                <i class="fa-solid fa-location-dot"></i>
                <input id="endereco" name="endereco" placeholder="endereço" type="text" required>
            </div>

            <label for="cep">CEP</label>
            <div class="input">
                <i class="fa-solid fa-location-dot"></i>
                <input id="cep" name="cep" placeholder="cep" type="text" required>
            </div>

            <label for="cidade">Cidade</label>
            <div class="input">
                <i class="fa-solid fa-location-dot"></i>
                <input id="cidade" name="cidade" placeholder="cidade" type="text" required>
            </div>

            <label for="estado">Estado</label>
            <div class="input">
                <i class="fa-solid fa-location-dot"></i>
                <input id="estado" name="estado" placeholder="estado" type="text" required>
            </div>

            <label for="telefone">Telefone</label>
            <div class="input">
                <i class="fa-solid fa-phone"></i>
                <input id="telefone" name="telefone" placeholder="telefone" type="text" required>
            </div>

            <label for="Profissao">Profissão</label>
            <div class="input">
                <i class="fa-solid fa-briefcase"></i> 
                <input id="Profissao" name="Profissao" placeholder="Profissão" type="text" required>
            </div>

            <label for="Email">Email</label>
            <div class="input">
                <i class="fa-regular fa-envelope"></i>   
                <input id="email" name="Email" placeholder="Email" type="email" required>
            </div>

            <label for="Senha">Senha</label>
            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input id="senha" name="Senha" placeholder="Senha" type="password" required>
            </div>

            <div id="btn">
                <button type="submit">Cadastrar</button>
            </div>
        </form>
    </div>
</body>
</html>
