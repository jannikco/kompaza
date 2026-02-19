describe('Tenant Customer Authentication', () => {

  const base = 'https://testcompany.kompaza.com'

  it('can login as customer', () => {
    cy.visit(`${base}/login`)
    cy.get('input[name="email"]').type('customer@testcompany.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    cy.url().should('not.include', '/login')
  })

  it('can access account page when logged in', () => {
    cy.customerLogin()
    cy.visit(`${base}/konto`)
    cy.get('body').should('be.visible')
  })

  it('account orders page loads', () => {
    cy.customerLogin()
    cy.visit(`${base}/konto/ordrer`)
    cy.get('body').should('be.visible')
  })

  it('account downloads page loads', () => {
    cy.customerLogin()
    cy.visit(`${base}/konto/downloads`)
    cy.get('body').should('be.visible')
  })

  it('account settings page loads', () => {
    cy.customerLogin()
    cy.visit(`${base}/konto/indstillinger`)
    cy.get('body').should('be.visible')
  })
})
