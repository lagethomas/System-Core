/**
 * Company Settings Module Logic
 */

window.previewImage = function(input, previewId, imgClass) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const placeholderIcon = preview.querySelector('.upload-placeholder-icon');
            const placeholderText = preview.querySelector('.upload-placeholder-text');
            if (placeholderIcon) placeholderIcon.remove();
            if (placeholderText) placeholderText.remove();

            let img = preview.querySelector('img');
            if (!img) {
                img = document.createElement('img');
                img.className = imgClass;
                const overlay = preview.querySelector('.upload-hover-overlay');
                if (overlay) preview.insertBefore(img, overlay);
                else preview.appendChild(img);
            }
            img.src = e.target.result;
            preview.style.borderColor = 'var(--primary)';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

window.toggleSelectableCard = function(card, inputId) {
    const container = card.closest('.theme-grid');
    if (!container) return;
    
    container.querySelectorAll('.selectable-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    
    const input = document.getElementById(inputId);
    if (input) input.checked = true;
}

document.addEventListener('ajaxSuccess', (e) => {
    if (e.target.id === 'company-settings-form' && e.detail && e.detail.success) {
        setTimeout(() => window.location.reload(), 1500);
    }
});
