(function() {
  'use strict';

  const copyBtn = document.getElementById('copy-lyrics-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async function() {
      const lyrics = this.dataset.lyrics || '';
      const title = this.dataset.title || '';
      const textToCopy = title + '\n\n' + lyrics;
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(textToCopy);
        } else {
          const textarea = document.createElement('textarea');
          textarea.value = textToCopy;
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }
        window.showNotification('Lyrics copied successfully');
      } catch (err) {
        window.showNotification('Failed to copy lyrics');
      }
    });
  }

  const shareBtn = document.getElementById('share-btn');
  const shareModal = document.getElementById('share-modal');
  const shareNativeBtn = document.getElementById('share-native-btn');
  const shareWhatsApp = document.getElementById('share-whatsapp');
  const shareFacebook = document.getElementById('share-facebook');
  const shareCopyLink = document.getElementById('share-copy-link');
  let shareUrl = '';
  let shareTitle = '';

  if (shareBtn && shareModal) {
    shareBtn.addEventListener('click', function() {
      shareUrl = this.dataset.url || window.location.href;
      shareTitle = this.dataset.title || document.title;
      if (shareWhatsApp) shareWhatsApp.href = 'https://wa.me/?text=' + encodeURIComponent(shareTitle + ' ' + shareUrl);
      if (shareFacebook) shareFacebook.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl);
      shareModal.hidden = false;
    });
  }

  if (shareNativeBtn) {
    shareNativeBtn.addEventListener('click', async function() {
      if (navigator.share) {
        try {
          await navigator.share({ title: shareTitle, url: shareUrl });
          if (shareModal) shareModal.hidden = true;
        } catch (err) {}
      } else {
        window.showNotification('Native sharing not supported on this device');
      }
    });
  }

  if (shareCopyLink) {
    shareCopyLink.addEventListener('click', async function() {
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(shareUrl);
        } else {
          const textarea = document.createElement('textarea');
          textarea.value = shareUrl;
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }
        window.showNotification('Link copied to clipboard');
        if (shareModal) shareModal.hidden = true;
      } catch (err) {
        window.showNotification('Failed to copy link');
      }
    });
  }
})();
