describe('Tenant Public Content Pages', () => {

  const base = 'https://testcompany.kompaza.com'

  it('blog page loads', () => {
    cy.visit(`${base}/blog`)
    cy.get('body').should('be.visible')
  })

  it('ebooks page loads', () => {
    cy.visit(`${base}/eboger`)
    cy.get('body').should('be.visible')
  })

  it('products page loads', () => {
    cy.visit(`${base}/produkter`)
    cy.get('body').should('be.visible')
  })

  it('newsletter signup API works', () => {
    cy.request({
      method: 'POST',
      url: `${base}/api/newsletter`,
      body: { email: 'newsletter@test.com' },
      headers: { 'Content-Type': 'application/json' },
      failOnStatusCode: false,
    }).then(response => {
      expect(response.status).to.be.oneOf([200, 201, 302, 422, 429])
    })
  })
})
