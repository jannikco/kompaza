describe('Tenant Admin Content Management', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // Lead Magnets
  it('shows lead magnets list', () => {
    cy.visit(`${base}/admin/lead-magnets`)
    cy.contains('Lead Magnets').should('be.visible')
  })

  it('loads create lead magnet form', () => {
    cy.visit(`${base}/admin/lead-magnets/opret`)
    // AI wizard step 1 is shown first; skip to manual form
    cy.contains('Skip AI').click()
    cy.get('input[name="title"]').should('be.visible')
    cy.get('input[name="slug"]').should('be.visible')
  })

  it('creates a lead magnet', () => {
    cy.visit(`${base}/admin/lead-magnets/opret`)
    // Skip AI wizard to get to the manual form
    cy.contains('Skip AI').click()
    cy.get('input[name="title"]').type('Test Lead Magnet')
    cy.get('input[name="slug"]').clear().type('test-lead-magnet')
    cy.get('input[name="hero_headline"]').type('Download Our Free Guide')
    cy.get('form').submit()
    cy.url().should('include', '/admin/lead-magnets')
  })

  // Articles
  it('shows articles list', () => {
    cy.visit(`${base}/admin/artikler`)
    cy.contains('Articles').should('be.visible')
  })

  it('loads create article form', () => {
    cy.visit(`${base}/admin/artikler/opret`)
    cy.get('input[name="title"]').should('be.visible')
  })

  it('creates an article', () => {
    cy.visit(`${base}/admin/artikler/opret`)
    cy.get('input[name="title"]').type('Test Article')
    cy.get('input[name="slug"]').clear().type('test-article')
    // Select published status
    cy.get('select[name="status"]').select('published')
    cy.get('form').submit()
    cy.url().should('include', '/admin/artikler')
  })

  // Ebooks
  it('shows ebooks list', () => {
    cy.visit(`${base}/admin/eboger`)
    cy.contains('Ebooks').should('be.visible')
  })

  it('loads create ebook form', () => {
    cy.visit(`${base}/admin/eboger/opret`)
    cy.get('input[name="title"]').should('be.visible')
  })

  it('creates an ebook', () => {
    cy.visit(`${base}/admin/eboger/opret`)
    cy.get('input[name="title"]').type('Test Ebook')
    cy.get('input[name="slug"]').clear().type('test-ebook')
    cy.get('input[name="price_dkk"]').clear().type('99')
    cy.get('form').submit()
    cy.url().should('include', '/admin/eboger')
  })
})
