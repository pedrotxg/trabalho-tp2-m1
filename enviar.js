document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const resultado = document.getElementById('resultado');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    resultado.textContent = 'Enviando...';
    try {
      const resp = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
      });
      const dados = await resp.json();
      mostrar(dados);
    } catch (erro) {
      resultado.textContent = 'Erro de comunicação com o servidor.';
    }
  });

  function mostrar(d) {
    resultado.textContent = '';
    const p = document.createElement('p');
    p.textContent = d.mensagem;
    resultado.appendChild(p);

    if (d.erros && d.erros.length > 0) {
      const ul = document.createElement('ul');
      d.erros.forEach((msg) => {
        const li = document.createElement('li');
        li.textContent = msg;
        ul.appendChild(li);
      });
      resultado.appendChild(ul);
    }
  }
});
