<?php
require __DIR__ . '/util.php';
exigir_post();

$erros = campos_vazios(['nome', 'cpf', 'email', 'telefone', 'nascimento', 'tipo']);

if (!$erros) {
    $cpf = so_digitos(campo('cpf'));
    if (strlen($cpf) !== 11) {
        $erros[] = 'O CPF deve ter 11 dígitos.';
    }
    if (!filter_var(campo('email'), FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido.';
    }
    if (!data_valida(campo('nascimento'))) {
        $erros[] = 'Data de nascimento inválida.';
    }
    if (!in_array(campo('tipo'), ['aluno', 'professor', 'funcionario'])) {
        $erros[] = 'Tipo de leitor inválido.';
    }
}

if ($erros) {
    respnoder(false, 'Corrija os campos abaixo.', $erros, 422);
}

// Lógica extra: CPF único e idade mínima
$dados = carregar_dados();
if (buscar($dados['leitores'], 'cpf', $cpf)) {
    respnoder(false, 'Leitor não cadastrado.', ['Já existe um leitor com este CPF.'], 422);
}

$idade = (new DateTime(campo('nascimento')))->diff(new DateTime('today'))->y;
if ($idade < 6) {
    respnoder(false, 'Leitor não cadastrado.', ['A idade mínima para cadastro é 6 anos.'], 422);
}

respnoder(true, 'Leitor ' . campo('nome') . ' cadastrado com sucesso.');
