document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const paymentOptions = document.querySelectorAll('.payment-option');
    const creditPayment = document.getElementById('credit-payment');
    const paypalPayment = document.getElementById('paypal-payment');
    const bankPayment = document.getElementById('bank-payment');
    const changeAddressButton = document.getElementById('change-address-btn');
    const addressForm = document.getElementById('address-form');
    const saveAddressButton = document.getElementById('save-address-btn');
    const cancelAddressButton = document.getElementById('cancel-address-btn');
    const deliveryAddressElement = document.getElementById('delivery-address');
    const addCardButton = document.getElementById('add-card-btn');
    const cardForm = document.getElementById('card-form');
    const saveCardButton = document.getElementById('save-card-btn');
    const cancelCardButton = document.getElementById('cancel-card-btn');
    const payButton = document.getElementById('pay-btn');
    const confirmationModal = document.getElementById('confirmation-modal');
    const processingModal = document.getElementById('processing-modal');
    const closeModal = document.querySelector('.close-modal');
    const continueButton = document.getElementById('continue-btn');
    const viewDetailsButton = document.getElementById('view-details-btn');
    const confirmAmountElement = document.getElementById('confirm-amount');
    const confirmAddressElement = document.getElementById('confirm-address');
    const confirmDeliveryElement = document.getElementById('confirm-delivery');
    const connectPaypalButton = document.getElementById('connect-paypal');
    const confirmTransferButton = document.getElementById('confirm-transfer-btn');
    const copyButtons = document.querySelectorAll('.copy-btn');
    const totalAmountElement = document.getElementById('total-amount');
    
    // Payment option selection
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            paymentOptions.forEach(o => o.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Show the corresponding payment section
            const method = this.dataset.method;
            
            // Hide all payment sections
            creditPayment.classList.add('hidden');
            paypalPayment.classList.add('hidden');
            bankPayment.classList.add('hidden');
            
            // Show the selected payment section
            if (method === 'credit') {
                creditPayment.classList.remove('hidden');
            } else if (method === 'paypal') {
                paypalPayment.classList.remove('hidden');
            } else if (method === 'bank') {
                bankPayment.classList.remove('hidden');
            }
        });
    });
    
    // Change address
    changeAddressButton.addEventListener('click', function() {
        addressForm.classList.remove('hidden');
    });
    
    // Save address
    saveAddressButton.addEventListener('click', function() {
        const fullname = document.getElementById('fullname').value;
        const street = document.getElementById('street').value;
        const city = document.getElementById('city').value;
        const state = document.getElementById('state').value;
        const zip = document.getElementById('zip').value;
        
        if (fullname && street && city && state && zip) {
            deliveryAddressElement.textContent = `${fullname}, ${street}, ${city}, ${state} ${zip}`;
            addressForm.classList.add('hidden');
            
            // Update confirmation address
            confirmAddressElement.textContent = deliveryAddressElement.textContent;
        } else {
            showAlert('Please fill in all address fields');
        }
    });
    
    // Cancel address change
    cancelAddressButton.addEventListener('click', function() {
        addressForm.classList.add('hidden');
    });
    
    // Add new card
    addCardButton.addEventListener('click', function() {
        cardForm.classList.remove('hidden');
    });
    
    // Save card
    saveCardButton.addEventListener('click', function() {
        // Simple validation
        const cardNumber = document.getElementById('card-number').value;
        const cardName = document.getElementById('card-name').value;
        const expiry = document.getElementById('expiry').value;
        const cvv = document.getElementById('cvv').value;
        
        if (cardNumber && cardName && expiry && cvv) {
            // In a real app, you would save the card to the user's account
            cardForm.classList.add('hidden');
            
            // Select the first saved card
            document.getElementById('card1').checked = true;
            showAlert('Card saved successfully!');
        } else {
            showAlert('Please fill in all card details');
        }
    });
    
    // Cancel card
    cancelCardButton.addEventListener('click', function() {
        cardForm.classList.add('hidden');
    });
    
    // Format card number with spaces
    const cardNumberInput = document.getElementById('card-number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            this.value = formattedValue;
        });
    }
    
    // Format expiry date
    const expiryInput = document.getElementById('expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                this.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                this.value = value;
            }
        });
    }
    
    // Copy bank details
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.dataset.value;
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Change icon temporarily
                const originalIcon = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                
                setTimeout(() => {
                    this.innerHTML = originalIcon;
                }, 1500);
            });
        });
    });
    
    // Connect with PayPal
    connectPaypalButton.addEventListener('click', function() {
        const paypalEmail = document.getElementById('paypal-email').value;
        
        if (paypalEmail && isValidEmail(paypalEmail)) {
            showProcessingModal();
            
            // Simulate PayPal connection
            setTimeout(() => {
                hideProcessingModal();
                showAlert('Connected with PayPal successfully!');
            }, 1500);
        } else {
            showAlert('Please enter a valid PayPal email address');
        }
    });
    
    // Confirm bank transfer
    confirmTransferButton.addEventListener('click', function() {
        const transferDate = document.getElementById('transfer-date').value;
        const transferAmount = document.getElementById('transfer-amount').value;
        const transferReference = document.getElementById('transfer-reference').value;
        
        if (transferDate && transferAmount && transferReference) {
            showProcessingModal();
            
            // Simulate transfer confirmation
            setTimeout(() => {
                hideProcessingModal();
                completeTransaction();
            }, 1500);
        } else {
            showAlert('Please fill in all transfer details');
        }
    });
    
    // Complete purchase button
    payButton.addEventListener('click', function() {
        const activeMethod = document.querySelector('.payment-option.active').dataset.method;
        
        // Validate based on payment method
        if (activeMethod === 'credit') {
            if (!document.querySelector('input[name="card"]:checked')) {
                showAlert('Please select a payment method');
                return;
            }
            
            showProcessingModal();
            
            // Simulate payment processing
            setTimeout(() => {
                hideProcessingModal();
                completeTransaction();
            }, 2000);
        } else if (activeMethod === 'paypal') {
            const paypalEmail = document.getElementById('paypal-email').value;
            
            if (!paypalEmail) {
                showAlert('Please enter your PayPal email');
                return;
            }
            
            showProcessingModal();
            
            // Simulate PayPal payment
            setTimeout(() => {
                hideProcessingModal();
                completeTransaction();
            }, 2000);
        } else if (activeMethod === 'bank') {
            showAlert('Please use the "Confirm Transfer" button after making your bank transfer');
        }
    });
    
    // Close modal
    closeModal.addEventListener('click', function() {
        confirmationModal.classList.add('hidden');
    });
    
    // Continue button
    continueButton.addEventListener('click', function() {
        confirmationModal.classList.add('hidden');
        // In a real app, you would redirect to a dashboard or home page
        window.location.reload();
    });
    
    // View details button
    viewDetailsButton.addEventListener('click', function() {
        showAlert('Order details will be available in your account dashboard');
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === confirmationModal) {
            confirmationModal.classList.add('hidden');
        }
    });
    
    // Helper functions
    function showAlert(message) {
        alert(message);
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showProcessingModal() {
        processingModal.classList.remove('hidden');
    }
    
    function hideProcessingModal() {
        processingModal.classList.add('hidden');
    }
    
    function completeTransaction() {
        // Update confirmation details
        confirmAmountElement.textContent = totalAmountElement.textContent;
        confirmAddressElement.textContent = deliveryAddressElement.textContent;
        
        // Calculate estimated delivery date (14 days from now)
        const deliveryDate = new Date();
        deliveryDate.setDate(deliveryDate.getDate() + 14);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        confirmDeliveryElement.textContent = deliveryDate.toLocaleDateString('en-US', options);
        
        // Show confirmation modal
        confirmationModal.classList.remove('hidden');
    }
    
    // Pre-fill transfer amount with the total due
    const transferAmountInput = document.getElementById('transfer-amount');
    if (transferAmountInput) {
        transferAmountInput.value = totalAmountElement.textContent.replace('$', '');
    }
    
    // Pre-fill transfer reference
    const transferReferenceInput = document.getElementById('transfer-reference');
    const bankReferenceElement = document.getElementById('bank-reference');
    if (transferReferenceInput && bankReferenceElement) {
        transferReferenceInput.value = bankReferenceElement.textContent;
    }
});