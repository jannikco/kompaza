describe('Tenant Admin Commerce', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // Products
  it('shows products list', () => {
    cy.visit(`${base}/admin/produkter`)
    cy.contains('Products').should('be.visible')
  })

  it('loads create product form', () => {
    cy.visit(`${base}/admin/produkter/opret`)
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="price_dkk"]').should('be.visible')
  })

  it('creates a product', () => {
    cy.visit(`${base}/admin/produkter/opret`)
    cy.get('input[name="name"]').type('Test Product')
    cy.get('input[name="slug"]').clear().type('test-product')
    cy.get('input[name="price_dkk"]').clear().type('199.00')
    cy.get('select[name="status"]').select('published')
    cy.get('form').submit()
    cy.url().should('include', '/admin/produkter')
  })

  it('product appears in list', () => {
    cy.visit(`${base}/admin/produkter`)
    cy.contains('Test Product').should('be.visible')
  })

  // Orders
  it('shows orders list', () => {
    cy.visit(`${base}/admin/ordrer`)
    cy.contains('Orders').should('be.visible')
    cy.get('table').should('be.visible')
  })

  // Customers
  it('shows customers list', () => {
    cy.visit(`${base}/admin/kunder`)
    cy.contains('Customers').should('be.visible')
  })

  it('loads create customer form', () => {
    cy.visit(`${base}/admin/kunder/opret`)
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="email"]').should('be.visible')
  })

  it('creates a customer', () => {
    cy.visit(`${base}/admin/kunder/opret`)
    cy.get('input[name="name"]').type('Manual Customer')
    cy.get('input[name="email"]').type('manual@testcompany.com')
    cy.get('input[name="password"]').type('Manual123!')
    cy.get('form').submit()
    cy.url().should('include', '/admin/kunder')
  })

  it('customer appears in list', () => {
    cy.visit(`${base}/admin/kunder`)
    cy.contains('Manual Customer').should('be.visible')
  })
})
