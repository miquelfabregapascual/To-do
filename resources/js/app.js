import './bootstrap';
import '../css/app.css';   // 👈 include Tailwind

document.addEventListener('input', (e) => {
  if (e.target.matches('#description')) {
    e.target.style.height = 'auto';
    e.target.style.height = (e.target.scrollHeight) + 'px';
  }
  document.addEventListener('click', (event) => {
  const toggle = event.target.closest('[data-read-more-toggle]');
  if (!toggle) {
    return;
  }

  const targetId = toggle.getAttribute('data-target');
  if (!targetId) {
    return;
  }

  const wrapper = document.getElementById(targetId);
  const text = wrapper?.querySelector('[data-description-text]');
  if (!wrapper || !text) {
    return;
  }

  const expanded = wrapper.getAttribute('data-expanded') === 'true';
  const nextExpanded = !expanded;

  wrapper.setAttribute('data-expanded', String(nextExpanded));
  toggle.setAttribute('aria-expanded', String(nextExpanded));

  const labelTarget = toggle.querySelector('[data-toggle-label]') || toggle;
  const moreLabel = toggle.getAttribute('data-label-more') || 'Leer más';
  const lessLabel = toggle.getAttribute('data-label-less') || 'Leer menos';

  labelTarget.textContent = nextExpanded ? lessLabel : moreLabel;
});
});
