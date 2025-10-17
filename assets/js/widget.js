/**
 * Guardarian Payment Widget - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    class GuardarianWidget {
        constructor(element) {
            this.$widget = $(element);
            this.$form = this.$widget.find('.guardarian-payment-form');
            this.$amountInput = this.$widget.find('#guardarian-amount');
            this.$estimateDisplay = this.$widget.find('.estimate-amount');
            this.$rateDisplay = this.$widget.find('.exchange-rate');
            this.$submitBtn = this.$widget.find('.guardarian-submit-btn');
            this.$errorMsg = this.$widget.find('.guardarian-error');
            this.$loadingSpinner = this.$widget.find('.guardarian-loading');
            
            this.estimateTimeout = null;
            this.currentEstimate = null;
            this.refreshInterval = null;
            
            this.init();
        }
        
        init() {
            // Bind events
            this.$amountInput.on('focusout', this.handleAmountInput.bind(this));
            this.$form.on('submit', this.handleSubmit.bind(this));
            
            // Initial estimate if default amount is set
            if (this.$amountInput.val()) {
                this.getEstimate(this.$amountInput.val());
            }
            
            // Clear any existing interval
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }

            // Auto-refresh rate every 30 seconds
            this.refreshInterval = setInterval(() => {
                const amount = this.$amountInput.val();
                if (amount && amount > 0) {
                    this.getEstimate(amount, true);
                }
            }, 600000);
        }
        
        handleAmountInput(e) {
            clearTimeout(this.estimateTimeout);
            
            const amount = parseFloat(e.target.value);
            
            if (!amount || amount <= 0) {
                this.clearEstimate();
                return;
            }
            
            this.estimateTimeout = setTimeout(() => {
                this.getEstimate(amount);
            }, 500);
        }
        
        getEstimate(amount, silent = false) {
            if (!silent) {
                this.$widget.find('.fetching-rates').show();
                this.$submitBtn.prop('disabled', true);
            }
            
            $.ajax({
                url: guardarianWidget.ajax_url,
                method: 'POST',
                data: {
                    action: 'guardarian_get_estimate',
                    nonce: guardarianWidget.nonce,
                    amount: amount
                },
                success: (response) => {
                    if (response.success) {
                        this.updateEstimate(response.data);
                    } else {
                        if (!silent) {
                            this.showError(response.data.message);
                        }
                    }
                },
                error: () => {
                    if (!silent) {
                        this.showError(guardarianWidget.i18n.error_occurred);
                    }
                },
                complete: () => {
                    if (!silent) {
                        this.$widget.find('.fetching-rates').hide();
                        this.$submitBtn.prop('disabled', false);
                    }
                }
            });
        }
        
        updateEstimate(data) {
            this.currentEstimate = data;
            
            this.$estimateDisplay.text(
                parseFloat(data.estimated_amount).toFixed(6) + ' USDC'
            );
            
            this.$rateDisplay.text(
                '1 USD = ' + parseFloat(data.exchange_rate).toFixed(6) + ' USDC'
            );
            
            this.$widget.find('.estimate-display').slideDown();
            this.hideError();
        }
        
        clearEstimate() {
            this.$widget.find('.estimate-display').slideUp();
            this.currentEstimate = null;
        }
        
        handleSubmit(e) {
            e.preventDefault();
            
            const amount = parseFloat(this.$amountInput.val());
            const email = this.$widget.find('#guardarian-email').val();
            const name = this.$widget.find('#guardarian-name').val();
            
            // Validate amount
            if (!amount || amount <= 0) {
                this.showError(guardarianWidget.i18n.enter_amount);
                return;
            }
            
            if (amount < guardarianWidget.min_amount) {
                this.showError(guardarianWidget.i18n.min_amount_error);
                return;
            }
            
            if (guardarianWidget.max_amount > 0 && amount > guardarianWidget.max_amount) {
                this.showError(guardarianWidget.i18n.max_amount_error);
                return;
            }
            
            this.createTransaction(amount, email, name);
        }
        
        createTransaction(amount, email, name) {
            this.showLoading();
            this.$submitBtn.prop('disabled', true);
            
            $.ajax({
                url: guardarianWidget.ajax_url,
                method: 'POST',
                data: {
                    action: 'guardarian_create_transaction',
                    nonce: guardarianWidget.nonce,
                    amount: amount,
                    email: email,
                    name: name
                },
                success: (response) => {
                    if (response.success) {
                        // Redirect to payment URL
                        if (response.data.payment_url) {
                            window.location.href = response.data.payment_url;
                        } else {
                            this.showError('Redirect URL not available');
                        }
                    } else {
                        this.showError(response.data.message);
                        this.$submitBtn.prop('disabled', false);
                    }
                },
                error: () => {
                    this.showError(guardarianWidget.i18n.error_occurred);
                    this.$submitBtn.prop('disabled', false);
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        }
        
        showLoading() {
            this.$loadingSpinner.show();
        }
        
        hideLoading() {
            this.$loadingSpinner.hide();
        }
        
        showError(message) {
            this.$errorMsg.text(message).slideDown();
        }
        
        hideError() {
            this.$errorMsg.slideUp();
        }
    }
    
    // Initialize widgets
    $(document).ready(function() {
        $('.guardarian-payment-widget').each(function() {
            new GuardarianWidget(this);
        });
    });
    
})(jQuery);