describe('Superadmin Settings', () => {

  beforeEach(() => {
    cy.superadminLogin()
  })

  it('shows settings page', () => {
    cy.visit('https://superadmin.kompaza.com/settings')
    cy.contains('Platform Settings').should('be.visible')
    cy.get('input[name="platform_name"]').should('be.visible')
    cy.get('input[name="support_email"]').should('be.visible')
    cy.get('input[name="default_trial_days"]').should('be.visible')
  })

  it('can update settings', () => {
    cy.visit('https://superadmin.kompaza.com/settings')
    cy.get('input[name="platform_name"]').clear().type('Kompaza')
    cy.get('input[name="support_email"]').clear().type('support@kompaza.com')
    cy.get('form').submit()
    cy.contains('success', { matchCase: false }).should('be.visible')
  })
})
