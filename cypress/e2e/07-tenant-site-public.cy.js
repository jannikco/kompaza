describe('Tenant Public Site (testcompany.kompaza.com)', () => {

  const base = 'https://testcompany.kompaza.com'

  it('loads tenant homepage', () => {
    cy.visit(base)
    cy.get('body').should('be.visible')
  })

  it('loads blog page', () => {
    cy.visit(`${base}/blog`)
    cy.get('body').should('be.visible')
  })

  it('loads ebooks page', () => {
    cy.visit(`${base}/eboger`)
    cy.get('body').should('be.visible')
  })

  it('loads products page', () => {
    cy.visit(`${base}/produkter`)
    cy.get('body').should('be.visible')
  })

  it('loads cart page', () => {
    cy.visit(`${base}/kurv`)
    cy.get('body').should('be.visible')
  })

  it('loads login page', () => {
    cy.visit(`${base}/login`)
    cy.get('input[name="email"]').should('be.visible')
    cy.get('input[name="password"]').should('be.visible')
  })

  it('loads register page', () => {
    cy.visit(`${base}/registrer`)
    cy.get('input[name="name"]').should('be.visible')
    cy.get('input[name="email"]').should('be.visible')
    cy.get('input[name="password"]').should('be.visible')
  })

  it('loads privacy policy page', () => {
    cy.visit(`${base}/privatlivspolitik`)
    cy.get('body').should('be.visible')
  })
})
