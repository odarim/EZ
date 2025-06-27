// assets/controllers/datatable_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["table", "search", "pagination", "loading"]
    static values = {
        url: String,
        page: { type: Number, default: 1 },
        limit: { type: Number, default: 10 },
        sort: String,
        order: { type: String, default: "asc" },
        searchDelay: { type: Number, default: 500 }
    }

    connect() {
        this.searchTimeout = null
        this.loadData()
    }

    async loadData() {
        this.showLoading()

        const params = new URLSearchParams({
            page: this.pageValue,
            limit: this.limitValue,
            ...(this.sortValue && { sort: this.sortValue, order: this.orderValue }),
            ...(this.searchValue && { search: this.searchValue })
        })

        try {
            const response = await fetch(`${this.urlValue}?${params}`)
            const html = await response.text()
            this.tableTarget.innerHTML = html

            this.dispatch("loaded", { detail: { page: this.pageValue } })
        } catch (error) {
            this.tableTarget.innerHTML = `<tr><td colspan="100%" class="text-center text-red-500">Erreur de chargement</td></tr>`
        } finally {
            this.hideLoading()
        }
    }

    search(event) {
        clearTimeout(this.searchTimeout)
        this.searchValue = event.target.value

        this.searchTimeout = setTimeout(() => {
            this.pageValue = 1
            this.loadData()
        }, this.searchDelayValue)
    }

    sort(event) {
        const column = event.currentTarget.dataset.column

        if (this.sortValue === column) {
            this.orderValue = this.orderValue === "asc" ? "desc" : "asc"
        } else {
            this.sortValue = column
            this.orderValue = "asc"
        }

        this.pageValue = 1
        this.loadData()
        this.updateSortIndicators()
    }

    changePage(event) {
        event.preventDefault()
        const page = parseInt(event.currentTarget.dataset.page)

        if (page !== this.pageValue) {
            this.pageValue = page
            this.loadData()
        }
    }

    updateSortIndicators() {
        // Mise à jour des indicateurs de tri dans le DOM
        this.element.querySelectorAll("[data-column]").forEach(header => {
            header.classList.remove("sort-asc", "sort-desc")
            if (header.dataset.column === this.sortValue) {
                header.classList.add(`sort-${this.orderValue}`)
            }
        })
    }

    showLoading() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.remove("hidden")
        }
    }

    hideLoading() {
        if (this.hasLoadingTarget) {
            this.loadingTarget.classList.add("hidden")
        }
    }
}
