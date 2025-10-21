<?php
include 'conexao.php';
$sql = "SELECT nome_completo, grupo FROM usuarios";
$result = $conexao->query($sql);
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
?>