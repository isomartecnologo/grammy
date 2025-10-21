<?php
include 'conexao.php';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome_completo = $_POST['nome_completo'];
    $nome_login = $_POST['nome_login'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $grupo = $_POST['grupo'];

    // Verifica se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        $erro = "As senhas não são iguais.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome_completo, nome_login, senha, grupo) VALUES (?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("ssss", $nome_completo, $nome_login, $senha_hash, $grupo);

        if ($stmt->execute()) {
            $sucesso = "Usuário cadastrado com sucesso!";
        } else {
            if ($conexao->errno == 1062) {
                $erro = "Nome de login já existe.";
            } else {
                $erro = "Erro ao cadastrar: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Excluir usuário
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    // Redireciona para evitar reenvio ao atualizar
    header("Location: cadastro.php");
    exit();
}

// Buscar todos os usuários
$sql = "SELECT id, nome_completo, nome_login, grupo FROM usuarios ORDER BY id";
$resultado = $conexao->query($sql);
$usuarios = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastro de Usuários - Grammy 2025</title>
  <style>
    body {
      background: linear-gradient(135deg, #0c1a3e, #1a2d64);
      color: white;
      font-family: Arial, sans-serif;
      padding: 30px;
    }
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: rgba(10, 15, 50, 0.9);
      border: 3px solid gold;
      border-radius: 20px;
      padding: 40px 20px;
      box-shadow: 0 0 30px rgba(255, 215, 0, 0.5);
    }
    h2 { text-align: center; color: gold; }
    .form-group {
      margin: 15px 0;
    }
    label {
      display: block;
      margin-bottom: 5px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      border: none;
      border-radius: 5px;
    }
    .radio-group {
      margin: 10px 0;
    }
    button {
      background: #00bfff;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 30px 0;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #00bfff;
    }
    th {
      background: rgba(0, 191, 255, 0.3);
      color: gold;
    }
    .btn-excluir {
      background: #e74c3c;
      color: white;
      padding: 6px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .mensagem {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      text-align: center;
      font-weight: bold;
    }
    .erro {
      background: #f8d7da;
      color: #721c24;
    }
    .sucesso {
      background: #d4edda;
      color: #155724;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>📝 Cadastro de Usuários</h2>

    <!-- Mensagens -->
    <?php if (isset($erro)): ?>
      <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if (isset($sucesso)): ?>
      <div class="mensagem sucesso"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <!-- Formulário de Cadastro -->
    <form method="POST">
      <div class="form-group">
        <label>Nome completo</label>
        <input type="text" name="nome_completo" required />
      </div>
      <div class="form-group">
        <label>Nome de login</label>
        <input type="text" name="nome_login" required />
      </div>
      <div class="form-group">
        <label>Senha</label>
        <input type="password" name="senha" id="senha" required />
      </div>
      <div class="form-group">
        <label>Confirmar Senha</label>
        <input type="password" name="confirmar_senha" id="confirmar_senha" required />
        <span id="erroSenha" style="color:#e74c3c; font-size:0.9em;"></span>
      </div>
      <div class="form-group radio-group">
        <label><input type="radio" name="grupo" value="gerente" required> Gerente</label>
        <label><input type="radio" name="grupo" value="jurado"> Jurado</label>
      </div>
      <button type="submit" name="cadastrar">Cadastrar Usuário</button>
    </form>

    <hr style="border:1px solid #00bfff; margin:30px 0;">

    <!-- Lista de Usuários Cadastrados -->
    <h2>👥 Usuários Cadastrados</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome Completo</th>
          <th>Login</th>
          <th>Grupo</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nome_completo']) ?></td>
            <td><?= htmlspecialchars($u['nome_login']) ?></td>
            <td><?= ucfirst($u['grupo']) ?></td>
            <td>
              <a href="?excluir=<?= $u['id'] ?>" 
                 onclick="return confirm('Tem certeza que deseja excluir <?= addslashes($u['nome_completo']) ?>?');">
                <button class="btn-excluir">🗑️ Excluir</button>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <p style="text-align:center;margin-top:30px;">
      <a href="index.html" style="color:gold;">⬅️ Voltar para Login</a>
    </p>
  </div>

  <script>
    const senha = document.getElementById('senha');
    const confirmar = document.getElementById('confirmar_senha');
    const erroSpan = document.getElementById('erroSenha');

    function verificarSenhas() {
      if (confirmar.value && senha.value !== confirmar.value) {
        erroSpan.textContent = "As senhas não são iguais.";
      } else {
        erroSpan.textContent = "";
      }
    }

    senha.addEventListener('input', verificarSenhas);
    confirmar.addEventListener('input', verificarSenhas);
  </script>

</body>
</html>