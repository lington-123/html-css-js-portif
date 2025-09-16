// Mobile nav toggle
const toggle = document.querySelector('.nav__toggle');
const menu = document.querySelector('#nav-menu');
if (toggle && menu) {
  toggle.addEventListener('click', () => {
    const isOpen = menu.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });
}

// Smooth scroll for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href');
    const targetEl = document.querySelector(targetId);
    if (targetEl) {
      e.preventDefault();
      targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      menu && menu.classList.remove('open');
      toggle && toggle.setAttribute('aria-expanded', 'false');
    }
  });
});

// CSRF token generation (simple client-side token echoed by PHP session)
function generateToken() {
  const array = new Uint8Array(16);
  crypto.getRandomValues(array);
  return Array.from(array).map(b => b.toString(16).padStart(2, '0')).join('');
}

const csrfInput = document.getElementById('csrf');
if (csrfInput) {
  const token = generateToken();
  sessionStorage.setItem('csrf', token);
  csrfInput.value = token;
}

// Contact form progressive enhancement (AJAX submit)
const form = document.getElementById('contactForm');
const statusEl = document.getElementById('formStatus');
if (form && statusEl) {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    statusEl.textContent = 'Sending...';
    const formData = new FormData(form);
    try {
      const res = await fetch(form.action, { method: 'POST', body: formData });
      let data;
      try { data = await res.json(); } catch (_) { data = { success: false, message: 'Invalid server response' }; }
      if (res.ok && data.success) {
        statusEl.textContent = 'Message sent successfully!';
        form.reset();
      } else {
        statusEl.textContent = data.message || ('Request failed: ' + res.status);
      }
    } catch (err) {
      statusEl.textContent = 'Network error. Check your server is running.';
    }
  });
}

// Theme toggle with persistence
const themeToggle = document.getElementById('themeToggle');
const root = document.documentElement;
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light') {
  root.classList.add('light');
  themeToggle && (themeToggle.textContent = '🌙');
}
themeToggle && themeToggle.addEventListener('click', () => {
  const isLight = root.classList.toggle('light');
  localStorage.setItem('theme', isLight ? 'light' : 'dark');
  themeToggle.textContent = isLight ? '🌙' : '☀️';
});


