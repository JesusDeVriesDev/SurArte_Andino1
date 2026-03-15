const pills  = document.querySelectorAll('.filter-pill');
const cards  = document.querySelectorAll('.art-card');

pills.forEach(pill => {
  pill.addEventListener('click', () => {
    pills.forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    const disc = pill.dataset.disc;
    cards.forEach(card => {
      const show = disc === 'all' || card.dataset.disc === disc;
      card.style.display = show ? '' : 'none';
    });
  });
});
