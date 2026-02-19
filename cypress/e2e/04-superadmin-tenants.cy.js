describe('Superadmin Tenant Management', () => {

  beforeEach(() => {
    cy.superadminLogin()
  })

  it('shows tenants list with seeded tenant', () => {
    cy.visit('https://superadmin.kompaza.com/tenants')
    cy.contains('Tenants').should('be.visible')
    cy.contains('Create Tenant').should('be.visible')
    cy.get('table').should('be.visible')
    cy.contains('Test Company').should('be.visible')
    cy.contains('testcompany').should('be.visible')
  })

  it('loads create tenant form', () => {
    cy.visit('https://superadmin.kompaza.com/tenants/create')
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="slug"]').should('be.visible')
    cy.get('input[name="email"]').should('be.visible')
    cy.get('select[name="status"]').should('be.visible')
    cy.get('select[name="plan_id"]').should('be.visible')
  })

  it('can edit the test tenant', () => {
    cy.visit('https://superadmin.kompaza.com/tenants')
    cy.contains('tr', 'Test Company').find('a').contains('Edit').click()
    cy.get('input[name="name"]').should('have.value', 'Test Company')
  })

  it('search filters tenants', () => {
    cy.visit('https://superadmin.kompaza.com/tenants?search=testcompany')
    cy.contains('Test Company').should('be.visible')
  })
})
