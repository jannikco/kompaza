describe('Marketing Site (kompaza.com)', () => {

  it('loads homepage', () => {
    cy.visit('https://kompaza.com/')
    cy.get('body').should('be.visible')
    cy.contains('Kompaza').should('be.visible')
  })

  it('has navigation links', () => {
    cy.visit('https://kompaza.com/')
    cy.contains('a', 'Pricing').should('be.visible')
    cy.contains('a', 'Log In').should('be.visible')
    cy.contains('a', 'Get Started Free').should('be.visible')
  })

  it('loads pricing page', () => {
    cy.visit('https://kompaza.com/pricing')
    cy.get('body').should('be.visible')
    cy.contains('Starter').should('be.visible')
    cy.contains('Growth').should('be.visible')
    cy.contains('Enterprise').should('be.visible')
  })

  it('loads register page', () => {
    cy.visit('https://kompaza.com/register')
    cy.get('input[name="company_name"]').should('be.visible')
    cy.get('input[name="email"]').should('be.visible')
    cy.get('input[name="password"]').should('be.visible')
    cy.get('form').should('be.visible')
  })

  it('loads login page (workspace finder)', () => {
    cy.visit('https://kompaza.com/login')
    cy.contains('Welcome back').should('be.visible')
    // Workspace finder form
    cy.get('body').should('be.visible')
  })

  it('register form validates required fields', () => {
    cy.visit('https://kompaza.com/register')
    cy.get('form').submit()
    cy.url().should('include', '/register')
  })
})
