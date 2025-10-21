<?php
include 'conexao.php';

$titulo = $_POST['titulo'];
$criador = $_POST['criador'];
$url = $_POST['url'];

$sql = "INSERT INTO apresentacoes (titulo, criador, url) VALUES (?, ?, ?)";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("sss", $titulo, $criador, $url);

if ($stmt->execute()) {
    echo "<script>alert('✅ Apresentação cadastrada com sucesso!'); window.location.href='gerente.html';</script>";
} else {
    echo "❌ Erro: " . $stmt->error;
}

$stmt->close();
$conexao->close();
?>