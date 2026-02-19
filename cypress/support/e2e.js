// Global config
Cypress.on('uncaught:exception', () => false)

// Session-cached login commands with validation to handle token rotation
Cypress.Commands.add('superadminLogin', () => {
  cy.session('superadmin', () => {
    cy.visit('https://superadmin.kompaza.com/login')
    cy.get('input[name="email"]').type('admin@kompaza.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    cy.url().should('not.include', '/login')
  }, {
    validate: () => {
      cy.request({
        url: 'https://superadmin.kompaza.com/',
        followRedirect: false,
        failOnStatusCode: false,
      }).its('status').should('eq', 200)
    }
  })
})

Cypress.Commands.add('tenantAdminLogin', (slug = 'testcompany') => {
  cy.session(`tenant-admin-${slug}`, () => {
    cy.visit(`https://${slug}.kompaza.com/login`)
    cy.get('input[name="email"]').type('admin@testcompany.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    cy.url().should('not.include', '/login')
  }, {
    validate: () => {
      cy.request({
        url: `https://${slug}.kompaza.com/admin`,
        followRedirect: false,
        failOnStatusCode: false,
      }).its('status').should('eq', 200)
    }
  })
})

Cypress.Commands.add('customerLogin', (slug = 'testcompany') => {
  cy.session(`customer-${slug}`, () => {
    cy.visit(`https://${slug}.kompaza.com/login`)
    cy.get('input[name="email"]').type('customer@testcompany.com')
    cy.get('input[name="password"]').type('password')
    cy.get('form').submit()
    cy.url().should('not.include', '/login')
  }, {
    validate: () => {
      cy.request({
        url: `https://${slug}.kompaza.com/konto`,
        followRedirect: false,
        failOnStatusCode: false,
      }).its('status').should('eq', 200)
    }
  })
})
