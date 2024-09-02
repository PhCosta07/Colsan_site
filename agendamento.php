<?php
session_start(); // Inicia a sessão para acessar as variáveis de sessão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = $_POST['date'];
    $hora = $_POST['time'];

    // Recupera o CPF da sessão
    if (!isset($_SESSION['cpf'])) {
        die("Usuário não está logado.");
    }

    $cpf = $_SESSION['cpf'];

    // Carrega os dados existentes de compromissos e usuários
    $arquivoCompromissos = 'compromissos.xml';
    $arquivoUsuarios = 'usuarios.xml';

    $xmlCompromissos = file_exists($arquivoCompromissos) ? new SimpleXMLElement(file_get_contents($arquivoCompromissos)) : new SimpleXMLElement('<compromissos></compromissos>');
    $xmlUsuarios = file_exists($arquivoUsuarios) ? new SimpleXMLElement(file_get_contents($arquivoUsuarios)) : new SimpleXMLElement('<usuarios></usuarios>');

    // Define a chave de criptografia (deve ser a mesma usada para criptografia)
    $chaveCriptografia = 'chave_teste'; // Substitua pela sua chave real

    // Descriptografa o nome do usuário com base no CPF armazenado
    foreach ($xmlUsuarios->usuario as $usuario) {
        $cpfArmazenado = descriptografarDados((string)$usuario->cpf, $chaveCriptografia);
        if ($cpfArmazenado === $cpf) {
            $nomeUsuario = descriptografarDados((string)$usuario->nome, $chaveCriptografia);
            break;
        }
    }

    if (!isset($nomeUsuario)) {
        die("Usuário não encontrado.");
    }

    // Cria uma nova entrada de compromisso
    $novoCompromisso = $xmlCompromissos->addChild('compromisso');
    $novoCompromisso->addChild('data', $data);
    $novoCompromisso->addChild('hora', $hora);
    $novoCompromisso->addChild('nome', $nomeUsuario);
    $novoCompromisso->addChild('cpf', $cpf); // Supondo que você deseja armazenar o CPF

    // Salva os compromissos atualizados no arquivo
    $xmlCompromissos->asXML($arquivoCompromissos);

    // Redireciona ou exibe uma mensagem de sucesso
    echo "<script>alert('Obrigado por escolher doar sangue! Seu horário foi agendado.'); window.location.href='index.html';</script>";
    exit();
}

function descriptografarDados($dados, $chave) {
    $metodo = 'aes-256-cbc';
    $dados = base64_decode($dados);
    $comprimentoIv = openssl_cipher_iv_length($metodo);
    $iv = substr($dados, 0, $comprimentoIv);
    $dadosCriptografados = substr($dados, $comprimentoIv);
    return openssl_decrypt($dadosCriptografados, $metodo, $chave, 0, $iv);
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Colsan - Agendamento</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="img/sangue.png">
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">  
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
        <!-- jQuery UI CSS -->
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    
        <!-- jQuery and jQuery UI Scripts -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        
        <script>
            $(function () {
                $("#datepicker").datepicker({
                    dateFormat: 'dd/mm/yy',
                    dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
                    dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
                    dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                    monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                    nextText: 'Próximo',
                    prevText: 'Anterior',
                    minDate: "-1D",
                    beforeShowDay: function (d) {
                        var day = d.getDay();
                        return [day != 0];
                    }
                });
            });
        </script>
</head>
<body>
    <!-- Navigation-->
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand"><img src="img/colsan_header.png" alt="Colsan Logo"></a>
        </div>
    </nav>
    <!-- Header-->
    <header class="header">
        <div class="container py-3 px-lg-5 mb-5">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bolder">Agende já Seu horário</h1>
            </div>
        </div>
    </header>
    <!-- Section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-5">
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <div class="col mb-5">
                    <div class="card h-100">
                        <!-- local image-->
                        <img class="card-img-top" src="img/colsan_sorocaba.jpg" alt="Colsan Sorocaba" />
                        <!-- local details-->
                        <div class="card-body p-4">
                            <div class="text-center">
                                <!-- local name-->
                                <h5 class="fw-bolder">Colsan Sorocaba</h5>
                                <p class="mb-1 mt-5">Av. Comendador Pereira Inácio, 564</p>
                                Sorocaba – SP
                            </div>
                        </div>
                        <!-- Product actions-->
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                        </div>
                    </div>
                </div>
                <div class="col mb-5">
                    <div class="card h-100">
                        <!-- local image-->
                        <img class="card-img-top" src="img/colsan_jund.jfif" alt="Colsan Jundiaí" />
                        <!-- local details-->
                        <div class="card-body p-4">
                            <div class="text-center">
                                <!-- local name-->
                                <h5 class="fw-bolder">Colsan Jundiaí</h5>
                                <p class="mb-1 mt-5">Rua XV de Novembro, 1848</p>
                                Jundiaí – SP
                            </div>
                        </div>
                        <!-- local actions-->
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center">
                                <button type="button" class="btn btn-outline-dark mt-auto" data-bs-toggle="modal" data-bs-target="#scheduleModal">Agendar Horário</button>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_mariocovas.jpg" height="140" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Hospital Estadual Mário Covas</h5>
                                    <p class="mb-1 mt-4">Rua Dr. Henrique Calderazzo 321</p>
                                    Santo André – SP
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_sãobernardo.jfif" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Hemocentro Regional São Bernardo do Campo</h5>
                                    <p class="mb-1">Rua Pedro Jacobucci, 440 – Jardim das Américas</p>
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_servidorpublico.jfif" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Hospital do Servidor Público Municipal</h5>
                                    <p class="mb-1">R. Castro Alves, 60 – 4º andar (próximo ao Metrô Vergueiro)</p>
                                    São Paulo – SP
                                    <!-- Product price-->
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_tatuapé.jfif" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Hospital Municipal Dr. Carmino Caricchio</h5>
                                    <p class="mb-1">Av. Celso Garcia 4815 – Tatuapé – Entrada pela Rua Siria, 25</p>
                                    São Paulo – SP
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_mauá.jfif" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Posto de Coleta Mauá</h5>
                                    <p class="mb-1">Centro de Referência Saúde da Mulher, Criança e Adolescente Rua Luiz Lacava, 229</p>
                                    Mauá - SP 
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- local image-->
                            <img class="card-img-top" src="img/colsan_santos.jpg" alt="..." />
                            <!-- local details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- local name-->
                                    <h5 class="fw-bolder">Hemonúcleo de Santos</h5>
                                    <p class="mb-1">Rua Dr. Henrique Calderazzo 321 </p>
                                    Santo André – SP 
                                </div>
                            </div>
                            <!-- local actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><button type="button" class="btn btn-outline-dark mt-auto" href="#" disabled>Agendar Horário</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Modal-->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Agendar Horário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="date" class="form-label">Data</label>
                            <input type="text" class="form-control" id="datepicker" name="date" min="2024-09-01" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Horário</label>
                            <input type="text" class="form-control" id="timeInput" name="time" required>
                            <div id="timePicker" class="dropdown-menu"></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="documentCheck" name="documentCheck" required>
                        <label class="form-check-label" for="documentCheck">
                            Eu confirmo que levarei um documento com foto e que, se eu for menor de 18 anos, irei com meus pais para assinar a declaração para doação de sangue.
                        </label>
                    </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Footer-->
    <footer class="footer">
        <div class="container"><p class="m-0 text-center text-white"></p></div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <!-- Script for time picker -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timeInput = document.getElementById('timeInput');
            const timePicker = document.getElementById('timePicker');
    
            // Function to format time in 12-hour format with AM/PM
            const formatTime = (date) => {
                let hours = date.getHours();
                let minutes = date.getMinutes().toString().padStart(2, '0');
                let period = 'AM';
    
                if (hours >= 12) {
                    period = 'PM';
                    if (hours > 12) {
                        hours -= 12;
                    }
                } else if (hours === 0) {
                    hours = 12;
                }
    
                hours = hours.toString().padStart(2, '0');
                return `${hours}:${minutes} ${period}`;
            };
    
            // Generate time options in 30-minute intervals
            const generateTimeOptions = () => {
                const options = [];
                const startTime = new Date();
                startTime.setHours(7, 30, 0, 0); // Start time: 07:30
                const endTime = new Date();
                endTime.setHours(12, 30, 0, 0); // End time: 12:30
    
                let currentTime = startTime;
                while (currentTime <= endTime) {
                    options.push(formatTime(currentTime));
                    currentTime.setMinutes(currentTime.getMinutes() + 30); // Increment by 30 minutes
                }
                return options;
            };
    
            const timeOptions = generateTimeOptions();
    
            // Populate the time picker dropdown
            const populateTimePicker = () => {
                timePicker.innerHTML = ''; // Clear previous options
                timeOptions.forEach(time => {
                    const option = document.createElement('a');
                    option.classList.add('dropdown-item');
                    option.href = '#';
                    option.textContent = time;
                    option.addEventListener('click', () => {
                        timeInput.value = time;
                        timePicker.classList.remove('show');
                    });
                    timePicker.appendChild(option);
                });
            };
    
            // Show time picker when time input is focused
            timeInput.addEventListener('focus', () => {
                timePicker.classList.add('show');
            });
    
            // Hide time picker when clicking outside
            document.addEventListener('click', (e) => {
                if (!timeInput.contains(e.target) && !timePicker.contains(e.target)) {
                    timePicker.classList.remove('show');
                }
            });
    
            // Initialize time picker
            populateTimePicker();
        });
    </script>
    
    <!-- Style for time picker -->
    <style>
        #timePicker {
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            z-index: 1050;
            max-height: 200px;
            overflow-y: auto;
        }
        #timePicker .dropdown-item {
            cursor: pointer;
        }
        #timePicker .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .dropdown-menu.show {
            display: block;
        }
    </style>
</body>
</html>