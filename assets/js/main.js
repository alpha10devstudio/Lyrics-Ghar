(function() {
  'use strict';

  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const mainNav = document.querySelector('.main-nav');
  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', !isExpanded);
      mainNav.style.display = isExpanded ? 'none' : 'block';
      mainNav.classList.toggle('nav-open', !isExpanded);
    });
  }

  const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');
  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      if (window.innerWidth < 768) {
        e.preventDefault();
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', !isExpanded);
      }
    });
  });

  window.showNotification = function(message) {
    const notification = document.getElementById('notification');
    const text = document.getElementById('notification-text');
    if (!notification || !text) return;
    text.textContent = message;
    notification.classList.add('show');
    setTimeout(() => { notification.classList.remove('show'); }, 3000);
  };

  const modals = document.querySelectorAll('.modal');
  const modalClosers = document.querySelectorAll('[data-close-modal]');
  modalClosers.forEach(closer => {
    closer.addEventListener('click', function() {
      const modal = this.closest('.modal');
      if (modal) modal.hidden = true;
    });
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      modals.forEach(m => { if (!m.hidden) m.hidden = true; });
    }
  });

  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) { img.src = img.dataset.src; img.removeAttribute('data-src'); }
          observer.unobserve(img);
        }
      });
    });
    document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
  }

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
  }
})();
