describe('Superadmin Plan Management', () => {

  beforeEach(() => {
    cy.superadminLogin()
  })

  it('shows plans list with seed data', () => {
    cy.visit('https://superadmin.kompaza.com/plans')
    cy.contains('Plans').should('be.visible')
    cy.contains('Starter').should('be.visible')
    cy.contains('Growth').should('be.visible')
    cy.contains('Enterprise').should('be.visible')
  })

  it('loads create plan form', () => {
    cy.visit('https://superadmin.kompaza.com/plans/create')
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="price_monthly_usd"]').should('be.visible')
  })

  it('can edit an existing plan', () => {
    cy.visit('https://superadmin.kompaza.com/plans')
    cy.contains('tr', 'Starter').find('a').contains('Edit').click()
    cy.get('input[name="name"]').should('have.value', 'Starter')
    cy.get('input[name="price_monthly_usd"]').should('not.have.value', '')
  })
})
