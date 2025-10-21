<?php
include 'conexao.php';

$nome_completo = $_POST['nome_completo'];
$nome_login = $_POST['nome_login'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Senha criptografada
$grupo = $_POST['grupo'];

$sql = "INSERT INTO usuarios (nome_completo, nome_login, senha, grupo) VALUES (?, ?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("ssss", $nome_completo, $nome_login, $senha, $grupo);

if ($stmt->execute()) {
    echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='index.html';</script>";
} else {
    if ($conexao->errno == 1062) {
        echo "<script>alert('Nome de login já existe.'); history.back();</script>";
    } else {
        echo "<script>alert('Erro: " . $stmt->error . "'); history.back();</script>";
    }
}

$stmt->close();
$conexao->close();
?>