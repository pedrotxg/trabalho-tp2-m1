<?php
require __DIR__ . '/util.php';
exigir_post();

$erros = campos_vazios(['cpf_leitor', 'isbn_livro', 'data_emprestimo', 'prazo_dias']);

if (!$erros) {
    if (!data_valida(campo('data_emprestimo'))) {
        $erros[] = 'Data do empréstimo inválida.';
    }
    $prazo = campo('prazo_dias');
    if (!ctype_digit($prazo) || (int) $prazo < 1 || (int) $prazo > 14) {
        $erros[] = 'O prazo deve ser um número de 1 a 14 dias.';
    }
}

if ($erros) {
    respnoder(false, 'Corrija os campos abaixo.', $erros, 422);
}

$dados = carregar_dados();
$leitor = buscar($dados['leitores'], 'cpf', so_digitos(campo('cpf_leitor')));
$livro = buscar($dados['livros'], 'isbn', so_digitos(campo('isbn_livro')));

if (!$leitor) {
    respnoder(false, 'Empréstimo não registrado.', ['Leitor não encontrado.'], 422);
}
if (!$livro) {
    respnoder(false, 'Empréstimo não registrado.', ['Livro não encontrado.'], 422);
}

// Lógica extra 1: Limite de 3 empréstimos ativos por leitor
$ativos = 0;
foreach ($dados['emprestimos'] as $e) {
    if ($e['id_leitor'] == $leitor['id'] && $e['status'] === 'ativo') {
        $ativos++;
    }
}
if ($ativos >= 3) {
    respnoder(false, 'Empréstimo não registrado.', ['O leitor já possui 3 empréstimos ativos.'], 422);
}

// Lógica extra 2: Livro precisa ter exemplar disponível
if ($livro['disponiveis'] < 1) {
    respnoder(false, 'Empréstimo não registrado.', ['Não há exemplares disponíveis. Faça uma reserva.'], 422);
}

// Lógica extra 3: Leitor com multa pendente não pode pegar livros
foreach ($dados['multas'] as $m) {
    $emp = buscar($dados['emprestimos'], 'id', $m['id_emprestimo']);
    if ($emp && $emp['id_leitor'] == $leitor['id'] && $m['status'] === 'pendente') {
        respnoder(false, 'Empréstimo não registrado.', ['O leitor possui multa pendente.'], 422);
    }
}

// Lógica extra 4: Data prevista de devolução
$prevista = new DateTime(campo('data_emprestimo'));
$prevista->modify('+' . (int) campo('prazo_dias') . ' days');

respnoder(true, 'Empréstimo de "' . $livro['titulo'] . '" para ' . $leitor['nome']
    . ' registrado. Devolução prevista em ' . $prevista->format('d/m/Y') . '.');
