describe('Tenant Admin Courses', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // Course list
  it('shows courses list', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Courses').should('be.visible')
  })

  // Create course form loads
  it('loads create course form', () => {
    cy.visit(`${base}/admin/kurser/opret`)
    cy.get('input[name="title"]').should('be.visible')
    cy.get('input[name="slug"]').should('be.visible')
    // Pricing type is radio buttons (sr-only), check they exist
    cy.get('input[name="pricing_type"]').should('have.length', 3)
    cy.get('select[name="status"]').should('be.visible')
  })

  // Create a free course (pricing_type defaults to 'free')
  it('creates a free course', () => {
    cy.visit(`${base}/admin/kurser/opret`)
    cy.get('input[name="title"]').type('Cypress Test Course')
    cy.get('input[name="slug"]').clear().type('cypress-test-course')
    cy.get('input[name="subtitle"]').type('A test course created by Cypress')
    // pricing_type defaults to 'free', no need to change
    cy.get('select[name="status"]').select('published')
    cy.get('form').submit()
    cy.url().should('include', '/admin/kurser')
  })

  // Course appears in list
  it('course appears in list', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course').should('be.visible')
  })

  // Edit course (course builder)
  it('loads course builder/edit page', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course')
      .closest('tr')
      .find('a:contains("Edit")')
      .click()
    cy.get('input[name="title"]').should('have.value', 'Cypress Test Course')
    cy.contains('Curriculum').should('be.visible')
  })

  // Add a module via the Add Module Alpine.js form
  it('adds a module to the course', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course')
      .closest('tr')
      .find('a:contains("Edit")')
      .click()
    // Click "Add Module" to toggle the form
    cy.contains('button', 'Add Module').click()
    // Fill module title (in the module form, not the course form)
    cy.get('form[action="/admin/kurser/modul/gem"] input[name="title"]').type('Module 1: Introduction')
    cy.get('form[action="/admin/kurser/modul/gem"]').submit()
    // Should redirect back to edit page with module visible
    cy.contains('Module 1: Introduction').should('be.visible')
  })

  // Navigate to create lesson form
  it('loads create lesson form', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course')
      .closest('tr')
      .find('a:contains("Edit")')
      .click()
    // Click "Add Lesson" link in the first module
    cy.get('a:contains("Add Lesson")').first().click()
    cy.get('input[name="title"]').should('be.visible')
    cy.get('input[name="lesson_type"]').should('exist')
  })

  // Create a text lesson
  it('creates a text lesson', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course')
      .closest('tr')
      .find('a:contains("Edit")')
      .click()
    cy.get('a:contains("Add Lesson")').first().click()
    cy.get('input[name="title"]').type('Lesson 1: Welcome')
    // Select text lesson type (sr-only radio, click parent label)
    cy.get('input[name="lesson_type"][value="text"]').check({ force: true })
    // Enable preview
    cy.get('input[name="is_preview"]').check({ force: true })
    cy.get('form').submit()
    cy.url().should('include', '/admin/kurser')
  })

  // Enrollments page
  it('shows enrollments page', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Test Course')
      .closest('tr')
      .find('a:contains("Students")')
      .click()
    cy.url().should('include', '/admin/kurser/tilmeldinger')
  })

  // Create a paid course
  it('creates a paid course', () => {
    cy.visit(`${base}/admin/kurser/opret`)
    cy.get('input[name="title"]').type('Cypress Paid Course')
    cy.get('input[name="slug"]').clear().type('cypress-paid-course')
    // Select one_time pricing via radio (sr-only, force check)
    cy.get('input[name="pricing_type"][value="one_time"]').check({ force: true })
    cy.get('input[name="price_dkk"]').clear().type('299')
    cy.get('select[name="status"]').select('published')
    cy.get('form').submit()
    cy.url().should('include', '/admin/kurser')
  })

  it('paid course appears in list', () => {
    cy.visit(`${base}/admin/kurser`)
    cy.contains('Cypress Paid Course').should('be.visible')
  })
})
