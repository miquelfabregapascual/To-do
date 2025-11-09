import './bootstrap';
import '../css/app.css';   // 👈 include Tailwind

document.addEventListener('input', (e) => {
  if (e.target.matches('#description')) {
    e.target.style.height = 'auto';
    e.target.style.height = (e.target.scrollHeight) + 'px';
  }
});
