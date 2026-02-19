describe('Tenant Admin Authentication', () => {

  const base = 'https://testcompany.kompaza.com'

  it('admin pages redirect to login when not authenticated', () => {
    cy.visit(`${base}/admin`)
    cy.url().should('include', '/login')
  })

  it('can login as tenant admin', () => {
    cy.visit(`${base}/login`)
    cy.get('input[name="email"]').type('admin@testcompany.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    cy.url().should('not.include', '/login')
  })

  it('can access admin dashboard', () => {
    cy.tenantAdminLogin()
    cy.visit(`${base}/admin`)
    cy.contains('Dashboard').should('be.visible')
  })

  it('admin sidebar layout is correct (no gap)', () => {
    cy.tenantAdminLogin()
    cy.visit(`${base}/admin`)
    cy.get('aside').should('have.css', 'position', 'fixed')
  })
})
