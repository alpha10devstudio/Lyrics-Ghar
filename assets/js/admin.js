// Lyrics Ghar - Admin JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Auto-generate slug from title
  const titleInput = document.getElementById('title');
  const slugInput = document.getElementById('slug');
  if (titleInput && slugInput && !slugInput.value) {
    titleInput.addEventListener('blur', function() {
      if (!slugInput.value) {
        slugInput.value = this.value.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s-]+/g, '-').replace(/^-+|-+$/g, '');
      }
    });
  }
});
