(() => {
    const form = document.getElementById('tax-form');
    if (!form) return;

    function updateIncomeGroup(group, clear = false) {
        const type = document.getElementById(`income_g${group}_type`);
        const amount = document.getElementById(`income_g${group}_amount`);
        const input = document.getElementById(`income_g${group}`);
        const active = type.value !== 'none';
        amount.hidden = !active;
        input.disabled = !active;
        if (!active && clear) input.value = '0';

        const method = document.getElementById(`income_g${group}_method`);
        if (method) {
            const actual = active && method.value === 'actual';
            document.getElementById(`expense_g${group}_fields`).hidden = !actual;
            document.getElementById(`expense_g${group}`).disabled = !actual;
        }
        if (group === 8) {
            const checkbox = document.getElementById('business_confirmed');
            const flat = active && method.value === 'flat';
            checkbox.closest('label').hidden = !flat;
            checkbox.disabled = !flat;
            // Explain unsupported flat deductions instead of silently changing the selection.
            method.querySelector('option[value="flat"]').disabled = type.value === 'other';
            if (!active && clear) checkbox.checked = false;
        }
    }

    function updateSocialCap() {
        const cap = document.getElementById('tax_year').value === '2569' ? 10500 : 9000;
        document.getElementById('deduct_social').max = cap;
        document.getElementById('social-cap').textContent = cap.toLocaleString('en-US');
    }

    function updateSelfExemption() {
        const choice = document.getElementById('self_exemption').value;
        document.getElementById('self-exemption-details').hidden = choice === 'none';
        const disability = document.getElementById('disability_confirmed');
        disability.closest('label').hidden = choice !== 'disabled';
        disability.disabled = choice !== 'disabled';
    }

    for (const group of [5, 6, 8]) {
        document.getElementById(`income_g${group}_type`).addEventListener('change', () => {
            if (group === 8 && document.getElementById('income_g8_type').value === 'other') {
                document.getElementById('income_g8_method').value = 'actual';
            }
            if (group === 8) document.getElementById('business_confirmed').checked = false;
            updateIncomeGroup(group, true);
        });
        const method = document.getElementById(`income_g${group}_method`);
        if (method) method.addEventListener('change', () => updateIncomeGroup(group));
    }
    document.getElementById('tax_year').addEventListener('change', updateSocialCap);
    document.getElementById('self_exemption').addEventListener('change', updateSelfExemption);
    function updateForm() {
        [5, 6, 8].forEach(group => updateIncomeGroup(group));
        updateSocialCap();
        updateSelfExemption();
    }
    window.addEventListener('pageshow', updateForm);
    updateForm();
    document.getElementById('validation-errors')?.focus();
})();
