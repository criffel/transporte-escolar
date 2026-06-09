/**
 * Motorista Check - Frontend JavaScript
 */

(function() {
    'use strict';

    // Máscara de CPF
    function mascaraCpf(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 9) {
            value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6, 9) + '-' + value.slice(9);
        } else if (value.length > 6) {
            value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6);
        } else if (value.length > 3) {
            value = value.slice(0, 3) + '.' + value.slice(3);
        }
        input.value = value;
    }

    // Máscara de Telefone
    function mascaraTelefone(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 7) {
            if (value.length === 11) {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2, 3) + ' ' + value.slice(3, 7) + '-' + value.slice(7);
            } else {
                value = '(' + value.slice(0, 2) + ') ' + value.slice(2, 6) + '-' + value.slice(6);
            }
        } else if (value.length > 2) {
            value = '(' + value.slice(0, 2) + ') ' + value.slice(2);
        } else if (value.length > 0) {
            value = '(' + value;
        }
        input.value = value;
    }

    // Auto uppercase for license plates
    function placaUppercase(input) {
        let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (value.length > 7) value = value.slice(0, 7);
        if (value.length > 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }
        input.value = value;
    }

    // Inicializar máscaras
    function initMasks() {
        document.querySelectorAll('.cpf-mask').forEach(el => {
            el.addEventListener('input', function() { mascaraCpf(this); });
        });

        document.querySelectorAll('.tel-mask').forEach(el => {
            el.addEventListener('input', function() { mascaraTelefone(this); });
        });

        document.querySelectorAll('[name="placa"]').forEach(el => {
            el.addEventListener('input', function() { placaUppercase(this); });
        });
    }

    // Auto-dismiss alerts
    function initAlerts() {
        document.querySelectorAll('.alert-dismissible').forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => { el.remove(); }, 500);
            }, 5000);
        });
    }

    // Confirm forms
    function initConfirms() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm || 'Tem certeza?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Document ready
    document.addEventListener('DOMContentLoaded', function() {
        initMasks();
        initAlerts();
        initConfirms();
    });

})();
