/**
 * Selectable Cards Component - v1.0.0
 * Standardized according to .agenterules.md
 */

/**
 * Toggles a selectable card and executes optional callback (Rule 28)
 * @param {HTMLElement} el The card element or a child element
 * @param {string} id The id of the hidden input
 * @param {function} callback Optional callback for AJAX persistence
 */
window.toggleSelectableCard = function(el, id, callback = null) {
    const card = el.classList.contains('selectable-card') ? el : el.closest('.selectable-card');
    const input = document.getElementById(id);
    
    if (!card || !input) return;

    const isRadio = input.type === 'radio';
    
    if (isRadio) {
        // If it's a radio, unselect others in the same group (same name)
        const name = input.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(inp => {
            const otherCard = inp.closest('.selectable-card');
            if (otherCard) otherCard.classList.remove('active');
        });
        input.checked = true;
        card.classList.add('active');
    } else {
        // Toggle checkbox
        input.checked = !input.checked;
        card.classList.toggle('active', input.checked);
    }

    // Immediate AJAX persistence if callback is provided (Rule 29)
    if (typeof callback === 'function') {
        callback(input.value, input.checked);
    }
};

// Auto-init for existing checked inputs
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.selectable-card input').forEach(input => {
        if (input.checked) {
            const card = input.closest('.selectable-card');
            if (card) card.classList.add('active');
        }
    });
});
