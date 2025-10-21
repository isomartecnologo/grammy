<?php
// api.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Inclui a conexão com o banco
if (!file_exists('conexao.php')) {
    die(json_encode(["erro" => "Arquivo conexao.php não encontrado"]));
}

include 'conexao.php';

// Verifica se a conexão foi bem-sucedida
if (!$conexao) {
    die(json_encode(["erro" => "Conexão com o banco falhou"]));
}


$method = $_SERVER['REQUEST_METHOD'];
$acao = $_GET['acao'] ?? '';

// ===========================
// 1. LISTAR APRESENTAÇÕES
// ===========================
if ($acao === 'listar_apresentacoes' && $method === 'GET') {
    $sql = "SELECT * FROM apresentacoes ORDER BY data_cadastro DESC";
    $result = $conexao->query($sql);
    $dados = [];
    while ($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
    echo json_encode($dados);
}

// ===========================
// 2. SALVAR NOVA APRESENTAÇÃO
// ===========================
else if ($acao === 'salvar_apresentacao' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $titulo = $conexao->real_escape_string($data['titulo']);
    $criador = $conexao->real_escape_string($data['criador']);
    $url = $conexao->real_escape_string($data['url']);

    $sql = "INSERT INTO apresentacoes (titulo, criador, url) VALUES ('$titulo', '$criador', '$url')";
    
    if ($conexao->query($sql) === TRUE) {
        echo json_encode(["sucesso" => "Apresentação cadastrada!", "id" => $conexao->insert_id]);
    } else {
        echo json_encode(["erro" => $conexao->error]);
    }
}

// ===========================
// 3. EXCLUIR APRESENTAÇÃO
// ===========================
else if ($acao === 'excluir_apresentacao' && $method === 'POST') {
    $id = (int)$conexao->real_escape_string($_POST['id']);
    $sql1 = "DELETE FROM avaliacoes WHERE apresentacao_id = $id";
    $sql2 = "DELETE FROM apresentacoes WHERE id = $id";

    $conexao->autocommit(FALSE);
    try {
        if (!$conexao->query($sql1)) throw new Exception($conexao->error);
        if (!$conexao->query($sql2)) throw new Exception($conexao->error);
        $conexao->commit();
        echo json_encode(["sucesso" => "Apresentação excluída"]);
    } catch (Exception $e) {
        $conexao->rollback();
        echo json_encode(["erro" => $e->getMessage()]);
    }
    $conexao->autocommit(TRUE);
}

// ===========================
// 4. LISTAR AVALIAÇÕES
// ===========================
else if ($acao === 'listar_avaliacoes' && $method === 'GET') {
    $sql = "SELECT 
                av.id,
                ap.titulo AS apresentacao_titulo,
                av.jurado_nome,
                av.media,
                av.data_avaliacao,
                av.roteiro, av.producao, av.lipsync, av.figurino, av.sinergia,
                av.criatividade, av.performance, av.revelacao, av.arte_audiovisual
            FROM avaliacoes av
            LEFT JOIN apresentacoes ap ON av.apresentacao_id = ap.id
            ORDER BY av.media DESC";

    $result = $conexao->query($sql);
    $dados = [];
    while ($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
    echo json_encode($dados);
}

// ===========================
// 6. SALVAR AVALIAÇÃO
// ===========================
else if ($acao === 'salvar_avaliacao' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)$data['id'];
    $jurado_nome = $conexao->real_escape_string($data['jurado']);
    $notas = $data['notas'];
    $media = number_format(array_sum($notas) / count($notas), 2);

    // Verifica se já avaliou
    $check = "SELECT id FROM avaliacoes WHERE apresentacao_id = $id AND jurado_nome = '$jurado_nome'";
    $result = $conexao->query($check);
    if ($result->num_rows > 0) {
        echo json_encode(["erro" => "Você já avaliou esta apresentação."]);
        exit();
    }

    // Insere a avaliação
    $sql = "INSERT INTO avaliacoes 
            (apresentacao_id, jurado_nome, roteiro, producao, lipsync, figurino, sinergia, 
             criatividade, performance, revelacao, arte_audiovisual, media)
            VALUES 
            ('$id', '$jurado_nome',
             '{$notas['Roteiro']}', '{$notas['Produção']}', '{$notas['Equipe de lipsinch/dublagem/karaokê']}',
             '{$notas['Figurino']}', '{$notas['Sinergia música/filmagem']}', '{$notas['Criatividade']}',
             '{$notas['Coreografia/performance']}', '{$notas['Artistas-revelação (elenco/atuação)']}',
             '{$notas['Caracterização de arte audiovisual']}', '$media')";

    if ($conexao->query($sql) !== TRUE) {
        echo json_encode(["erro" => "Falha ao salvar avaliação: " . $conexao->error]);
        exit();
    }

    // Recalcula a pontuação total
    $sql_soma = "SELECT SUM(media) as total FROM avaliacoes WHERE apresentacao_id = $id";
    $res_soma = $conexao->query($sql_soma);
    $row = $res_soma->fetch_assoc();
    $nova_pontuacao = (float)$row['total'];

    // Atualiza a apresentação
    $sql_update = "UPDATE apresentacoes SET pontuacao_total = $nova_pontuacao WHERE id = $id";
    $conexao->query($sql_update);

    echo json_encode([
        "sucesso" => "Avaliação salva com sucesso!",
        "media" => $media,
        "pontuacao_total" => $nova_pontuacao
    ]);
}

/*

// ===========================
// 5. SALVAR AVALIAÇÃO
// ===========================
else if ($acao === 'salvar_avaliacao' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)$data['id'];
    $jurado_nome = $conexao->real_escape_string($data['jurado']); // ← nome_completo do usuário
    $notas = $data['notas'];
    $media = number_format(array_sum($notas) / count($notas), 2);

    // Verifica se já avaliou
    $check = "SELECT id FROM avaliacoes WHERE apresentacao_id = $id AND jurado_nome = '$jurado_nome'";
    $result = $conexao->query($check);
    if ($result->num_rows > 0) {
        echo json_encode(["erro" => "Você já avaliou esta apresentação."]);
        exit();
    }

    $sql = "INSERT INTO avaliacoes 
            (apresentacao_id, jurado_nome, roteiro, producao, lipsync, figurino, sinergia, 
             criatividade, performance, revelacao, arte_audiovisual, media)
            VALUES 
            ('$id', '$jurado_nome',
             '{$notas['Roteiro']}', '{$notas['Produção']}', '{$notas['Equipe de lipsinch/dublagem/karaokê']}',
             '{$notas['Figurino']}', '{$notas['Sinergia música/filmagem']}', '{$notas['Criatividade']}',
             '{$notas['Coreografia/performance']}', '{$notas['Artistas-revelação (elenco/atuação)']}',
             '{$notas['Caracterização de arte audiovisual']}', '$media')";

    if ($conexao->query($sql) === TRUE) {
        echo json_encode(["sucesso" => "Avaliação salva!", "media" => $media]);
    } else {
        echo json_encode(["erro" => $conexao->error]);
    }
}

*/

/*
// ===========================
// 6. EXCLUIR AVALIAÇÃO 02
// ===========================
else if ($acao === 'excluir_avaliacao' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $apresentacao_id = (int)$data['apresentacao_id'];
    $jurado_nome = $conexao->real_escape_string($data['jurado_nome']);

    $sql = "DELETE FROM avaliacoes WHERE apresentacao_id = $apresentacao_id AND jurado_nome = '$jurado_nome'";

    if ($conexao->query($sql) === TRUE) {
        if ($conexao->affected_rows > 0) {
            echo json_encode(["sucesso" => "Avaliação excluída com sucesso."]);
        } else {
            echo json_encode(["erro" => "Nenhuma avaliação encontrada para excluir."]);
        }
    } else {
        echo json_encode(["erro" => "Erro ao excluir: " . $conexao->error]);
    }
}

*/


// ===========================
// 7. EXCLUIR AVALIAÇÃO DO JURADO
// ===========================
else if ($acao === 'excluir_avaliacao' && $method === 'POST') {
    if (!isset($_POST['apresentacao_id']) || !isset($_POST['jurado_nome'])) {
        echo json_encode([
            "sucesso" => false,
            "erro" => "Dados incompletos para exclusão"
        ]);
        exit();
    }

    $apresentacao_id = (int)$conexao->real_escape_string($_POST['apresentacao_id']);
    $jurado_nome = $conexao->real_escape_string($_POST['jurado_nome']);

    $sql = "DELETE FROM avaliacoes WHERE apresentacao_id = $apresentacao_id AND jurado_nome = '$jurado_nome'";

    if ($conexao->query($sql) === TRUE) {
        if ($conexao->affected_rows > 0) {
            // Recalcula a pontuação total da apresentação
            $sql_soma = "SELECT SUM(media) as total FROM avaliacoes WHERE apresentacao_id = $apresentacao_id";
            $res_soma = $conexao->query($sql_soma);
            $row = $res_soma->fetch_assoc();
            $nova_pontuacao = (float)($row['total'] ?? 0);

            $sql_update = "UPDATE apresentacoes SET pontuacao_total = $nova_pontuacao WHERE id = $apresentacao_id";
            $conexao->query($sql_update);

            echo json_encode([
                "sucesso" => true,
                "mensagem" => "Avaliação excluída e pontuação atualizada."
            ]);
        } else {
            echo json_encode([
                "sucesso" => false,
                "erro" => "Nenhuma avaliação encontrada para excluir."
            ]);
        }
    } else {
        echo json_encode([
            "sucesso" => false,
            "erro" => "Erro no banco: " . $conexao->error
        ]);
    }
}

$conexao->close();
?>