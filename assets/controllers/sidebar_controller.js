import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["sidebar", "toggler", "menuToggler"]
    static classes = ["collapsed", "menuActive"]
    static values = {
        collapsedHeight: String,
        fullHeight: String,
        breakpoint: Number
    }

    connect() {
        // Set default values
        this.collapsedHeightValue = this.collapsedHeightValue || "56px"
        this.fullHeightValue = this.fullHeightValue || "calc(100vh - 32px)"
        this.breakpointValue = this.breakpointValue || 1024

        // Initialize sidebar state
        this.initializeSidebar()

        // Add resize listener
        this.resizeListener = this.handleResize.bind(this)
        window.addEventListener('resize', this.resizeListener)

        // Add keyboard navigation
        this.keyboardListener = this.handleKeyboard.bind(this)
        document.addEventListener('keydown', this.keyboardListener)
    }

    disconnect() {
        // Clean up event listeners
        if (this.resizeListener) {
            window.removeEventListener('resize', this.resizeListener)
        }
        if (this.keyboardListener) {
            document.removeEventListener('keydown', this.keyboardListener)
        }
    }

    // Initialize sidebar based on screen size and saved state
    initializeSidebar() {
        const savedState = localStorage.getItem('sidebar-collapsed')
        const isCollapsed = savedState === 'true'

        if (window.innerWidth >= this.breakpointValue) {
            if (isCollapsed) {
                this.element.classList.add('collapsed')
            }
            this.element.style.height = this.fullHeightValue
        } else {
            this.element.classList.remove('collapsed')
            this.element.style.height = "auto"
        }
    }

    // Toggle sidebar collapse state (desktop)
    toggleCollapse() {
        const isCollapsed = this.element.classList.toggle('collapsed')

        // Save state to localStorage
        localStorage.setItem('sidebar-collapsed', isCollapsed.toString())

        // Dispatch custom event for other components
        this.dispatch('collapsed', {
            detail: { collapsed: isCollapsed }
        })

        // Update ARIA attributes
        this.updateAriaAttributes()
    }

    // Toggle mobile menu
    toggleMenu() {
        const isMenuActive = this.element.classList.toggle('menu-active')

        // Update sidebar height
        this.updateSidebarHeight(isMenuActive)

        // Update menu toggle icon
        this.updateMenuToggleIcon(isMenuActive)

        // Dispatch custom event
        this.dispatch('menuToggled', {
            detail: { active: isMenuActive }
        })
    }

    // Handle navigation clicks
    navigate(event) {
        const link = event.currentTarget
        const route = link.dataset.route

        // Remove active class from all links
        this.element.querySelectorAll('.nav-link').forEach(navLink => {
            navLink.classList.remove('active')
        })

        // Add active class to clicked link
        link.classList.add('active')

        // Close mobile menu if open
        if (window.innerWidth < this.breakpointValue) {
            this.element.classList.remove('menu-active')
            this.updateSidebarHeight(false)
            this.updateMenuToggleIcon(false)
        }

        // Dispatch navigation event
        this.dispatch('navigate', {
            detail: { route: route, element: link }
        })

        // For Live Components integration
        if (this.data.has('turbo-action')) {
            event.preventDefault()
            this.handleTurboNavigation(route)
        }
    }

    // Handle logout
    logout(event) {
        event.preventDefault()

        // Show confirmation dialog
        if (confirm('Are you sure you want to logout?')) {
            // Dispatch logout event
            this.dispatch('logout')

            // Clear saved state
            localStorage.removeItem('sidebar-collapsed')

            // Redirect to logout URL
            window.location.href = event.currentTarget.href
        }
    }

    // Handle window resize
    handleResize() {
        const isDesktop = window.innerWidth >= this.breakpointValue

        if (isDesktop) {
            // Desktop mode
            this.element.style.height = this.fullHeightValue
            this.element.classList.remove('menu-active')
        } else {
            // Mobile mode
            this.element.classList.remove('collapsed')
            this.element.style.height = "auto"
            this.updateSidebarHeight(this.element.classList.contains('menu-active'))
        }
    }

    // Handle keyboard navigation
    handleKeyboard(event) {
        // Toggle sidebar with Ctrl/Cmd + \
        if ((event.ctrlKey || event.metaKey) && event.key === '\\') {
            event.preventDefault()
            if (window.innerWidth >= this.breakpointValue) {
                this.toggleCollapse()
            } else {
                this.toggleMenu()
            }
        }

        // Close mobile menu with Escape
        if (event.key === 'Escape' && this.element.classList.contains('menu-active')) {
            this.toggleMenu()
        }
    }

    // Update sidebar height for mobile
    updateSidebarHeight(isMenuActive) {
        if (window.innerWidth < this.breakpointValue) {
            this.element.style.height = isMenuActive
                ? `${this.element.scrollHeight}px`
                : this.collapsedHeightValue
        }
    }

    // Update menu toggle icon
    updateMenuToggleIcon(isMenuActive) {
        const menuToggler = this.element.querySelector('.menu-toggler span')
        if (menuToggler) {
            menuToggler.textContent = isMenuActive ? 'close' : 'menu'
        }
    }

    // Update ARIA attributes for accessibility
    updateAriaAttributes() {
        const isCollapsed = this.element.classList.contains('collapsed')
        const toggler = this.element.querySelector('.sidebar-toggler')

        if (toggler) {
            toggler.setAttribute('aria-expanded', (!isCollapsed).toString())
        }

        this.element.setAttribute('aria-expanded', (!isCollapsed).toString())
    }

    // Handle Turbo/Live Component navigation
    handleTurboNavigation(route) {
        // Implement your Turbo Frame or Live Component navigation logic here
        // Example:
        // Turbo.visit(route)
        // or dispatch event for Live Component to handle

        this.dispatch('turboNavigate', {
            detail: { route: route }
        })
    }

    // Public API methods for external use
    collapse() {
        if (!this.element.classList.contains('collapsed')) {
            this.toggleCollapse()
        }
    }

    expand() {
        if (this.element.classList.contains('collapsed')) {
            this.toggleCollapse()
        }
    }

    closeMenu() {
        if (this.element.classList.contains('menu-active')) {
            this.toggleMenu()
        }
    }

    // Highlight active navigation item
    setActiveNavItem(route) {
        this.element.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active')
            if (link.dataset.route === route) {
                link.classList.add('active')
            }
        })
    }
}
