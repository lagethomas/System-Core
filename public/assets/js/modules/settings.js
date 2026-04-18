/**
 * Settings Module Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    if(window.lucide) lucide.createIcons();
});

// Settings success handler - Handles dynamic theme switching without reload
document.addEventListener('ajaxSuccess', (e) => {
    const result = e.detail;
    
    // Only proceed if it was a theme update
    const form = document.querySelector('.ajax-form[action*="settings/save"]');
    if (!form || !result.success) return;

    const formData = new FormData(form);
    const tab = formData.get('tab');
    
    if (tab === 'themes') {
        const newTheme = formData.get('system_theme');
        if (newTheme) {
            const themeLink = document.querySelector('link[href*="/assets/css/theme/"]');
            if (themeLink) {
                const baseUrl = themeLink.href.split('/assets/css/theme/')[0];
                const version = new Date().getTime(); 
                themeLink.href = `${baseUrl}/assets/css/theme/${newTheme}.css?v=${version}`;
            }
        }
    }
    
    UI.showToast(result.message || 'Configurações atualizadas!', 'success');
});

function previewImage(input, previewId, flagId) {
    if (input.files && input.files[0]) {
        if (flagId) document.getElementById(flagId).value = "0";

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const isBg = previewId === 'preview-bg';
            const btnTitle = isBg ? 'Remover Fundo' : 'Remover Logo';
            const overlayText = isBg ? 'Alterar Fundo' : 'Alterar Logo';

            preview.innerHTML = `
                <img src="${e.target.result}" class="preview-img ${isBg ? 'object-cover' : ''}"> 
                <button type="button" class="btn-remove-image" onclick="removeImage(event, '${previewId}', '${flagId}', '${input.id}')" title="${btnTitle}">
                    <i data-lucide="x"></i>
                </button>
                <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>${overlayText}</span></div>
            `;
            if(window.lucide) lucide.createIcons();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(event, previewId, flagId, inputId) {
    event.stopPropagation();
    
    if (flagId) document.getElementById(flagId).value = "1";
    if (inputId) document.getElementById(inputId).value = "";
    
    const preview = document.getElementById(previewId);
    const isBg = previewId === 'preview-bg';
    const icon = isBg ? 'monitor' : 'image';
    const text = isBg ? 'Selecionar Wallpaper' : 'Selecionar Imagem';
    const overlayText = isBg ? 'Alterar Fundo' : 'Alterar Logo';

    preview.innerHTML = `
        <div class="upload-empty">
            <i data-lucide="${icon}"></i>
            <span>${text}</span>
        </div>
        <div class="upload-overlay"><i data-lucide="upload-cloud"></i> <span>${overlayText}</span></div>
    `;
    if(window.lucide) lucide.createIcons();
}
