// assets/controllers/modal_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["modal", "backdrop", "content"]
    static values = {
        url: String,
        size: { type: String, default: "md" },
        backdrop: { type: Boolean, default: true },
        keyboard: { type: Boolean, default: true }
    }

    connect() {
        this.boundHandleKeydown = this.handleKeydown.bind(this)
    }

    disconnect() {
        document.removeEventListener("keydown", this.boundHandleKeydown)
    }

    async open(event) {
        event?.preventDefault()

        if (this.urlValue) {
            await this.loadContent()
        }

        this.showModal()
    }

    close(event) {
        if (event?.target === this.backdropTarget || event?.type === "click") {
            this.hideModal()
        }
    }

    async loadContent() {
        try {
            const response = await fetch(this.urlValue)
            const html = await response.text()
            this.contentTarget.innerHTML = html
        } catch (error) {
            this.contentTarget.innerHTML = `<div class="alert alert-error">Erreur de chargement</div>`
        }
    }

    showModal() {
        this.modalTarget.classList.remove("hidden")
        this.modalTarget.classList.add(`modal-${this.sizeValue}`)
        document.body.classList.add("overflow-hidden")

        if (this.keyboardValue) {
            document.addEventListener("keydown", this.boundHandleKeydown)
        }

        // Animation d'entrée
        requestAnimationFrame(() => {
            this.modalTarget.classList.add("modal-show")
        })
    }

    hideModal() {
        this.modalTarget.classList.remove("modal-show")

        setTimeout(() => {
            this.modalTarget.classList.add("hidden")
            document.body.classList.remove("overflow-hidden")
        }, 300)

        document.removeEventListener("keydown", this.boundHandleKeydown)
        this.dispatch("closed")
    }

    handleKeydown(event) {
        if (event.key === "Escape") {
            this.hideModal()
        }
    }
}
