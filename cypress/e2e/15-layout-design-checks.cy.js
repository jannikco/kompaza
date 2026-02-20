describe('Layout & Design Verification', () => {

  it('superadmin: sidebar is fixed, no content gap', () => {
    cy.superadminLogin()
    cy.visit('https://superadmin.kompaza.com/')

    // Sidebar should be fixed position
    cy.get('aside').should('have.css', 'position', 'fixed')

    // Header should be at the top of the viewport area (not pushed down)
    cy.get('header').then($header => {
      const rect = $header[0].getBoundingClientRect()
      expect(rect.top).to.be.lessThan(5) // Should be at top
    })

    // Main content should be immediately after header
    cy.get('main').then($main => {
      const rect = $main[0].getBoundingClientRect()
      expect(rect.top).to.be.lessThan(100) // Should be right after 64px header
    })
  })

  it('superadmin: all pages render without 500 errors', () => {
    cy.superadminLogin()
    const pages = ['/', '/tenants', '/plans', '/settings']
    pages.forEach(page => {
      cy.visit(`https://superadmin.kompaza.com${page}`)
      cy.get('body').should('be.visible')
      cy.get('body').should('not.contain', 'Fatal error')
      cy.get('body').should('not.contain', 'Warning:')
    })
  })

  it('tenant admin: sidebar is fixed, no content gap', () => {
    cy.tenantAdminLogin()
    cy.visit('https://testcompany.kompaza.com/admin')

    cy.get('aside').should('have.css', 'position', 'fixed')

    cy.get('header').then($header => {
      const rect = $header[0].getBoundingClientRect()
      expect(rect.top).to.be.lessThan(5)
    })
  })

  it('tenant admin: all pages render without 500 errors', () => {
    cy.tenantAdminLogin()
    const base = 'https://testcompany.kompaza.com'
    const pages = [
      '/admin',
      '/admin/lead-magnets',
      '/admin/artikler',
      '/admin/eboger',
      '/admin/produkter',
      '/admin/ordrer',
      '/admin/kunder',
      '/admin/tilmeldinger',
      '/admin/brugere',
      '/admin/indstillinger',
      '/admin/connectpilot',
      '/admin/connectpilot/kampagner',
      '/admin/connectpilot/leads',
    ]
    pages.forEach(page => {
      cy.visit(`${base}${page}`)
      cy.get('body').should('be.visible')
      cy.get('body').should('not.contain', 'Fatal error')
      cy.get('body').should('not.contain', 'Warning:')
    })
  })

  it('marketing site: responsive meta tag present', () => {
    cy.visit('https://kompaza.com/')
    cy.get('meta[name="viewport"]').should('exist')
  })

  it('marketing site: no PHP errors visible', () => {
    cy.visit('https://kompaza.com/')
    cy.get('body').should('not.contain', 'Fatal error')
    cy.get('body').should('not.contain', 'Parse error')
    cy.get('body').should('not.contain', 'Warning:')
  })
})
