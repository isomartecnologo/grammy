<?php
// validar_login.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Habilitar erros (para depuração)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$input = file_get_contents('php://input');
file_put_contents('log_login.txt', "\n--- NOVO LOGIN (" . date('Y-m-d H:i:s') . ") ---\n", FILE_APPEND);
file_put_contents('log_login.txt', "Raw input: $input\n", FILE_APPEND);

$data = json_decode($input, true);

if (!$data || !isset($data['nome_login']) || !isset($data['senha'])) {
    echo json_encode([
        "sucesso" => false,
        "erro" => "Dados inválidos"
    ]);
    exit();
}

$nome_login = trim($data['nome_login']);
$senha_informada = trim($data['senha']);

file_put_contents('log_login.txt', "Tentativa: nome_login='$nome_login', senha='$senha_informada'\n", FILE_APPEND);

include 'conexao.php';
$conexao->set_charset("utf8");

// Buscar usuário pelo nome_login
$sql = "SELECT nome_completo, grupo, senha AS senha_hash FROM usuarios WHERE nome_login = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("s", $nome_login);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    file_put_contents('log_login.txt', "Erro: Usuário não encontrado.\n", FILE_APPEND);
    echo json_encode([
        "sucesso" => false,
        "erro" => "Login ou senha inválidos"
    ]);
    exit();
}

$usuario = $result->fetch_assoc();
$senha_hash = $usuario['senha_hash'];

file_put_contents('log_login.txt', "Usuário encontrado: {$usuario['nome_completo']} (grupo: {$usuario['grupo']})\n", FILE_APPEND);
file_put_contents('log_login.txt', "Hash no banco: $senha_hash\n", FILE_APPEND);

// Verificar a senha com password_verify
if (password_verify($senha_informada, $senha_hash)) {
    file_put_contents('log_login.txt', "✅ SUCESSO: Senha correta!\n", FILE_APPEND);
    echo json_encode([
        "sucesso" => true,
        "usuario" => [
            "nome" => $usuario['nome_completo'],
            "tipo" => $usuario['grupo']
        ]
    ]);
} else {
    file_put_contents('log_login.txt', "❌ Falha na verificação da senha.\n", FILE_APPEND);
    echo json_encode([
        "sucesso" => false,
        "erro" => "Login ou senha inválidos"
    ]);
}

$stmt->close();
$conexao->close();
?>