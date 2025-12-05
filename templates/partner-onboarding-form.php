<?php
/**
 * Partner Onboarding Form Template
 *
 * @package Partner_CIU_Manager
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="partner-onboarding-form">
    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="progress-step active" data-step="1">
            <div class="progress-step-circle">1</div>
            <div class="progress-step-label"><?php esc_html_e('Company', 'partner-ciu-manager'); ?></div>
        </div>
        <div class="progress-step" data-step="2">
            <div class="progress-step-circle">2</div>
            <div class="progress-step-label"><?php esc_html_e('Contacts', 'partner-ciu-manager'); ?></div>
        </div>
        <div class="progress-step" data-step="3">
            <div class="progress-step-circle">3</div>
            <div class="progress-step-label"><?php esc_html_e('Finance', 'partner-ciu-manager'); ?></div>
        </div>
        <div class="progress-step" data-step="4">
            <div class="progress-step-circle">4</div>
            <div class="progress-step-label"><?php esc_html_e('Impact', 'partner-ciu-manager'); ?></div>
        </div>
        <div class="progress-step" data-step="5">
            <div class="progress-step-circle">5</div>
            <div class="progress-step-label"><?php esc_html_e('Review', 'partner-ciu-manager'); ?></div>
        </div>
    </div>

    <form id="partner-onboarding-form" enctype="multipart/form-data">
        <?php wp_nonce_field('partner_onboarding_nonce', 'partner_onboarding_nonce_field'); ?>

        <!-- STEP 1: COMPANY FIELDS -->
        <div class="onboarding-step" id="step-1">
            <div class="step-header">
                <h2><?php esc_html_e('Step 1: Company Information', 'partner-ciu-manager'); ?></h2>
                <p class="step-description"><?php esc_html_e('Your legal name is used for invoicing. Trading Name + logo show on your public profile.', 'partner-ciu-manager'); ?></p>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-field required">
                        <label><?php esc_html_e('Company Legal Name', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="company_legal_name" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('Trading Name', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional, public)', 'partner-ciu-manager'); ?></span></label>
                        <input type="text" name="company_trading_name" />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('Company Registration Number', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="company_registration_number" required />
                        <small class="field-hint"><?php esc_html_e('Required for UK/EU/US; optional elsewhere', 'partner-ciu-manager'); ?></small>
                    </div>

                    <div class="form-field required">
                        <label><?php esc_html_e('Company Type', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <select name="company_type" required>
                            <option value=""><?php esc_html_e('Select...', 'partner-ciu-manager'); ?></option>
                            <option value="private_ltd"><?php esc_html_e('Private Ltd', 'partner-ciu-manager'); ?></option>
                            <option value="public_co"><?php esc_html_e('Public Co', 'partner-ciu-manager'); ?></option>
                            <option value="non_profit"><?php esc_html_e('Non-profit', 'partner-ciu-manager'); ?></option>
                            <option value="charity"><?php esc_html_e('Charity', 'partner-ciu-manager'); ?></option>
                            <option value="foundation"><?php esc_html_e('Foundation', 'partner-ciu-manager'); ?></option>
                            <option value="other"><?php esc_html_e('Other', 'partner-ciu-manager'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field required">
                        <label><?php esc_html_e('Website URL', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="url" name="company_website" placeholder="https://" required />
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Registered Address', 'partner-ciu-manager'); ?></div>

                <div class="form-row">
                    <div class="form-field required">
                        <label><?php esc_html_e('Address Line 1', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="address1" required />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('City', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="city" required />
                    </div>

                    <div class="form-field required">
                        <label><?php esc_html_e('State/Region', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="state" required />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('Postal Code', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="postal_code" required />
                    </div>

                    <div class="form-field required">
                        <label><?php esc_html_e('Country', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <select name="country" required>
                            <option value=""><?php esc_html_e('Select...', 'partner-ciu-manager'); ?></option>
                            <option value="GB"><?php esc_html_e('United Kingdom', 'partner-ciu-manager'); ?></option>
                            <option value="US"><?php esc_html_e('United States', 'partner-ciu-manager'); ?></option>
                            <option value="FR"><?php esc_html_e('France', 'partner-ciu-manager'); ?></option>
                            <option value="DE"><?php esc_html_e('Germany', 'partner-ciu-manager'); ?></option>
                            <option value="ES"><?php esc_html_e('Spain', 'partner-ciu-manager'); ?></option>
                            <option value="IT"><?php esc_html_e('Italy', 'partner-ciu-manager'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('Company Logo', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional, public)', 'partner-ciu-manager'); ?></span></label>
                        <input type="file" name="company_logo" accept="image/png,image/svg+xml" />
                        <small class="field-hint"><?php esc_html_e('PNG or SVG, square crop, max 2MB', 'partner-ciu-manager'); ?></small>
                        <div id="logo-preview"></div>
                    </div>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="button button-secondary" disabled><?php esc_html_e('Back', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button button-primary next-step" data-next="2"><?php esc_html_e('Next: Contacts & Login', 'partner-ciu-manager'); ?></button>
            </div>
        </div>

        <!-- STEP 2: CONTACTS & LOGIN -->
        <div class="onboarding-step" id="step-2" style="display: none;">
            <div class="step-header">
                <h2><?php esc_html_e('Step 2: Contacts & Login', 'partner-ciu-manager'); ?></h2>
            </div>

            <div class="form-section">
                <div class="form-section-title"><?php esc_html_e('Primary Contact (Account Owner)', 'partner-ciu-manager'); ?></div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('Full Name', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="primary_contact_name" required />
                    </div>

                    <div class="form-field required">
                        <label><?php esc_html_e('Role/Title', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="text" name="primary_contact_role" placeholder="<?php esc_attr_e('e.g., Head of CSR', 'partner-ciu-manager'); ?>" required />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('Email (Login)', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="email" name="primary_contact_email" required />
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('Phone', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional)', 'partner-ciu-manager'); ?></span></label>
                        <input type="tel" name="primary_contact_phone" placeholder="+44 20 1234 5678" />
                        <small class="field-hint"><?php esc_html_e('Include country code', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Impact Reporting Contact', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional)', 'partner-ciu-manager'); ?></span></div>

                <div class="form-row two-columns">
                    <div class="form-field">
                        <label><?php esc_html_e('Full Name', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="impact_contact_name" />
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('Email', 'partner-ciu-manager'); ?></label>
                        <input type="email" name="impact_contact_email" />
                        <small class="field-hint"><?php esc_html_e('For press/ESG communications', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Login & Security', 'partner-ciu-manager'); ?></div>

                <div class="form-row">
                    <div class="form-field required">
                        <label><?php esc_html_e('Password', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <input type="password" name="password" id="password-input" required />
                        <small class="field-hint"><?php esc_html_e('Minimum 12 characters, 1 number, 1 symbol', 'partner-ciu-manager'); ?></small>
                        <div class="password-strength-meter">
                            <div class="strength-bar"></div>
                            <span class="strength-text"></span>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="enable_2fa" value="1" />
                            <?php esc_html_e('Enable Two-Factor Authentication (2FA)', 'partner-ciu-manager'); ?>
                        </label>
                        <div id="2fa-options" style="display: none; margin-top: 10px;">
                            <label>
                                <input type="radio" name="2fa_method" value="sms" checked /> <?php esc_html_e('SMS', 'partner-ciu-manager'); ?>
                            </label>
                            <label style="margin-left: 20px;">
                                <input type="radio" name="2fa_method" value="auth_app" /> <?php esc_html_e('Authenticator App', 'partner-ciu-manager'); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Team Access', 'partner-ciu-manager'); ?></div>
                <p class="form-description"><?php esc_html_e('Invite colleagues to access this account', 'partner-ciu-manager'); ?></p>

                <div id="team-members-container"></div>

                <button type="button" class="button button-secondary" id="add-team-member">+ <?php esc_html_e('Invite Colleague', 'partner-ciu-manager'); ?></button>
            </div>

            <div class="step-navigation">
                <button type="button" class="button button-secondary prev-step" data-prev="1"><?php esc_html_e('Back', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button button-primary next-step" data-next="3"><?php esc_html_e('Next: Finance & Tax', 'partner-ciu-manager'); ?></button>
            </div>
        </div>

        <!-- STEP 3: FINANCE & TAX -->
        <div class="onboarding-step" id="step-3" style="display: none;">
            <div class="step-header">
                <h2><?php esc_html_e('Step 3: Finance & Tax', 'partner-ciu-manager'); ?></h2>
            </div>

            <div class="form-section">
                <div class="form-section-title"><?php esc_html_e('Billing Preferences', 'partner-ciu-manager'); ?></div>

                <div class="form-row two-columns">
                    <div class="form-field required">
                        <label><?php esc_html_e('Preferred Currency', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <select name="preferred_currency" required>
                            <option value="GBP" selected><?php esc_html_e('GBP (£)', 'partner-ciu-manager'); ?></option>
                            <option value="EUR"><?php esc_html_e('EUR (€)', 'partner-ciu-manager'); ?></option>
                            <option value="USD"><?php esc_html_e('USD ($)', 'partner-ciu-manager'); ?></option>
                        </select>
                        <small class="field-hint"><?php esc_html_e('All costs default to GBP', 'partner-ciu-manager'); ?></small>
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('Invoicing Email', 'partner-ciu-manager'); ?></label>
                        <input type="email" name="invoicing_email" />
                        <small class="field-hint"><?php esc_html_e('If different from primary contact', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('PO Number', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional)', 'partner-ciu-manager'); ?></span></label>
                        <input type="text" name="po_number" placeholder="PO-2025-001" />
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Bank Details (for wire payments)', 'partner-ciu-manager'); ?></div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('Account Name', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="account_name" />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field">
                        <label><?php esc_html_e('IBAN / Account Number', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="account_number" />
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('SWIFT / Sort Code', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="swift_code" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="billing_address_same" id="billing-address-same" checked />
                            <?php esc_html_e('Billing address same as registered address', 'partner-ciu-manager'); ?>
                        </label>
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Tax Information', 'partner-ciu-manager'); ?></div>

                <div class="form-row two-columns">
                    <div class="form-field">
                        <label><?php esc_html_e('VAT Number', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="vat_id" placeholder="GB123456789" />
                        <small class="field-hint"><?php esc_html_e('Required where applicable', 'partner-ciu-manager'); ?></small>
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('Tax ID', 'partner-ciu-manager'); ?></label>
                        <input type="text" name="tax_id" />
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Compliance', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional but recommended)', 'partner-ciu-manager'); ?></span></div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('Proof of Business', 'partner-ciu-manager'); ?></label>
                        <input type="file" name="compliance_docs[]" accept=".pdf,.jpg,.jpeg,.png" multiple />
                        <small class="field-hint"><?php esc_html_e('Certificate of Incorporation, 501(c)(3) letter, etc.', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="button button-secondary prev-step" data-prev="2"><?php esc_html_e('Back', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button button-primary next-step" data-next="4"><?php esc_html_e('Next: Impact & Visibility', 'partner-ciu-manager'); ?></button>
            </div>
        </div>

        <!-- STEP 4: IMPACT & VISIBILITY -->
        <div class="onboarding-step" id="step-4" style="display: none;">
            <div class="step-header">
                <h2><?php esc_html_e('Step 4: Impact & Visibility', 'partner-ciu-manager'); ?></h2>
            </div>

            <div class="form-section">
                <div class="form-section-title"><?php esc_html_e('Impact Preferences', 'partner-ciu-manager'); ?></div>
                <p class="form-description"><?php esc_html_e('Select your primary environmental focus areas', 'partner-ciu-manager'); ?></p>

                <div class="checkbox-grid">
                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="oceans" />
                        <span class="checkbox-card-icon">🌊</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Oceans', 'partner-ciu-manager'); ?></span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="forests" />
                        <span class="checkbox-card-icon">🌳</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Forests', 'partner-ciu-manager'); ?></span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="endangered_species" />
                        <span class="checkbox-card-icon">🐅</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Endangered Species', 'partner-ciu-manager'); ?></span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="climate" />
                        <span class="checkbox-card-icon">🌍</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Climate', 'partner-ciu-manager'); ?></span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="freshwater" />
                        <span class="checkbox-card-icon">💧</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Freshwater', 'partner-ciu-manager'); ?></span>
                    </label>

                    <label class="checkbox-card">
                        <input type="checkbox" name="primary_categories[]" value="soil" />
                        <span class="checkbox-card-icon">🌱</span>
                        <span class="checkbox-card-label"><?php esc_html_e('Soil', 'partner-ciu-manager'); ?></span>
                    </label>
                </div>

                <div class="form-section-title"><?php esc_html_e('Public Display Settings', 'partner-ciu-manager'); ?></div>

                <div class="form-row">
                    <div class="form-field required">
                        <label><?php esc_html_e('Visibility', 'partner-ciu-manager'); ?> <span class="required-indicator">*</span></label>
                        <select name="visibility" required>
                            <option value="public"><?php esc_html_e('Public (company name & logo shown)', 'partner-ciu-manager'); ?></option>
                            <option value="semi_public"><?php esc_html_e('Semi-public (logo hidden, name shown)', 'partner-ciu-manager'); ?></option>
                            <option value="anonymous"><?php esc_html_e('Anonymous (completely private)', 'partner-ciu-manager'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('CSR Statement', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(public, max 200 words)', 'partner-ciu-manager'); ?></span></label>
                        <textarea name="csr_statement" rows="5" maxlength="1000"></textarea>
                        <small class="field-hint character-counter">0 / 1000 <?php esc_html_e('characters', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Social Links', 'partner-ciu-manager'); ?> <span class="optional"><?php esc_html_e('(optional)', 'partner-ciu-manager'); ?></span></div>

                <div class="form-row">
                    <div class="form-field">
                        <label><?php esc_html_e('LinkedIn', 'partner-ciu-manager'); ?></label>
                        <input type="url" name="social_linkedin" placeholder="https://linkedin.com/company/..." />
                    </div>
                </div>

                <div class="form-row two-columns">
                    <div class="form-field">
                        <label><?php esc_html_e('Twitter / X', 'partner-ciu-manager'); ?></label>
                        <input type="url" name="social_twitter" placeholder="https://twitter.com/..." />
                    </div>

                    <div class="form-field">
                        <label><?php esc_html_e('Instagram', 'partner-ciu-manager'); ?></label>
                        <input type="url" name="social_instagram" placeholder="https://instagram.com/..." />
                    </div>
                </div>

                <div class="form-section-title"><?php esc_html_e('Attribution Rules (for CIUs)', 'partner-ciu-manager'); ?></div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="proportional_allocation" value="1" checked />
                            <?php esc_html_e('Attribute CIUs proportionally across selected categories', 'partner-ciu-manager'); ?>
                        </label>
                        <small class="field-hint"><?php esc_html_e('If off, you can allocate CIUs per category during checkout', 'partner-ciu-manager'); ?></small>
                    </div>
                </div>
            </div>

            <div class="step-navigation">
                <button type="button" class="button button-secondary prev-step" data-prev="3"><?php esc_html_e('Back', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button button-primary next-step" data-next="5"><?php esc_html_e('Next: Review & Confirm', 'partner-ciu-manager'); ?></button>
            </div>
        </div>

        <!-- STEP 5: REVIEW & CONFIRM -->
        <div class="onboarding-step" id="step-5" style="display: none;">
            <div class="step-header">
                <h2><?php esc_html_e('Step 5: Review & Confirm', 'partner-ciu-manager'); ?></h2>
                <p class="step-description"><?php esc_html_e('Please review your information before submitting', 'partner-ciu-manager'); ?></p>
            </div>

            <div class="review-section">
                <div class="review-card">
                    <h3><?php esc_html_e('Company Information', 'partner-ciu-manager'); ?></h3>
                    <div class="review-grid">
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Legal Name:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-legal-name"></span>
                        </div>
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Trading Name:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-trading-name"></span>
                        </div>
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Website:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-website"></span>
                        </div>
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Country:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-country"></span>
                        </div>
                    </div>
                    <button type="button" class="edit-section-btn" data-step="1"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></button>
                </div>

                <div class="review-card">
                    <h3><?php esc_html_e('Contacts & Login', 'partner-ciu-manager'); ?></h3>
                    <div class="review-grid">
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Primary Contact:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-primary-contact"></span>
                        </div>
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Email:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-email"></span>
                        </div>
                    </div>
                    <button type="button" class="edit-section-btn" data-step="2"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></button>
                </div>

                <div class="review-card">
                    <h3><?php esc_html_e('Finance & Tax', 'partner-ciu-manager'); ?></h3>
                    <div class="review-grid">
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Preferred Currency:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-currency"></span>
                        </div>
                    </div>
                    <button type="button" class="edit-section-btn" data-step="3"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></button>
                </div>

                <div class="review-card">
                    <h3><?php esc_html_e('Impact & Visibility', 'partner-ciu-manager'); ?></h3>
                    <div class="review-grid">
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Focus Areas:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-categories"></span>
                        </div>
                        <div class="review-item">
                            <span class="review-label"><?php esc_html_e('Visibility:', 'partner-ciu-manager'); ?></span>
                            <span class="review-value" id="review-visibility"></span>
                        </div>
                    </div>
                    <button type="button" class="edit-section-btn" data-step="4"><?php esc_html_e('Edit', 'partner-ciu-manager'); ?></button>
                </div>
            </div>

            <div class="confirmation-section">
                <label class="checkbox-label required">
                    <input type="checkbox" name="terms_accepted" id="terms-checkbox" required />
                    <?php esc_html_e('I confirm the information is accurate and agree to', 'partner-ciu-manager'); ?> <a href="/terms" target="_blank"><?php esc_html_e('Partner Terms', 'partner-ciu-manager'); ?></a>.
                </label>
            </div>

            <div class="step-navigation">
                <button type="button" class="button button-secondary prev-step" data-prev="4"><?php esc_html_e('Back', 'partner-ciu-manager'); ?></button>
                <button type="button" class="button" id="save-draft-btn"><?php esc_html_e('Save as Draft', 'partner-ciu-manager'); ?></button>
                <button type="submit" class="button button-primary" id="create-account-btn" disabled><?php esc_html_e('Create Account', 'partner-ciu-manager'); ?></button>
            </div>
        </div>
    </form>
</div>
