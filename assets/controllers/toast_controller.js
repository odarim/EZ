// assets/controllers/toast_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["container"]
    static values = {
        duration: { type: Number, default: 5000 },
        position: { type: String, default: "top-right" }
    }

    connect() {
        this.setupContainer()
    }

    setupContainer() {
        if (!this.hasContainerTarget) {
            const container = document.createElement("div")
            container.className = `toast-container toast-${this.positionValue}`
            container.dataset.toastTarget = "container"
            document.body.appendChild(container)
        }
    }

    show(event) {
        const { message, type = "info", duration } = event.detail || event.params || {}
        this.createToast(message, type, duration || this.durationValue)
    }

    createToast(message, type, duration) {
        const toast = document.createElement("div")
        toast.className = `toast toast-${type} toast-enter`
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${this.getIcon(type)}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" data-action="click->toast#closeToast">×</button>
            </div>
        `

        this.containerTarget.appendChild(toast)

        // Animation d'entrée
        requestAnimationFrame(() => {
            toast.classList.remove("toast-enter")
            toast.classList.add("toast-show")
        })

        // Auto-suppression
        if (duration > 0) {
            setTimeout(() => {
                this.removeToast(toast)
            }, duration)
        }
    }

    closeToast(event) {
        const toast = event.target.closest(".toast")
        this.removeToast(toast)
    }

    removeToast(toast) {
        toast.classList.add("toast-exit")
        setTimeout(() => {
            toast.remove()
        }, 300)
    }

    getIcon(type) {
        const icons = {
            success: "✓",
            error: "✗",
            warning: "⚠",
            info: "ℹ"
        }
        return icons[type] || icons.info
    }
}
