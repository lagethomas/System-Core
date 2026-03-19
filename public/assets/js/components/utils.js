/**
 * Utility Helpers Component
 */
if (typeof UI !== 'undefined') {
    UI.generatePassword = function (targetId) {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const el = document.getElementById(targetId);
        if (el) {
            el.value = password;
            el.type = 'text';
            this.showToast('Nova senha gerada!', 'info');
            setTimeout(() => el.type = 'password', 3000);
        }
    };

    UI.lookupZip = async function (zip, cityElId, stateElId, streetElId = null, neighborhoodElId = null) {
        zip = zip.replace(/\D/g, '');
        if (zip.length !== 8) return;
        try {
            const response = await fetch(`https://viacep.com.br/ws/${zip}/json/`);
            const data = await response.json();
            if (!data.erro) {
                const cityEl = document.getElementById(cityElId);
                const stateEl = document.getElementById(stateElId);
                const streetEl = streetElId ? document.getElementById(streetElId) : null;
                const neighborEl = neighborhoodElId ? document.getElementById(neighborhoodElId) : null;
                if (cityEl) cityEl.value = data.localidade;
                if (stateEl) stateEl.value = data.uf;
                if (streetEl) streetEl.value = data.logradouro;
                if (neighborEl) neighborEl.value = data.bairro;
            }
        } catch (e) { console.error('CEP check failed'); }
    };

    UI.uploadProfilePicture = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const avatar = document.getElementById('avatar-preview') || document.querySelector('.profile-avatar');
                if (avatar) {
                    avatar.style.backgroundImage = `url(${e.target.result})`;
                    avatar.style.backgroundSize = 'cover';
                    avatar.style.backgroundPosition = 'center';
                    avatar.style.color = 'transparent';
                }
            }
            reader.readAsDataURL(input.files[0]);
            this.showToast('Foto selecionada! Clique em Salvar Alterações para aplicá-la.', 'info');
        }
    };
}
