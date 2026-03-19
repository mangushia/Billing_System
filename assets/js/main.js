  
// Main JavaScript for Ardthon Solutions WiFi Hotspot

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if(hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if(event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if(target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Form validation
function validatePhone(phone) {
    const phoneRegex = /^(07|01|\+254|254)[0-9]{8}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

function showMessage(message, type = 'success') {
    const statusDiv = document.getElementById('payment-status');
    if(statusDiv) {
        statusDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => {
            statusDiv.innerHTML = '';
        }, 5000);
    }
}

// Loading spinner
function showLoading(show = true) {
    const spinner = document.getElementById('loading-spinner');
    if(show) {
        if(!spinner) {
            const div = document.createElement('div');
            div.id = 'loading-spinner';
            div.className = 'spinner';
            div.innerHTML = '<div class="spinner-border"></div>';
            document.body.appendChild(div);
        }
    } else {
        if(spinner) {
            spinner.remove();
        }
    }
}

// Package selection
function selectPackage(packageId, amount) {
    document.getElementById('package_id').value = packageId;
    document.getElementById('amount').textContent = amount;
    document.getElementById('paymentModal').style.display = 'block';
}

// Process M-PESA payment
function processPayment(event) {
    event.preventDefault();
    
    const phone = document.getElementById('phone').value;
    const packageId = document.getElementById('package_id').value;
    
    if(!validatePhone(phone)) {
        showMessage('Please enter a valid Safaricom phone number', 'error');
        return;
    }
    
    showLoading(true);
    
    // Send payment request
    fetch('billing/process-payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `phone=${encodeURIComponent(phone)}&package_id=${packageId}`
    })
    .then(response => response.json())
    .then(data => {
        showLoading(false);
        
        if(data.success) {
            showMessage('STK Push sent. Please check your phone and enter PIN.', 'success');
            // Start polling for status
            checkPaymentStatus(data.checkoutRequestID);
        } else {
            showMessage(data.message || 'Payment failed. Please try again.', 'error');
        }
    })
    .catch(error => {
        showLoading(false);
        showMessage('Network error. Please try again.', 'error');
        console.error('Error:', error);
    });
}

// Check payment status
function checkPaymentStatus(checkoutRequestID) {
    const checkInterval = setInterval(() => {
        fetch('api/check-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `checkoutRequestID=${checkoutRequestID}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.ResultCode === 0) {
                clearInterval(checkInterval);
                showMessage('Payment successful! Check your phone for voucher code.', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else if(data.ResultCode !== 1037) { // 1037 means pending
                clearInterval(checkInterval);
                showMessage('Payment failed. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
        });
    }, 5000); // Check every 5 seconds
    
    // Stop checking after 2 minutes
    setTimeout(() => {
        clearInterval(checkInterval);
    }, 120000);
}

// Copy voucher code to clipboard
function copyVoucher(code) {
    navigator.clipboard.writeText(code).then(() => {
        showMessage('Voucher code copied to clipboard!');
    }).catch(err => {
        console.error('Could not copy text: ', err);
    });
}