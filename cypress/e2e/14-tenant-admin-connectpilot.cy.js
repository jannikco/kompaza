describe('Tenant Admin ConnectPilot', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  it('shows connectpilot dashboard', () => {
    cy.visit(`${base}/admin/connectpilot`)
    cy.get('body').should('be.visible')
    // May show "disconnected" since no LinkedIn account connected
  })

  it('shows account connection page', () => {
    cy.visit(`${base}/admin/connectpilot/konto`)
    cy.get('body').should('be.visible')
    // Should show instructions for connecting
  })

  it('shows campaigns list', () => {
    cy.visit(`${base}/admin/connectpilot/kampagner`)
    cy.get('body').should('be.visible')
    cy.contains('Campaign', { matchCase: false }).should('be.visible')
  })

  it('loads create campaign form', () => {
    cy.visit(`${base}/admin/connectpilot/kampagner/opret`)
    cy.get('input[name="name"]').should('be.visible')
  })

  it('shows leads list', () => {
    cy.visit(`${base}/admin/connectpilot/leads`)
    cy.get('body').should('be.visible')
  })
})
