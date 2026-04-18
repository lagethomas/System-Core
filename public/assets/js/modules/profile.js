/**
 * Profile Module Logic
 */

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.style.backgroundImage = `url(${e.target.result})`;
            const initials = preview.querySelector('.avatar-initials');
            if (initials) initials.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('ajaxSuccess', (e) => {
    const result = e.detail;
    // Check if it is the profile form
    if (e.target.id === 'profileForm' && result.success) {
        const formData = new FormData(e.target);
        const name = formData.get('name');
        const email = formData.get('email');
        
        const sidebarName = document.querySelector('.profile-sidebar h3');
        const sidebarEmail = document.querySelector('.profile-sidebar p');
        if (sidebarName) sidebarName.textContent = name;
        if (sidebarEmail) sidebarEmail.textContent = email;

        const headerName = document.querySelector('.user-info .user-name');
        if (headerName && name) {
             // Just the name part if there is an icon
             const icon = headerName.querySelector('i');
             headerName.textContent = name;
             if (icon) headerName.appendChild(icon);
        }
        
        UI.showToast(result.message || 'Perfil atualizado!', 'success');
    }
});
