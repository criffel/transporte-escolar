/**
 * Motorista Check - Admin JavaScript
 */

(function() {
    'use strict';

    // Initialize DataTables
    function initDataTables() {
        document.querySelectorAll('.datatable').forEach(function(table) {
            if (typeof $.fn.DataTable !== 'undefined') {
                try {
                    $(table).DataTable({
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/pt-BR.json'
                        },
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100],
                        ordering: true,
                        responsive: true,
                        autoWidth: false,
                    });
                } catch(e) {
                    console.warn('DataTable init failed:', e);
                }
            }
        });
    }

    // Toggle sidebar on mobile
    function initSidebar() {
        const toggle = document.getElementById('menu-toggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        }
    }

    // Auto-dismiss alerts
    function initAlerts() {
        document.querySelectorAll('.alert-dismissible').forEach(function(el) {
            setTimeout(function() {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            }, 5000);
        });
    }

    // Event listeners for bootstrap modals
    function initModals() {
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                // Reset form when modal closes (if it has a form)
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    // Reset the action back to create
                    const actionInput = form.querySelector('[name="action"]');
                    if (actionInput) actionInput.value = 'create';
                    const idInput = form.querySelector('[name="id"]');
                    if (idInput) idInput.value = '0';
                }
            });
        });
    }

    // Delete confirmation
    function initDeleteButtons() {
        document.querySelectorAll('form[onsubmit]').forEach(function(form) {
            const originalSubmit = form.onsubmit;
            form.onsubmit = null;
            form.addEventListener('submit', function(e) {
                if (!confirm('Tem certeza que deseja excluir este registro?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // Document ready
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initDataTables, 100);
        initSidebar();
        initAlerts();
        initModals();
        initDeleteButtons();
    });

})();
