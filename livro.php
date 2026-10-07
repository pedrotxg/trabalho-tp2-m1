<?php
require __DIR__ . '/util.php';
exigir_post();

$erros = campos_vazios(['titulo', 'isbn', 'autor', 'categoria', 'editora', 'ano_publicacao', 'exemplares']);

if (!$erros) {
    $isbn = so_digitos(campo('isbn'));
    if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
        $erros[] = 'O ISBN deve ter 10 ou 13 dígitos.';
    }
    if (!ctype_digit(campo('exemplares')) || (int) campo('exemplares') <= 0) {
        $erros[] = 'A quantidade de exemplares deve ser maior que zero.';
    }
    $ano = campo('ano_publicacao');
    if (!ctype_digit($ano) || (int) $ano < 1500 || (int) $ano > (int) date('Y')) {
        $erros[] = 'O ano de publicação deve estar entre 1500 e ' . date('Y') . '.';
    }
}

if ($erros) {
    respnoder(false, 'Corrija os campos abaixo.', $erros, 422);
}

// Lógica extra: ISBN único
$dados = carregar_dados();
if (buscar($dados['livros'], 'isbn', $isbn)) {
    respnoder(false, 'Livro não cadastrado.', ['Já existe um livro com este ISBN.'], 422);
}

$total = (int) campo('exemplares');
respnoder(true, 'Livro "' . campo('titulo') . '" cadastrado com ' . $total . ' exemplar(es) disponível(is).');
