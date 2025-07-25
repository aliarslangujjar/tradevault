// TradeVault Main JavaScript - v2.1
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const flashMsg = document.getElementById('msg-flash');
    if (flashMsg) {
        setTimeout(() => {
            flashMsg.style.transition = 'opacity 1s';
            flashMsg.style.opacity = '0';
            setTimeout(() => flashMsg.remove(), 1000);
        }, 5000);
    }

    // Form validation helper
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Trade calculator for add/edit trade forms
    const entryInput = document.querySelector('input[name="entry_price"]');
    const exitInput = document.querySelector('input[name="exit_price"]');
    const sizeInput = document.querySelector('input[name="position_size"]');
    const typeSelect = document.querySelector('select[name="position_type"]');
    
    if (entryInput && exitInput && sizeInput && typeSelect) {
        function calculateTrade() {
            const entry = parseFloat(entryInput.value) || 0;
            const exit = parseFloat(exitInput.value) || 0;
            const size = parseFloat(sizeInput.value) || 0;
            const type = typeSelect.value;

            let pnl, rr;
            if (type === 'long') {
                pnl = (exit - entry) * size;
                rr = entry !== exit ? (exit - entry) / (entry - exit) : 0;
            } else {
                pnl = (entry - exit) * size;
                rr = entry !== exit ? (entry - exit) / (exit - entry) : 0;
            }

            const pnlDisplay = document.getElementById('pnl-display');
            const rrDisplay = document.getElementById('rr-display');
            
            if (pnlDisplay) {
                pnlDisplay.textContent = pnl.toFixed(2);
                pnlDisplay.className = pnl >= 0 ? 'text-success' : 'text-danger';
            }
            if (rrDisplay) rrDisplay.textContent = rr.toFixed(2);
        }

        [entryInput, exitInput, sizeInput, typeSelect].forEach(input => {
            if (input) input.addEventListener('input', calculateTrade);
        });
        
        // Initial calculation
        calculateTrade();
    }
    
    // Set default dates for trade forms
    const setDefaultDates = () => {
        const now = new Date();
        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, -8);
        
        const entryDateInput = document.querySelector('input[name="entry_date"]');
        const exitDateInput = document.querySelector('input[name="exit_date"]');
        
        if (entryDateInput && !entryDateInput.value) {
            entryDateInput.value = localISOTime;
        }
        if (exitDateInput && !exitDateInput.value) {
            exitDateInput.value = localISOTime;
        }
    };
    
    setDefaultDates();
    
    // Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});