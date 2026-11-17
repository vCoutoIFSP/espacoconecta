<?php
// Configurações do Banco de Dados (Credenciais da Hospedagem / phpMyAdmin)
$host = "localhost";
$usuario = "simplifica";
$senha = "extensao@2024";
$banco = "simplifica_espacoconecta";

// E-mail do Scrum Master (Victor Couto)
$email_scrum_master = "victor.couto@aluno.ifsp.edu.br"; 

$mensagem_status = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome     = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
    $email    = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $assunto  = trim(filter_input(INPUT_POST, 'assunto', FILTER_SANITIZE_SPECIAL_CHARS));
    $conteudo = trim(filter_input(INPUT_POST, 'conteudo', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!empty($nome) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($assunto) && !empty($conteudo)) {
        
        // 1. Salvar no Banco de Dados MySQL
        $salvo_bd = false;
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $stmt = $pdo->prepare("INSERT INTO mensagens_contato (nome, email, assunto, conteudo) VALUES (:nome, :email, :assunto, :conteudo)");
            $stmt->execute([
                ':nome'     => $nome,
                ':email'    => $email,
                ':assunto'  => $assunto,
                ':conteudo' => $conteudo
            ]);
            $salvo_bd = true;
        } catch (PDOException $e) {
            // Caso o banco ainda não esteja configurado, segue para o envio de e-mail
            $salvo_bd = false;
        }

        // 2. Enviar E-mail para o Scrum Master
        $titulo_email = "[Espaço Conecta] Novo Contato: " . $assunto;
        $corpo_email  = "Você recebeu uma nova mensagem pelo portal Espaço Conecta.\n\n";
        $corpo_email .= "Nome: " . $nome . "\n";
        $corpo_email .= "E-mail: " . $email . "\n";
        $corpo_email .= "Assunto: " . $assunto . "\n\n";
        $corpo_email .= "Conteúdo:\n" . $conteudo . "\n";

        $headers  = "From: nao-responda@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $enviado_email = @mail($email_scrum_master, $titulo_email, $corpo_email, $headers);

        if ($salvo_bd || $enviado_email) {
            $mensagem_status = "Mensagem enviada com sucesso! Nosso Scrum Master entrará em contato em breve.";
            $tipo_alerta = "alerta-sucesso";
        } else {
            $mensagem_status = "Erro ao processar o envio. Verifique as credenciais do banco/servidor.";
            $tipo_alerta = "alerta-erro";
        }
    } else {
        $mensagem_status = "Por favor, preencha todos os campos corretamente.";
        $tipo_alerta = "alerta-erro";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contatos e Fale Conosco | Espaço Conecta</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header>
    <nav class="navbar" aria-label="Navegação Principal">
      <a href="index.html" class="brand">
        <img src="https://iili.io/nP1xgqB.png" alt="Logo Espaço Conecta">
        <span>Espaço Conecta</span>
      </a>
      <ul class="nav-links">
        <li><a href="index.html">Início</a></li>
        <li><a href="quem-somos.html">Quem Somos</a></li>
        <li><a href="personas.html">Personas</a></li>
        <li><a href="contato.php" class="ativo">Contatos e Fale Conosco</a></li>
      </ul>
    </nav>
  </header>

  <main class="container" style="padding: 50px 20px;">
    <h1 class="section-title">Contatos e Fale Conosco</h1>
    <div class="divisor"></div>

    <div class="form-card">
      <p style="margin-bottom: 20px; text-align: center;">
        Preencha os campos abaixo para solicitar ações de extensão, tirar dúvidas ou propor parcerias. Sua mensagem será encaminhada diretamente ao nosso <strong>Scrum Master</strong>.
      </p>

      <?php if (!empty($mensagem_status)): ?>
        <div class="alerta <?php echo $tipo_alerta; ?>">
          <?php echo $mensagem_status; ?>
        </div>
      <?php endif; ?>

      <form action="contato.php" method="POST" id="formContato">
        <div class="form-group">
          <label for="nome">Nome Completo *</label>
          <input type="text" id="nome" name="nome" required placeholder="Digite seu nome ou nome da ONG">
        </div>

        <div class="form-group">
          <label for="email">E-mail *</label>
          <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com.br">
        </div>

        <div class="form-group">
          <label for="assunto">Assunto *</label>
          <input type="text" id="assunto" name="assunto" required placeholder="Ex: Solicitação de Consultoria / Parceria">
        </div>

        <div class="form-group">
          <label for="conteudo">Conteúdo *</label>
          <textarea id="conteudo" name="conteudo" rows="6" required placeholder="Descreva sua demanda ou mensagem..."></textarea>
        </div>

        <button type="submit" class="btn" style="width: 100%; font-size: 1.05rem;">Enviar Mensagem ao Scrum Master</button>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 Espaço Conecta — Startup Acadêmica do IFSP Campus Guarulhos.</p>
  </footer>

  <script>
    // Validação rápida em JavaScript no lado do cliente
    document.getElementById('formContato').addEventListener('submit', function(e) {
      const conteudo = document.getElementById('conteudo').value.trim();
      if (conteudo.length < 10) {
        alert('Por favor, detalhe um pouco mais o conteúdo da sua mensagem (mínimo de 10 caracteres).');
        e.preventDefault();
      }
    });
  </script>

</body>
</html>
