describe('Tenant Admin System Pages', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // Email Signups
  it('shows email signups list', () => {
    cy.visit(`${base}/admin/tilmeldinger`)
    cy.get('main').contains('Email Signups').should('be.visible')
  })

  // Users
  it('shows users list', () => {
    cy.visit(`${base}/admin/brugere`)
    cy.get('main').contains('Users').should('be.visible')
  })

  it('loads create user form', () => {
    cy.visit(`${base}/admin/brugere/opret`)
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="email"]').should('be.visible')
  })

  // Settings
  it('shows settings page', () => {
    cy.visit(`${base}/admin/indstillinger`)
    cy.get('main').contains('Settings').should('be.visible')
  })

  // All sidebar navigation links
  it('all sidebar nav links load without errors', () => {
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
    ]

    pages.forEach(page => {
      cy.visit(`${base}${page}`)
      cy.get('body').should('be.visible')
      cy.get('aside').should('be.visible')
      cy.get('header').should('be.visible')
      cy.get('main').should('be.visible')
    })
  })
})
