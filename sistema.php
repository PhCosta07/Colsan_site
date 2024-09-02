<?php
// Define sua chave de criptografia (garanta que seja consistente em todas as operações)
$chaveCriptografia = 'chave_teste'; // Substitua pela sua chave real

// Função para descriptografar dados
function descriptografarDados($dados, $chave) {
    $metodo = 'aes-256-cbc';
    $dados = base64_decode($dados);
    $tamanhoIv = openssl_cipher_iv_length($metodo);
    $iv = substr($dados, 0, $tamanhoIv);
    $dadosCriptografados = substr($dados, $tamanhoIv);
    return openssl_decrypt($dadosCriptografados, $metodo, $chave, 0, $iv);
}

// Manipular a exclusão de compromissos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletar'])) {
    $data = $_POST['data'];
    $hora = $_POST['hora'];

    $arquivoXml = 'compromissos.xml';

    if (file_exists($arquivoXml)) {
        $xml = simplexml_load_file($arquivoXml);
        $compromissos = $xml->compromisso;

        foreach ($compromissos as $compromisso) {
            if ($compromisso->data == $data && $compromisso->hora == $hora) {
                $dom = dom_import_simplexml($compromisso);
                $dom->parentNode->removeChild($dom);
                break;
            }
        }

        $xml->asXML($arquivoXml);
    }

    header('Location: sistema.php'); // Redirecionar para a página de compromissos
    exit();
}

// Manipular a ordenação de compromissos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ordenar'])) {
    $arquivoXml = 'compromissos.xml';

    if (file_exists($arquivoXml)) {
        $xml = simplexml_load_file($arquivoXml);

        // Converter SimpleXMLElement para array, incluindo nome e cpf
        $compromissos = [];
        foreach ($xml->compromisso as $compromisso) {
            $compromissos[] = [
                'data' => (string)$compromisso->data,
                'hora' => (string)$compromisso->hora,
                'nome' => (string)$compromisso->nome,
                'cpf' => (string)$compromisso->cpf,
            ];
        }

        // Ordenar compromissos por data e hora
        usort($compromissos, function($a, $b) {
            $dataHoraA = strtotime($a['data'] . ' ' . $a['hora']);
            $dataHoraB = strtotime($b['data'] . ' ' . $b['hora']);
            return $dataHoraA <=> $dataHoraB;
        });

        // Limpar a estrutura XML antiga
        $xml = new SimpleXMLElement('<compromissos></compromissos>');

        // Adicionar compromissos ordenados de volta ao XML
        foreach ($compromissos as $compromisso) {
            $nodoCompromisso = $xml->addChild('compromisso');
            $nodoCompromisso->addChild('data', htmlspecialchars($compromisso['data']));
            $nodoCompromisso->addChild('hora', htmlspecialchars($compromisso['hora']));
            $nodoCompromisso->addChild('nome', htmlspecialchars($compromisso['nome']));
            $nodoCompromisso->addChild('cpf', htmlspecialchars($compromisso['cpf']));
        }

        // Salvar o arquivo XML
        $xml->asXML($arquivoXml);
    }

    header('Location: sistema.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Colsan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="img/sangue.png">
</head>
<body>

    <!-- Navegação -->
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand ms-5" href="sistema.php">SISTEMA COLSAN</a>
    </nav>

    <!-- Lista de Compromissos -->
    <div class="container mt-5">
        <h1 class="mb-4">Compromissos Agendados</h1>
        
        <!-- Botão para Ordenar Compromissos -->
        <form method="POST" action="sistema.php" class="mb-3">
            <input type="hidden" name="ordenar" value="1">
            <button type="submit" class="btn btn-primary">Ordenar Compromissos</button>
        </form>

        <ul class="list-group">
            <?php
            $arquivoCompromissos = 'compromissos.xml';
            $arquivoUsuarios = 'usuarios.xml';

            if (file_exists($arquivoCompromissos) && file_exists($arquivoUsuarios)) {
                $xmlCompromissos = simplexml_load_file($arquivoCompromissos);
                $xmlUsuarios = simplexml_load_file($arquivoUsuarios);

                foreach ($xmlCompromissos->compromisso as $compromisso) {
                    $data = (string)$compromisso->data;
                    $hora = (string)$compromisso->hora;
                    $cpf = (string)$compromisso->cpf;

                    $cpfEncontrado = false;
                    $nomeEncontrado = false;

                    // Encontrar o usuário correspondente pelo CPF
                    foreach ($xmlUsuarios->usuario as $usuario) {
                        $cpfDescriptografado = descriptografarDados((string)$usuario->cpf, $chaveCriptografia);

                        if ($cpfDescriptografado === $cpf) {
                            $cpfEncontrado = true;
                            $nomeDescriptografado = descriptografarDados((string)$usuario->nome, $chaveCriptografia);

                            if (!empty($nomeDescriptografado)) {
                                $nomeEncontrado = true;
                                echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                                echo "$data às $hora - Nome: $nomeDescriptografado, CPF: $cpfDescriptografado";
                                echo "<form method='POST' action='sistema.php' style='display:inline;'>
                                        <input type='hidden' name='deletar' value='1'>
                                        <input type='hidden' name='data' value='$data'>
                                        <input type='hidden' name='hora' value='$hora'>
                                        <button type='submit' class='btn btn-danger btn-sm'>Excluir</button>
                                      </form>";
                                echo "</li>";
                            }
                            break;
                        }
                    }

                    if (!$cpfEncontrado) {
                        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                        echo "$data às $hora - CPF não encontrado";
                        echo "<form method='POST' action='sistema.php' style='display:inline;'>
                                <input type='hidden' name='deletar' value='1'>
                                <input type='hidden' name='data' value='$data'>
                                <input type='hidden' name='hora' value='$hora'>
                                <button type='submit' class='btn btn-danger btn-sm'>Excluir</button>
                              </form>";
                        echo "</li>";
                    } elseif ($cpfEncontrado && !$nomeEncontrado) {
                        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                        echo "$data às $hora - Nome não encontrado para CPF: $cpf";
                        echo "<form method='POST' action='sistema.php' style='display:inline;'>
                                <input type='hidden' name='deletar' value='1'>
                                <input type='hidden' name='data' value='$data'>
                                <input type='hidden' name='hora' value='$hora'>
                                <button type='submit' class='btn btn-danger btn-sm'>Excluir</button>
                              </form>";
                        echo "</li>";
                    }
                }
            } else {
                echo "<li class='list-group-item'>Nenhum compromisso encontrado</li>";
            }
            ?>
        </ul>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
