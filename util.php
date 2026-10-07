<?php
header('Content-Type: application/json; charset=utf-8');

function exigir_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respnoder(false, 'Método não permitido.', [], 405);
    }
}

function respnoder($sucesso, $mensagem, $erros = [], $http = 200) {
    http_response_code($http);
    echo json_encode(
        ['sucesso' => $sucesso, 'mensagem' => $mensagem, 'erros' => $erros],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function campo($nome) {
    return trim($_POST[$nome] ?? '');
}

function campos_vazios($nomes) {
    $erros = [];
    foreach ($nomes as $n) {
        if (campo($n) === '') {
            $erros[] = "O campo $n é obrigatório.";
        }
    }
    return $erros;
}

function so_digitos($s) {
    return preg_replace('/\D/', '', $s);
}

function data_valida($s) {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
}

function carregar_dados() {
    return require __DIR__ . '/dados.php';
}

function buscar($lista, $chave, $valor) {
    foreach ($lista as $item) {
        if ($item[$chave] == $valor) {
            return $item;
        }
    }
    return null;
}
