// assets/controllers/dropdown_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["toggle", "menu", "search", "options"]
    static values = {
        open: { type: Boolean, default: false },
        searchable: { type: Boolean, default: false },
        multiple: { type: Boolean, default: false },
        placeholder: { type: String, default: "Sélectionner..." }
    }

    connect() {
        this.boundHandleClickOutside = this.handleClickOutside.bind(this)
        this.selectedValues = new Set()
        this.originalOptions = []

        if (this.hasOptionsTarget) {
            this.saveOriginalOptions()
        }
    }

    disconnect() {
        document.removeEventListener("click", this.boundHandleClickOutside)
    }

    toggle(event) {
        event.preventDefault()
        this.openValue = !this.openValue
    }

    openValueChanged() {
        if (this.openValue) {
            this.show()
        } else {
            this.hide()
        }
    }

    show() {
        this.menuTarget.classList.remove("hidden")
        document.addEventListener("click", this.boundHandleClickOutside)

        if (this.searchableValue && this.hasSearchTarget) {
            this.searchTarget.focus()
        }

        this.dispatch("opened")
    }

    hide() {
        this.menuTarget.classList.add("hidden")
        document.removeEventListener("click", this.boundHandleClickOutside)
        this.dispatch("closed")
    }

    select(event) {
        event.preventDefault()
        const option = event.currentTarget
        const value = option.dataset.value
        const text = option.textContent.trim()

        if (this.multipleValue) {
            this.toggleMultipleSelection(value, text, option)
        } else {
            this.selectSingle(value, text, option)
            this.openValue = false
        }

        this.dispatch("changed", { detail: { value, text, selected: this.getSelectedValues() } })
    }

    selectSingle(value, text, option) {
        // Désélectionner toutes les options
        this.element.querySelectorAll("[data-value]").forEach(opt => {
            opt.classList.remove("selected")
        })

        // Sélectionner la nouvelle option
        option.classList.add("selected")
        this.selectedValues.clear()
        this.selectedValues.add(value)

        // Mettre à jour l'affichage
        this.toggleTarget.textContent = text
    }

    toggleMultipleSelection(value, text, option) {
        if (this.selectedValues.has(value)) {
            this.selectedValues.delete(value)
            option.classList.remove("selected")
        } else {
            this.selectedValues.add(value)
            option.classList.add("selected")
        }

        this.updateMultipleDisplay()
    }

    updateMultipleDisplay() {
        const selectedCount = this.selectedValues.size

        if (selectedCount === 0) {
            this.toggleTarget.textContent = this.placeholderValue
        } else if (selectedCount === 1) {
            const selectedOption = this.element.querySelector(".selected")
            this.toggleTarget.textContent = selectedOption.textContent.trim()
        } else {
            this.toggleTarget.textContent = `${selectedCount} éléments sélectionnés`
        }
    }

    search(event) {
        const query = event.target.value.toLowerCase()

        this.element.querySelectorAll("[data-value]").forEach(option => {
            const text = option.textContent.toLowerCase()
            const matches = text.includes(query)
            option.style.display = matches ? "block" : "none"
        })
    }

    handleClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.openValue = false
        }
    }

    saveOriginalOptions() {
        this.originalOptions = Array.from(this.element.querySelectorAll("[data-value]")).map(option => ({
            value: option.dataset.value,
            text: option.textContent.trim(),
            element: option.cloneNode(true)
        }))
    }

    getSelectedValues() {
        return Array.from(this.selectedValues)
    }

    clear() {
        this.selectedValues.clear()
        this.element.querySelectorAll(".selected").forEach(option => {
            option.classList.remove("selected")
        })

        if (this.multipleValue) {
            this.updateMultipleDisplay()
        } else {
            this.toggleTarget.textContent = this.placeholderValue
        }

        this.dispatch("cleared")
    }
}
