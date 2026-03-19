  
// M-PESA specific functionality

// STK Push simulation (for testing)
function simulateSTKPush(phone, amount) {
    console.log(`Simulating STK Push to ${phone} for KSh ${amount}`);
    
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                success: true,
                message: 'STK Push sent successfully'
            });
        }, 2000);
    });
}

// Format phone number for display
function formatPhoneDisplay(phone) {
    if(phone.startsWith('254')) {
        return '0' + phone.substring(3);
    }
    return phone;
}

// Validate M-PESA transaction
function validateTransaction(transactionId) {
    return fetch(`api/validate-transaction.php?id=${transactionId}`)
        .then(response => response.json());
}

// Get transaction history
function getTransactionHistory(phone, limit = 10) {
    return fetch(`api/transaction-history.php?phone=${phone}&limit=${limit}`)
        .then(response => response.json());
}

// Handle M-PESA callback response
function handleMpesaCallback(data) {
    if(data.ResultCode === 0) {
        // Payment successful
        showMessage('Payment successful!', 'success');
        
        // Display voucher if available
        if(data.voucher) {
            const voucherHtml = `
                <div class="voucher-display">
                    <h3>Your Voucher Code:</h3>
                    <div class="voucher-code">${data.voucher}</div>
                    <button onclick="copyVoucher('${data.voucher}')" class="btn-copy">
                        Copy Code
                    </button>
                </div>
            `;
            document.getElementById('voucher-section').innerHTML = voucherHtml;
        }
    } else {
        // Payment failed
        showMessage(data.ResultDesc || 'Payment failed', 'error');
    }
}

// Retry failed payment
function retryPayment(originalData) {
    if(confirm('Retry this payment?')) {
        processPayment(new Event('submit'));
    }
}