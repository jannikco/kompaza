describe('Superadmin Dashboard', () => {

  beforeEach(() => {
    cy.superadminLogin()
  })

  it('shows dashboard with stats', () => {
    cy.visit('https://superadmin.kompaza.com/')
    cy.contains('Total Tenants').should('be.visible')
    cy.contains('Active Tenants').should('be.visible')
    cy.contains('Trial Tenants').should('be.visible')
    cy.contains('Total Users').should('be.visible')
  })

  it('sidebar layout is correct (no gap)', () => {
    cy.visit('https://superadmin.kompaza.com/')
    cy.get('aside').should('have.css', 'position', 'fixed')
    cy.get('header').then($header => {
      const rect = $header[0].getBoundingClientRect()
      expect(rect.top).to.be.lessThan(5)
    })
  })

  it('sidebar navigation works', () => {
    cy.visit('https://superadmin.kompaza.com/')
    cy.contains('a', 'Tenants').click()
    cy.url().should('include', '/tenants')

    cy.contains('a', 'Plans').click()
    cy.url().should('include', '/plans')

    cy.contains('a', 'Settings').click()
    cy.url().should('include', '/settings')

    cy.contains('a', 'Dashboard').click()
    cy.url().should('eq', 'https://superadmin.kompaza.com/')
  })
})
