/**
 * Partner Onboarding Form JavaScript
 *
 * @package Partner_CIU_Manager
 */

(function($) {
    'use strict';

    var onboardingForm = {
        currentStep: 1,
        totalSteps: 5,

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.updateProgressIndicator();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Next step
            $(document).on('click', '.next-step', function(e) {
                e.preventDefault();
                var nextStep = $(this).data('next');

                if (self.validateStep(self.currentStep)) {
                    self.showStep(nextStep);
                }
            });

            // Previous step
            $(document).on('click', '.prev-step', function(e) {
                e.preventDefault();
                var prevStep = $(this).data('prev');
                self.showStep(prevStep);
            });

            // Edit section from review
            $(document).on('click', '.edit-section-btn', function(e) {
                e.preventDefault();
                var step = $(this).data('step');
                self.showStep(step);
            });

            // Password strength
            $('input[name="password"]').on('input', function() {
                self.checkPasswordStrength($(this).val());
            });

            // 2FA toggle
            $('input[name="enable_2fa"]').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#2fa-options').slideDown(200);
                } else {
                    $('#2fa-options').slideUp(200);
                }
            });

            // Add team member
            $('#add-team-member').on('click', function(e) {
                e.preventDefault();
                self.addTeamMember();
            });

            // Remove team member
            $(document).on('click', '.remove-team-member', function(e) {
                e.preventDefault();
                if (confirm(partnerOnboardingAjax.strings.confirmRemove)) {
                    $(this).closest('.team-member-row').fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });

            // Billing address toggle
            $('#billing-address-same').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#separate-billing-address').slideUp(200);
                } else {
                    $('#separate-billing-address').slideDown(200);
                }
            });

            // Character counter
            $('textarea[name="csr_statement"]').on('input', function() {
                var length = $(this).val().length;
                $('.character-counter').text(length + ' / 1000 characters');
            });

            // Terms checkbox
            $('#terms-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#create-account-btn').prop('disabled', false);
                } else {
                    $('#create-account-btn').prop('disabled', true);
                }
            });

            // Logo preview
            $('input[name="company_logo"]').on('change', function(e) {
                self.previewLogo(e.target);
            });

            // Save draft
            $('#save-draft-btn').on('click', function(e) {
                e.preventDefault();
                self.saveDraft();
            });

            // Submit form
            $('#partner-onboarding-form').on('submit', function(e) {
                e.preventDefault();
                self.submitForm();
            });
        },

        /**
         * Show specific step
         */
        showStep: function(stepNumber) {
            $('.onboarding-step').hide();
            $('#step-' + stepNumber).fadeIn(300);
            this.currentStep = stepNumber;
            this.updateProgressIndicator();

            // Scroll to top
            $('html, body').animate({
                scrollTop: $('.partner-onboarding-form').offset().top - 50
            }, 300);

            // Update review if on step 5
            if (stepNumber === 5) {
                this.populateReview();
            }
        },

        /**
         * Update progress indicator
         */
        updateProgressIndicator: function() {
            $('.progress-step').each(function(index) {
                var stepNum = index + 1;
                var $step = $(this);

                if (stepNum < onboardingForm.currentStep) {
                    $step.addClass('completed').removeClass('active');
                } else if (stepNum === onboardingForm.currentStep) {
                    $step.addClass('active').removeClass('completed');
                } else {
                    $step.removeClass('active completed');
                }
            });
        },

        /**
         * Validate current step
         */
        validateStep: function(step) {
            var isValid = true;
            var $currentStep = $('#step-' + step);

            // Check required fields
            $currentStep.find('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });

            // Custom validations
            if (step === 1) {
                // Validate URL format
                var website = $('input[name="company_website"]').val();
                if (website && !this.isValidURL(website)) {
                    alert(partnerOnboardingAjax.strings.invalidURL);
                    isValid = false;
                }

                // Validate logo size
                var logoFile = $('input[name="company_logo"]')[0].files[0];
                if (logoFile && logoFile.size > 2 * 1024 * 1024) {
                    alert('Logo file size must be less than 2MB');
                    isValid = false;
                }
            }

            if (step === 2) {
                // Validate email
                var email = $('input[name="primary_contact_email"]').val();
                if (email && !this.isValidEmail(email)) {
                    alert(partnerOnboardingAjax.strings.invalidEmail);
                    isValid = false;
                }

                // Validate password strength
                var password = $('input[name="password"]').val();
                if (password && !this.isStrongPassword(password)) {
                    alert(partnerOnboardingAjax.strings.weakPassword);
                    isValid = false;
                }
            }

            if (!isValid) {
                alert(partnerOnboardingAjax.strings.fillRequired);
            }

            return isValid;
        },

        /**
         * Helper: Validate URL
         */
        isValidURL: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },

        /**
         * Helper: Validate email
         */
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Helper: Check password strength
         */
        isStrongPassword: function(password) {
            return password.length >= 12 &&
                   /[0-9]/.test(password) &&
                   /[!@#$%^&*]/.test(password);
        },

        /**
         * Check password strength and update meter
         */
        checkPasswordStrength: function(password) {
            var strength = 0;

            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*]/.test(password)) strength++;

            var $meter = $('.strength-bar');
            var $text = $('.strength-text');

            $meter.removeClass('weak medium strong');

            if (strength <= 2) {
                $meter.addClass('weak');
                $text.text('Weak').css('color', '#dc3232');
            } else if (strength <= 4) {
                $meter.addClass('medium');
                $text.text('Medium').css('color', '#dba617');
            } else {
                $meter.addClass('strong');
                $text.text('Strong').css('color', '#00a32a');
            }
        },

        /**
         * Add team member
         */
        addTeamMember: function() {
            var memberHTML = '<div class="team-member-row" style="display: flex; gap: 10px; margin-bottom: 10px;">' +
                '<input type="text" name="team_members[][name]" placeholder="Full Name" style="flex: 2;" />' +
                '<input type="email" name="team_members[][email]" placeholder="Email" style="flex: 2;" />' +
                '<select name="team_members[][role]" style="flex: 1;">' +
                    '<option value="admin">Admin</option>' +
                    '<option value="finance">Finance</option>' +
                    '<option value="viewer">Viewer</option>' +
                '</select>' +
                '<button type="button" class="button button-small remove-team-member">Remove</button>' +
            '</div>';

            $('#team-members-container').append(memberHTML);
        },

        /**
         * Preview logo
         */
        previewLogo: function(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#logo-preview').html('<img src="' + e.target.result + '" alt="Logo Preview" />');
                };

                reader.readAsDataURL(input.files[0]);
            }
        },

        /**
         * Populate review step
         */
        populateReview: function() {
            // Company
            $('#review-legal-name').text($('input[name="company_legal_name"]').val());
            $('#review-trading-name').text($('input[name="company_trading_name"]').val() || 'N/A');
            $('#review-website').text($('input[name="company_website"]').val());
            $('#review-country').text($('select[name="country"] option:selected').text());

            // Contacts
            $('#review-primary-contact').text($('input[name="primary_contact_name"]').val());
            $('#review-email').text($('input[name="primary_contact_email"]').val());

            // Finance
            $('#review-currency').text($('select[name="preferred_currency"]').val());

            // Impact
            var categories = [];
            $('input[name="primary_categories[]"]:checked').each(function() {
                categories.push($(this).closest('.checkbox-card').find('.checkbox-card-label').text());
            });
            $('#review-categories').text(categories.join(', ') || 'None selected');
            $('#review-visibility').text($('select[name="visibility"] option:selected').text());
        },

        /**
         * Save as draft
         */
        saveDraft: function() {
            var formData = $('#partner-onboarding-form').serialize();

            $.ajax({
                url: partnerOnboardingAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_onboarding_draft',
                    nonce: partnerOnboardingAjax.nonce,
                    draft_data: formData
                },
                beforeSend: function() {
                    $('#save-draft-btn').prop('disabled', true).text('Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $('#save-draft-btn').prop('disabled', false).text('Save as Draft');
                    } else {
                        alert('Error: ' + response.data.message);
                        $('#save-draft-btn').prop('disabled', false).text('Save as Draft');
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                    $('#save-draft-btn').prop('disabled', false).text('Save as Draft');
                }
            });
        },

        /**
         * Submit form
         */
        submitForm: function() {
            if (!$('#terms-checkbox').is(':checked')) {
                alert('Please accept the terms and conditions');
                return;
            }

            var formData = new FormData($('#partner-onboarding-form')[0]);
            formData.append('action', 'submit_partner_onboarding');
            formData.append('nonce', partnerOnboardingAjax.nonce);

            $.ajax({
                url: partnerOnboardingAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('.partner-onboarding-form').addClass('loading');
                    $('#create-account-btn').prop('disabled', true).text('Creating Account...');
                },
                success: function(response) {
                    $('.partner-onboarding-form').removeClass('loading');
                    if (response.success) {
                        alert(response.data.message);
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert('Error: ' + response.data.message);
                        $('#create-account-btn').prop('disabled', false).text('Create Account');
                    }
                },
                error: function() {
                    $('.partner-onboarding-form').removeClass('loading');
                    alert('Something went wrong. Please try again.');
                    $('#create-account-btn').prop('disabled', false).text('Create Account');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        onboardingForm.init();
    });

})(jQuery);
