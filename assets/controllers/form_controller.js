import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["form", "submit", "error"]
    static values = {
        async: { type: Boolean, default: false },
        resetOnSuccess: { type: Boolean, default: false },
        validateOnBlur: { type: Boolean, default: true },
        preventDoubleSubmit: { type: Boolean, default: true }
    }

    connect() {
        if (this.validateOnBlurValue) {
            this.setupValidation()
        }

        // Store original button text for all submit buttons
        this.storeOriginalButtonTexts()
    }

    storeOriginalButtonTexts() {
        this.formTarget.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(button => {
            if (!button.dataset.originalText) {
                const textElement = button.querySelector('.indicator-label') || button
                button.dataset.originalText = textElement.textContent.trim()
            }
        })
    }

    setupValidation() {
        this.formTarget.querySelectorAll("input, textarea, select").forEach(field => {
            field.addEventListener("blur", this.validateField.bind(this))
            field.addEventListener("input", this.clearFieldError.bind(this))
        })
    }

    async submit(event) {
        if (this.preventDoubleSubmitValue && this.isSubmitting) {
            event.preventDefault();
            return;
        }

        if (!this.asyncValue || this.isSecurityForm()) {
            this.handleNonAsyncSubmit(event);
            return;
        }

        event.preventDefault();
        this.setSubmitting(true); // Show spinner
        this.clearErrors();
        this.isSubmitting = true;

        try {
            const formData = new FormData(this.formTarget);
            const response = await fetch(this.formTarget.action, {
                method: this.formTarget.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected) {
                window.location.href = response.url;
                return;
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const result = await response.json();
                if (response.ok) {
                    this.handleSuccess(result);
                } else {
                    this.handleErrors(result.errors || {});
                }
            } else {
                const html = await response.text();
                this.handleHtmlResponse(html, response.ok);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.handleErrors({ _global: ["An error occurred during form submission."] });
        } finally {
            this.setSubmitting(false); // Hide spinner
            this.isSubmitting = false;
        }
    }

    isSecurityForm() {
        // Check if this is a security-related form (login, etc.)
        return this.formTarget.action.includes('/login') ||
            this.formTarget.action.includes('/logout') ||
            this.formTarget.querySelector('input[name="_csrf_token"]') !== null
    }

    handleNonAsyncSubmit(event) {
        // For security forms, just add loading state and let Symfony handle it
        this.setSubmitting(true)
        this.clearErrors()

        // Don't prevent the default - let the form submit naturally
        this.isSubmitting = true
    }

    async handleAsyncSubmit() {
        this.setSubmitting(true)
        this.clearErrors()
        this.isSubmitting = true

        try {
            const formData = new FormData(this.formTarget)
            const response = await fetch(this.formTarget.action, {
                method: this.formTarget.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })

            if (response.redirected) {
                // Handle redirects (common in Symfony forms)
                window.location.href = response.url
                return
            }

            const contentType = response.headers.get('content-type')

            if (contentType && contentType.includes('application/json')) {
                const result = await response.json()

                if (response.ok) {
                    this.handleSuccess(result)
                } else {
                    this.handleErrors(result.errors || {})
                }
            } else {
                // Handle HTML responses (e.g., form with errors)
                const html = await response.text()
                this.handleHtmlResponse(html, response.ok)
            }
        } catch (error) {
            console.error('Form submission error:', error)
            this.handleErrors({ _global: ["Une erreur s'est produite lors de l'envoi du formulaire"] })
        } finally {
            this.setSubmitting(false)
            this.isSubmitting = false
        }
    }

    handleHtmlResponse(html, isSuccess) {
        if (isSuccess) {
            // Replace form content or redirect
            this.dispatch("success", { detail: { html } })
        } else {
            // Parse HTML for errors
            const parser = new DOMParser()
            const doc = parser.parseFromString(html, 'text/html')
            const errors = this.extractErrorsFromHtml(doc)
            this.handleErrors(errors)
        }
    }

    extractErrorsFromHtml(doc) {
        const errors = {}

        // Extract form errors
        doc.querySelectorAll('.alert-danger, .invalid-feedback').forEach(errorElement => {
            errors._global = errors._global || []
            errors._global.push(errorElement.textContent.trim())
        })

        return errors
    }

    validateField(event) {
        const field = event.target
        const value = field.value.trim()
        const errors = []

        // Skip validation for certain field types
        if (field.type === 'hidden' || field.name.startsWith('_')) {
            return
        }

        // Required validation
        if (field.required && !value) {
            errors.push("Ce champ est requis")
        }

        // Email validation
        if (field.type === "email" && value && !this.isValidEmail(value)) {
            errors.push("Email invalide")
        }

        // Username/email validation for login forms
        if (field.name === 'username' && value) {
            if (value.includes('@') && !this.isValidEmail(value)) {
                errors.push("Format d'email invalide")
            }
        }

        // Password validation
        if (field.type === "password" && value && field.minLength && value.length < field.minLength) {
            errors.push(`Minimum ${field.minLength} caractères`)
        }

        // URL validation
        if (field.type === "url" && value && !this.isValidUrl(value)) {
            errors.push("URL invalide")
        }

        // Number validation
        if (field.type === "number" && value) {
            if (field.min && parseFloat(value) < parseFloat(field.min)) {
                errors.push(`Valeur minimum: ${field.min}`)
            }
            if (field.max && parseFloat(value) > parseFloat(field.max)) {
                errors.push(`Valeur maximum: ${field.max}`)
            }
        }

        this.showFieldError(field, errors)
    }

    clearFieldError(event) {
        const field = event.target
        this.showFieldError(field, [])
    }

    showFieldError(field, errors) {
        const formGroup = field.closest('.mb-3, .form-group, .field-group')
        let errorElement = formGroup?.querySelector('.field-error, .invalid-feedback')

        if (errors.length > 0) {
            field.classList.add('is-invalid', 'error')

            if (!errorElement) {
                errorElement = document.createElement('div')
                errorElement.className = 'field-error invalid-feedback'
                formGroup.appendChild(errorElement)
            }

            errorElement.textContent = errors[0]
            errorElement.style.display = 'block'
        } else {
            field.classList.remove('is-invalid', 'error')

            if (errorElement) {
                errorElement.style.display = 'none'
            }
        }
    }

    handleSuccess(result) {
        if (this.resetOnSuccessValue) {
            this.formTarget.reset()
        }

        this.dispatch("success", { detail: result })

        // Show success message
        this.dispatch("toast", {
            detail: {
                message: result.message || "Opération réussie",
                type: "success"
            }
        })

        // Handle redirects
        if (result.redirect) {
            setTimeout(() => {
                window.location.href = result.redirect
            }, 1000)
        }
    }

    handleErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            if (field === "_global") {
                this.showGlobalError(Array.isArray(messages) ? messages[0] : messages)
            } else {
                const fieldElement = this.formTarget.querySelector(`[name="${field}"]`)
                if (fieldElement) {
                    const errorMessages = Array.isArray(messages) ? messages : [messages]
                    this.showFieldError(fieldElement, errorMessages)
                }
            }
        })
    }

    showGlobalError(message) {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = message
            this.errorTarget.classList.remove('hidden', 'd-none')
            this.errorTarget.style.display = 'block'
        } else {
            // Create a global error element if it doesn't exist
            const errorDiv = document.createElement('div')
            errorDiv.className = 'alert alert-danger'
            errorDiv.textContent = message
            this.formTarget.insertBefore(errorDiv, this.formTarget.firstChild)
        }
    }

    clearErrors() {
        if (this.hasErrorTarget) {
            this.errorTarget.classList.add('hidden', 'd-none')
            this.errorTarget.style.display = 'none'
        }

        this.formTarget.querySelectorAll('.field-error, .invalid-feedback').forEach(error => {
            error.style.display = 'none'
        })

        this.formTarget.querySelectorAll('.error, .is-invalid').forEach(field => {
            field.classList.remove('error', 'is-invalid')
        })

        // Remove temporary error alerts
        this.formTarget.querySelectorAll('.alert-danger').forEach(alert => {
            if (!alert.hasAttribute('data-permanent')) {
                alert.remove()
            }
        })
    }

    setSubmitting(isSubmitting) {
        const submitButtons = this.formTarget.querySelectorAll('button[type="submit"], input[type="submit"]');

        submitButtons.forEach(button => {
            button.disabled = isSubmitting;

            const indicatorLabel = button.querySelector('.indicator-label');
            const spinner = button.querySelector('.indicator-progress');

            if (indicatorLabel && spinner) {
                if (isSubmitting) {
                    indicatorLabel.style.display = 'none';
                    spinner.style.display = 'inline-block';
                } else {
                    indicatorLabel.style.display = 'inline-block';
                    spinner.style.display = 'none';
                }
            } else {
                if (isSubmitting) {
                    button.textContent = "Loading...";
                } else {
                    button.textContent = button.dataset.originalText || "Submit";
                }
            }
        });
    }

    // Validation helpers
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    }

    isValidUrl(url) {
        try {
            new URL(url)
            return true
        } catch {
            return false
        }
    }

    // Public methods for external use
    reset() {
        this.formTarget.reset()
        this.clearErrors()
    }

    validateAll() {
        let isValid = true
        this.formTarget.querySelectorAll("input, textarea, select").forEach(field => {
            if (!field.name.startsWith('_') && field.type !== 'hidden') {
                this.validateField({ target: field })
                if (field.classList.contains('is-invalid')) {
                    isValid = false
                }
            }
        })
        return isValid
    }
}
