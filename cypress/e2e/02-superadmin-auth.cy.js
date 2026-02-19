describe('Superadmin Authentication', () => {

  it('redirects to login when not authenticated', () => {
    cy.visit('https://superadmin.kompaza.com/')
    cy.url().should('include', '/login')
  })

  it('shows login form', () => {
    cy.visit('https://superadmin.kompaza.com/login')
    cy.get('input[name="email"]').should('be.visible')
    cy.get('input[name="password"]').should('be.visible')
    cy.get('form').should('be.visible')
  })

  it('rejects invalid credentials', () => {
    cy.visit('https://superadmin.kompaza.com/login')
    cy.get('input[name="email"]').type('wrong@example.com')
    cy.get('input[name="password"]').type('wrongpassword')
    cy.get('form').submit()
    cy.url().should('include', '/login')
  })

  it('logs in with valid credentials', () => {
    cy.superadminLogin()
    cy.visit('https://superadmin.kompaza.com/')
    cy.contains('Dashboard').should('be.visible')
  })

  it('logs out', () => {
    cy.superadminLogin()
    cy.visit('https://superadmin.kompaza.com/logout')
    cy.visit('https://superadmin.kompaza.com/')
    cy.url().should('include', '/login')
  })
})
