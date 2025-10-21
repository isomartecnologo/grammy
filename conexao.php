<?php
$host = '200.129.131.55';     // IP do servidor
$porta = '3306';              // Porta MySQL
$usuario = 'cameta';          // Seu usuário
$senha = 'sti.pa25@';         // Sua senha
$banco = 'grammy';            // Nome do banco

// Monta a string de conexão com a porta
$conexao = new mysqli($host, $usuario, $senha, $banco, $porta);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Define o conjunto de caracteres para evitar problemas com acentos
$conexao->set_charset("utf8");
?>
