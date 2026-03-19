/**
 * Input Masks Component
 */
if (typeof UI !== 'undefined') {
    UI.maskPhone = function (el) {
        el.addEventListener('input', (e) => {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    };

    UI.maskCpfCnpj = function (el) {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.slice(0, 14);

            if (value.length <= 11) {
                let x = value.match(/(\d{0,3})(\d{0,3})(\d{0,3})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '-' + x[4] : '');
            } else {
                let x = value.match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
                e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + (x[3] ? '.' + x[3] : '') + (x[4] ? '/' + x[4] : '') + (x[5] ? '-' + x[5] : '');
            }
        });
    };

    UI.maskZip = function (el) {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) value = value.slice(0, 8);
            let x = value.match(/(\d{0,5})(\d{0,3})/);
            e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
        });
    };

    UI.initMasks = function () {
        document.querySelectorAll('.mask-phone').forEach(el => this.maskPhone(el));
        document.querySelectorAll('.mask-document, .mask-doc').forEach(el => this.maskCpfCnpj(el));
        document.querySelectorAll('.mask-zip').forEach(el => this.maskZip(el));
    };
}
