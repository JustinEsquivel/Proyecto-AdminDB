function animateCount(element, target) {
    let current = 0;
    const increment = target / 50;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            clearInterval(timer);
            current = target;
        }
        element.textContent = Math.floor(current);
    }, 20);
}

document.addEventListener('DOMContentLoaded', function () {
  const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
  setText('mascotasCount', '0');
  setText('adopcionesCount', '0');
  setText('campaniasCount', '0');
  setText('voluntariosCount', '0');
});
