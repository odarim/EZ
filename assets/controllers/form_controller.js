// assets/controllers/form_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["form", "submit", "error"]
    static values = {
        async: { type: Boolean, default: false },
        resetOnSuccess: { type: Boolean, default: false },
        validateOnBlur: { type: Boolean, default: true }
    }

    connect() {
        if (this.validateOnBlurValue) {
            this.setupValidation()
        }
    }

    setupValidation() {
        this.formTarget.querySelectorAll("input, textarea, select").forEach(field => {
            field.addEventListener("blur", this.validateField.bind(this))
            field.addEventListener("input", this.clearFieldError.bind(this))
        })
    }

    async submit(event) {
        event.preventDefault()

        if (!this.asyncValue) {
            this.formTarget.submit()
            return
        }

        this.setSubmitting(true)
        this.clearErrors()

        try {
            const formData = new FormData(this.formTarget)
            const response = await fetch(this.formTarget.action, {
                method: this.formTarget.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })

            const result = await response.json()

            if (response.ok) {
                this.handleSuccess(result)
            } else {
                this.handleErrors(result.errors || {})
            }
        } catch (error) {
            this.handleErrors({ _global: ["Une erreur s'est produite"] })
        } finally {
            this.setSubmitting(false)
        }
    }

    validateField(event) {
        const field = event.target
        const value = field.value.trim()
        const errors = []

        // Validation requise
        if (field.required && !value) {
            errors.push("Ce champ est requis")
        }

        // Validation email
        if (field.type === "email" && value && !this.isValidEmail(value)) {
            errors.push("Email invalide")
        }

        // Validation longueur minimum
        if (field.minLength && value.length < field.minLength) {
            errors.push(`Minimum ${field.minLength} caractères`)
        }

        this.showFieldError(field, errors)
    }

    clearFieldError(event) {
        const field = event.target
        this.showFieldError(field, [])
    }

    showFieldError(field, errors) {
        const errorElement = field.parentNode.querySelector(".field-error")

        if (errors.length > 0) {
            field.classList.add("error")
            if (errorElement) {
                errorElement.textContent = errors[0]
                errorElement.classList.remove("hidden")
            }
        } else {
            field.classList.remove("error")
            if (errorElement) {
                errorElement.classList.add("hidden")
            }
        }
    }

    handleSuccess(result) {
        if (this.resetOnSuccessValue) {
            this.formTarget.reset()
        }

        this.dispatch("success", { detail: result })

        // Afficher un message de succès
        this.dispatch("toast", {
            detail: { message: result.message || "Opération réussie", type: "success" }
        })
    }

    handleErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            if (field === "_global") {
                this.showGlobalError(messages[0])
            } else {
                const fieldElement = this.formTarget.querySelector(`[name="${field}"]`)
                if (fieldElement) {
                    this.showFieldError(fieldElement, messages)
                }
            }
        })
    }

    showGlobalError(message) {
        if (this.hasErrorTarget) {
            this.errorTarget.textContent = message
            this.errorTarget.classList.remove("hidden")
        }
    }

    clearErrors() {
        if (this.hasErrorTarget) {
            this.errorTarget.classList.add("hidden")
        }

        this.formTarget.querySelectorAll(".field-error").forEach(error => {
            error.classList.add("hidden")
        })

        this.formTarget.querySelectorAll(".error").forEach(field => {
            field.classList.remove("error")
        })
    }

    setSubmitting(isSubmitting) {
        if (this.hasSubmitTarget) {
            this.submitTarget.disabled = isSubmitting
            this.submitTarget.textContent = isSubmitting ? "Envoi..." : this.submitTarget.dataset.originalText || "Envoyer"
        }
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    }
}
