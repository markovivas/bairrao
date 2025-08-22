function mostrarJogos(tipo) {
  document.getElementById('jogos-passados').style.display = 'none';
  document.getElementById('jogos-atuais').style.display = 'none';
  document.getElementById('jogos-futuros').style.display = 'none';
  document.getElementById('jogos-' + tipo).style.display = 'block';
  const tabs = document.querySelectorAll('.jogos-tab');
  tabs.forEach(tab => tab.classList.remove('active'));
  event.currentTarget.classList.add('active');
}