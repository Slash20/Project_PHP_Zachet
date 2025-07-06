document.addEventListener('DOMContentLoaded', () => {
    const amountInput = document.getElementById('amount');
    const fromSelect = document.getElementById('from-currency');
    const toSelect = document.getElementById('to-currency');
    const convertBtn = document.getElementById('convert-btn');
    const swapBtn = document.getElementById('swap-btn');
    const resultDiv = document.getElementById('result');
    const ratesTable = document.getElementById('rates-body');
  
    async function loadCurrencies() {
        try {
            const response = await fetch('/api/currency');
            const rates = await response.json();
            
            fromSelect.innerHTML = '';
            toSelect.innerHTML = '';
            
            for (const currency in rates) {
                fromSelect.innerHTML += `<option value="${currency}">${currency}</option>`;
                toSelect.innerHTML += `<option value="${currency}">${currency}</option>`;
            }
            
            fromSelect.value = 'EUR';
            toSelect.value = 'USD';
            
            updateRatesTable(rates);
        } catch (error) {
            console.error('Failed to load currencies:', error);
            resultDiv.innerHTML = `<div class="error">Failed to load currency data</div>`;
        }
    }
  
    function updateRatesTable(rates) {
        ratesTable.innerHTML = '';
        for (const [currency, rate] of Object.entries(rates)) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${currency}</td>
                <td>${rate.toFixed(6)}</td>
            `;
            ratesTable.appendChild(row);
        }
    }
  
    async function convertCurrency() {
        const amount = parseFloat(amountInput.value);
        const from = fromSelect.value;
        const to = toSelect.value;
        
        if (isNaN(amount)) {
            resultDiv.innerHTML = `<div class="error">Please enter valid amount</div>`;
            return;
        }
        
        try {
            const response = await fetch(
                `/api/convert?from=${from}&to=${to}&amount=${amount}`
            );
            const data = await response.json();
            
            if (data.error) {
                resultDiv.innerHTML = `<div class="error">${data.error}</div>`;
                return;
            }
            
            resultDiv.innerHTML = `
                <div class="success">
                    ${amount.toFixed(2)} ${from} =
                    <strong>${data.result.toFixed(2)} ${to}</strong>
                </div>
            `;
        } catch (error) {
            resultDiv.innerHTML = `<div class="error">Conversion failed</div>`;
        }
    }

    function swapCurrencies() {
        const temp = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = temp;
    }
  
    loadCurrencies();
    convertBtn.addEventListener('click', convertCurrency);
    swapBtn.addEventListener('click', swapCurrencies);
    amountInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') convertCurrency();
    });
  });