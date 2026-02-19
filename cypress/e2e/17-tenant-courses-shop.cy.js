describe('Tenant Course Shop Pages', () => {

  const base = 'https://testcompany.kompaza.com'

  // Public pages (no login needed)
  it('courses catalog page loads', () => {
    cy.visit(`${base}/courses`)
    cy.contains('Our Courses').should('be.visible')
  })

  it('free course appears in catalog', () => {
    cy.visit(`${base}/courses`)
    cy.contains('Cypress Test Course').should('be.visible')
  })

  it('course detail page loads', () => {
    cy.visit(`${base}/course/cypress-test-course`)
    cy.contains('Cypress Test Course').should('be.visible')
    cy.contains('A test course created by Cypress').should('be.visible')
  })

  it('course detail shows curriculum section', () => {
    cy.visit(`${base}/course/cypress-test-course`)
    cy.contains('Curriculum').should('be.visible')
    cy.contains('Module 1: Introduction').should('be.visible')
  })

  it('free course shows enroll for free button', () => {
    cy.visit(`${base}/course/cypress-test-course`)
    cy.contains('Enroll for Free').should('be.visible')
  })

  it('paid course detail page loads', () => {
    cy.visit(`${base}/course/cypress-paid-course`)
    cy.contains('Cypress Paid Course').should('be.visible')
    cy.contains('299').should('be.visible')
  })
})

describe('Customer Course Enrollment', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.customerLogin()
  })

  it('can enroll in free course', () => {
    cy.visit(`${base}/course/cypress-test-course`)
    // Click the Enroll for Free button (submits form)
    cy.contains('button', 'Enroll for Free').click()
    // Should redirect to course player
    cy.url().should('include', '/course/cypress-test-course/learn')
  })

  it('course player loads after enrollment', () => {
    cy.visit(`${base}/course/cypress-test-course/learn`)
    cy.get('body').should('be.visible')
    cy.contains('Cypress Test Course').should('be.visible')
  })

  it('course detail shows continue learning after enrollment', () => {
    cy.visit(`${base}/course/cypress-test-course`)
    cy.contains('Continue Learning').should('be.visible')
  })

  it('my courses page shows enrolled course', () => {
    cy.visit(`${base}/konto/kurser`)
    cy.contains('Cypress Test Course').should('be.visible')
  })

  it('account page shows my courses link', () => {
    cy.visit(`${base}/konto`)
    cy.contains('My Courses').should('be.visible')
  })
})

describe('Course API Endpoints', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.customerLogin()
  })

  it('mark-complete API responds', () => {
    cy.request({
      method: 'POST',
      url: `${base}/api/courses/mark-complete`,
      body: { lesson_id: 1 },
      headers: { 'Content-Type': 'application/json' },
      failOnStatusCode: false,
    }).then(response => {
      expect(response.status).to.be.oneOf([200, 400, 401, 403, 422])
    })
  })

  it('save-position API responds', () => {
    cy.request({
      method: 'POST',
      url: `${base}/api/courses/save-position`,
      body: { lesson_id: 1, position: 30, watched_percent: 10 },
      headers: { 'Content-Type': 'application/json' },
      failOnStatusCode: false,
    }).then(response => {
      expect(response.status).to.be.oneOf([200, 400, 401, 403, 422])
    })
  })

  it('video-status API responds', () => {
    cy.request({
      method: 'GET',
      url: `${base}/api/courses/video-status?lesson_id=1`,
      failOnStatusCode: false,
    }).then(response => {
      expect(response.status).to.be.oneOf([200, 400, 401, 403, 404])
    })
  })
})
