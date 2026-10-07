<?php
require __DIR__ . '/util.php';
exigir_post();

$erros = campos_vazios(['codigo_emprestimo', 'data_devolucao', 'estado_livro', 'forma_pagamento_multa']);

if (!$erros) {
    if (!data_valida(campo('data_devolucao'))) {
        $erros[] = 'Data da devolução inválida.';
    }
    if (!in_array(campo('estado_livro'), ['bom', 'danificado', 'perdido'])) {
        $erros[] = 'Estado do livro inválido.';
    }
    if (!in_array(campo('forma_pagamento_multa'), ['dinheiro', 'pix', 'cartao', 'a_pagar_depois'])) {
        $erros[] = 'Forma de pagamento inválida.';
    }
}

if ($erros) {
    respnoder(false, 'Corrija os campos abaixo.', $erros, 422);
}

$dados = carregar_dados();
$emp = buscar($dados['emprestimos'], 'id', campo('codigo_emprestimo'));

if (!$emp) {
    respnoder(false, 'Devolução não registrada.', ['Empréstimo não encontrado.'], 422);
}
if ($emp['status'] !== 'ativo') {
    respnoder(false, 'Devolução não registrada.', ['Este empréstimo já foi devolvido.'], 422);
}

$devolucao = new DateTime(campo('data_devolucao'));
$inicio = new DateTime($emp['data']);
$prevista = new DateTime($emp['prevista']);

if ($devolucao < $inicio) {
    respnoder(false, 'Devolução não registrada.', ['A data de devolução é anterior à data do empréstimo.'], 422);
}

// Lógica extra: dias de atraso e multa
$dias_atraso = 0;
if ($devolucao > $prevista) {
    $dias_atraso = $prevista->diff($devolucao)->days;
}

$estado = campo('estado_livro');
$valor = $dias_atraso * 0.50;
if ($estado === 'danificado') {
    $valor += 10.00;
} elseif ($estado === 'perdido') {
    $valor = 50.00;
}

$livro = buscar($dados['livros'], 'id', $emp['id_livro']);
$mensagem = 'Devolução de "' . $livro['titulo'] . '" registrada. Dias de atraso: ' . $dias_atraso . '.';

if ($valor > 0) {
    $mensagem .= ' Multa gerada: R$ ' . number_format($valor, 2, ',', '.')
        . ' (pagamento: ' . str_replace('_', ' ', campo('forma_pagamento_multa')) . ').';
} else {
    $mensagem .= ' Nenhuma multa.';
}

if ($estado !== 'perdido') {
    $mensagem .= ' O exemplar voltou ao estoque.';
}

respnoder(true, $mensagem);
