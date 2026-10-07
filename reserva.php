<?php
require __DIR__ . '/util.php';
exigir_post();

$erros = campos_vazios(['cpf_leitor', 'isbn_livro', 'data_reserva', 'prioridade']);

if (!$erros) {
    if (!data_valida(campo('data_reserva'))) {
        $erros[] = 'Data da reserva inválida.';
    }
    if (!in_array(campo('prioridade'), ['normal', 'alta'])) {
        $erros[] = 'Prioridade inválida.';
    }
}

if ($erros) {
    respnoder(false, 'Corrija os campos abaixo.', $erros, 422);
}

$dados = carregar_dados();
$leitor = buscar($dados['leitores'], 'cpf', so_digitos(campo('cpf_leitor')));
$livro = buscar($dados['livros'], 'isbn', so_digitos(campo('isbn_livro')));

if (!$leitor) {
    respnoder(false, 'Reserva não registrada.', ['Leitor não encontrado.'], 422);
}
if (!$livro) {
    respnoder(false, 'Reserva não registrada.', ['Livro não encontrado.'], 422);
}

// Lógica extra 1: Só reserva livro sem exemplares disponíveis
if ($livro['disponiveis'] > 0) {
    respnoder(false, 'Reserva não registrada.', ['O livro está disponível. Faça um empréstimo.'], 422);
}

// Lógica extra 2: Sem reserva duplicada
// Lógica extra 3: Posição na fila
$fila = 0;
foreach ($dados['reservas'] as $r) {
    if ($r['id_livro'] == $livro['id'] && $r['status'] === 'ativa') {
        if ($r['id_leitor'] == $leitor['id']) {
            respnoder(false, 'Reserva não registrada.', ['O leitor já tem uma reserva ativa para este livro.'], 422);
        }
        $fila++;
    }
}

respnoder(true, 'Reserva de "' . $livro['titulo'] . '" registrada. Posição na fila: ' . ($fila + 1) . '.');
