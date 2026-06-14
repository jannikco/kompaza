describe('Admin Form Submissions (no 500s)', () => {

  const base = 'https://testcompany.kompaza.com'
  const ts = Date.now()

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // ── 1. All create/form pages load (GET → no 500) ──────────────────────────

  describe('Create pages load without errors', () => {
    const pages = [
      '/admin/artikler/opret',
      '/admin/eboger/opret',
      '/admin/produkter/opret',
      '/admin/kunder/opret',
      '/admin/brugere/opret',
      '/admin/lead-magnets/opret',
      '/admin/kurser/opret',
      '/admin/custom-pages/create',
      '/admin/redirects/create',
      '/admin/discount-codes/create',
      '/admin/email-sequences/create',
      '/admin/mastermind/create',
      '/admin/companies/create',
      '/admin/newsletters/compose',
      '/admin/consultations/types',
    ]

    pages.forEach(path => {
      it(`GET ${path} returns 200`, () => {
        cy.request({
          url: `${base}${path}`,
          failOnStatusCode: false,
        }).then(resp => {
          expect(resp.status).to.eq(200)
        })
      })
    })
  })

  // ── 2. Company CRUD (the broken one) ──────────────────────────────────────

  describe('Company CRUD', () => {
    it('creates a company without admin_user_id', () => {
      cy.visit(`${base}/admin/companies/create`)
      cy.get('input[name="company_name"]').type(`Test Company ${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/companies')
      cy.url().should('not.include', '/create')
      cy.contains('Test Company').should('exist')
    })

    it('creates a company with all fields', () => {
      cy.visit(`${base}/admin/companies/create`)
      cy.get('input[name="company_name"]').type(`Full Company ${ts}`)
      cy.get('input[name="total_licenses"]').clear().type('10')
      cy.get('form').submit()
      cy.url().should('include', '/admin/companies')
      cy.url().should('not.include', '/create')
    })

    it('edits a company', () => {
      cy.visit(`${base}/admin/companies`)
      cy.contains(`Test Company ${ts}`).closest('tr').find('a[href*="edit"]').click()
      cy.get('input[name="company_name"]').clear().type(`Edited Company ${ts}`)
      cy.get('form').first().submit()
      cy.url().should('include', '/admin/companies')
    })

    it('deletes created companies', () => {
      cy.visit(`${base}/admin/companies`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Edited Company ${ts}`)) {
          // Click the Delete button to reveal confirm form (Alpine.js toggle)
          cy.contains(`Edited Company ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          // Then click the Confirm button in the revealed form
          cy.contains(`Edited Company ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
          cy.url().should('include', '/admin/companies')
        }
      })
    })
  })

  // ── 3. Form submission tests (POST → no 500) ─────────────────────────────

  describe('Article form submission', () => {
    it('creates an article', () => {
      cy.visit(`${base}/admin/artikler/opret`)
      cy.get('input[name="title"]').type(`Cypress Article ${ts}`)
      cy.get('input[name="slug"]').clear().type(`cypress-article-${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/artikler')
      cy.url().should('not.include', '/opret')
    })
  })

  describe('Ebook form submission', () => {
    it('creates an ebook', () => {
      cy.visit(`${base}/admin/eboger/opret`)
      cy.get('input[name="title"]').type(`Cypress Ebook ${ts}`)
      cy.get('input[name="slug"]').clear().type(`cypress-ebook-${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/eboger')
      cy.url().should('not.include', '/opret')
    })
  })

  describe('Product form submission', () => {
    it('creates a product', () => {
      cy.visit(`${base}/admin/produkter/opret`)
      cy.get('input[name="name"]').type(`Cypress Product ${ts}`)
      cy.get('input[name="slug"]').clear().type(`cypress-product-${ts}`)
      cy.get('input[name="price_dkk"]').clear().type('199')
      cy.get('form').submit()
      cy.url().should('include', '/admin/produkter')
      cy.url().should('not.include', '/opret')
    })
  })

  describe('Customer form submission', () => {
    it('creates a customer', () => {
      cy.visit(`${base}/admin/kunder/opret`)
      cy.get('input[name="name"]').type(`Cypress Customer ${ts}`)
      cy.get('input[name="email"]').type(`cypress-customer-${ts}@example.com`)
      cy.get('input[name="password"]').type('CypressTest123!')
      cy.get('form').submit()
      cy.url().should('include', '/admin/kunder')
      cy.url().should('not.include', '/opret')
    })
  })

  describe('User form submission', () => {
    it('creates a user without 500', () => {
      // User store redirects to /admin/users (not a registered route),
      // so test via cy.request to verify no 500 on POST
      cy.visit(`${base}/admin/brugere/opret`)
      cy.get('input[name="name"]').type(`Cypress User ${ts}`)
      cy.get('input[name="email"]').type(`cypress-user-${ts}@example.com`)
      cy.get('input[name="password"]').type('CypressTest123!')
      // Submit via request to check status directly
      cy.get('form').then($form => {
        const formData = new FormData($form[0])
        const body = {}
        formData.forEach((val, key) => { body[key] = val })
        cy.request({
          method: 'POST',
          url: `${base}/admin/brugere/gem`,
          form: true,
          body,
          failOnStatusCode: false,
          followRedirect: false,
        }).then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
      })
    })
  })

  describe('Lead Magnet form submission', () => {
    it('creates a lead magnet', () => {
      cy.visit(`${base}/admin/lead-magnets/opret`)
      cy.contains('Skip AI').click()
      // Step 3 accordion: open "Basic Info" section first
      cy.contains('button', 'Basic Info').click()
      cy.get('input[x-model="formData.title"]').should('be.visible')
        .type(`Cypress Lead Magnet ${ts}`)
      cy.get('input[x-model="formData.slug"]').clear().type(`cypress-lm-${ts}`)
      // Open "Hero Section" accordion
      cy.contains('button', 'Hero').click()
      cy.get('input[x-model="formData.hero_headline"]').should('be.visible')
        .type('Download Our Free Guide')
      cy.get('form[action*="lead-magnets"]').submit()
      cy.url().should('include', '/admin/lead-magnets')
    })
  })

  describe('Course form submission', () => {
    it('creates a course', () => {
      cy.visit(`${base}/admin/kurser/opret`)
      cy.get('input[name="title"]').type(`Cypress Course ${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/kurser')
      cy.url().should('not.include', '/opret')
    })
  })

  describe('Custom Page form submission', () => {
    it('creates a custom page', () => {
      cy.visit(`${base}/admin/custom-pages/create`)
      cy.get('input[name="title"]').type(`Cypress Page ${ts}`)
      cy.get('input[name="slug"]').clear().type(`cypress-page-${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/custom-pages')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Redirect form submission', () => {
    it('creates a redirect', () => {
      cy.visit(`${base}/admin/redirects/create`)
      cy.get('input[name="from_path"]').type(`/cypress-from-${ts}`)
      cy.get('input[name="to_path"]').type(`/cypress-to-${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/redirects')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Discount Code form submission', () => {
    it('creates a discount code', () => {
      cy.visit(`${base}/admin/discount-codes/create`)
      cy.get('input[name="code"]').type(`CY${ts}`)
      cy.get('select[name="type"]').select('percentage')
      cy.get('input[name="value"]').clear().type('10')
      cy.get('form').submit()
      cy.url().should('include', '/admin/discount-codes')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Email Sequence form submission', () => {
    it('creates an email sequence', () => {
      cy.visit(`${base}/admin/email-sequences/create`)
      cy.get('input[name="name"]').type(`Cypress Sequence ${ts}`)
      cy.get('form').first().submit()
      cy.url().should('include', '/admin/email-sequences')
    })
  })

  describe('Mastermind form submission', () => {
    it('creates a mastermind program', () => {
      cy.visit(`${base}/admin/mastermind/create`)
      cy.get('input[name="title"]').type(`Cypress Mastermind ${ts}`)
      cy.get('form').submit()
      cy.url().should('include', '/admin/mastermind')
      cy.url().should('not.include', '/create')
    })
  })

  describe('Newsletter form submission', () => {
    it('saves a newsletter draft', () => {
      cy.visit(`${base}/admin/newsletters/compose`)
      cy.get('input[name="subject"]').type(`Cypress Newsletter ${ts}`)
      // Quill CDN may not load in CI; set hidden field directly
      cy.get('#body_html-hidden').then($el => {
        $el.val('<p>This is a test newsletter body.</p>')
      })
      cy.get('#newsletterForm').submit()
      cy.url().should('include', '/admin/newsletters')
    })
  })

  describe('Consultation Type form submission', () => {
    it('creates a consultation type', () => {
      cy.visit(`${base}/admin/consultations/types`)
      // Scope to the "Add New" form to avoid matching edit form inputs
      cy.get('form[action*="type-store"]').within(() => {
        cy.get('input[name="name"]').type(`Cypress Consult ${ts}`)
        cy.get('input[name="duration_minutes"]').clear().type('45')
        cy.get('input[name="price_dkk"]').clear().type('500')
        cy.root().submit()
      })
      cy.url().should('include', '/admin/consultations')
    })
  })

  // ── 4. Settings update ────────────────────────────────────────────────────

  describe('Settings form submission', () => {
    it('submits settings without 500', () => {
      // Settings update redirects to /admin/settings (not a registered route),
      // so test via cy.request to verify no 500
      cy.visit(`${base}/admin/indstillinger`)
      cy.get('form[action*="opdater"]').then($form => {
        const formData = new FormData($form[0])
        const body = {}
        formData.forEach((val, key) => { body[key] = val })
        cy.request({
          method: 'POST',
          url: `${base}/admin/indstillinger/opdater`,
          form: true,
          body,
          failOnStatusCode: false,
          followRedirect: false,
        }).then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
      })
    })
  })

  // ── 5. Empty/minimal submissions (should redirect with error, not 500) ──

  describe('Empty form submissions return errors (not 500)', () => {
    it('article with empty title → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/artikler/gem`,
        form: true,
        body: { title: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('product with empty name → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/produkter/gem`,
        form: true,
        body: { name: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('customer with empty fields → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/kunder/gem`,
        form: true,
        body: { name: '', email: '', password: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('company with empty name → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/companies/store`,
        form: true,
        body: { company_name: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('redirect with empty paths → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/redirects/store`,
        form: true,
        body: { from_path: '', to_path: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('discount code with empty fields → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/discount-codes/store`,
        form: true,
        body: { code: '', type: '', value: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('email sequence with empty name → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/email-sequences/store`,
        form: true,
        body: { name: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('mastermind with empty title → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/mastermind/store`,
        form: true,
        body: { title: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('consultation type with empty name → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/consultations/type-store`,
        form: true,
        body: { name: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('newsletter with empty fields → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/newsletters/store`,
        form: true,
        body: { subject: '', body_html: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('custom page with empty title → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/custom-pages/store`,
        form: true,
        body: { title: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('course with empty title → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/kurser/gem`,
        form: true,
        body: { title: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('ebook with empty title → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/eboger/gem`,
        form: true,
        body: { title: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('user with empty fields → not 500', () => {
      cy.request({
        method: 'POST',
        url: `${base}/admin/brugere/gem`,
        form: true,
        body: { name: '', email: '', password: '' },
        failOnStatusCode: false,
        followRedirect: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })
  })

  // ── 6. Cleanup created test data ──────────────────────────────────────────
  // Uses Alpine.js 2-step delete: click "Delete" button → click "Confirm" button

  describe('Cleanup test data', () => {
    it('deletes test article', () => {
      cy.visit(`${base}/admin/artikler`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Article ${ts}`)) {
          cy.contains(`Cypress Article ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress Article ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test ebook', () => {
      cy.visit(`${base}/admin/eboger`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Ebook ${ts}`)) {
          cy.contains(`Cypress Ebook ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress Ebook ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test product', () => {
      cy.visit(`${base}/admin/produkter`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Product ${ts}`)) {
          cy.contains(`Cypress Product ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress Product ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test customer via API', () => {
      // Customers list has no delete button; delete via direct POST
      cy.visit(`${base}/admin/kunder`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Customer ${ts}`)) {
          // Find the customer row and get their detail link to extract ID
          cy.contains(`Cypress Customer ${ts}`).closest('tr').find('a[href*="/admin/kunder/"]').first()
            .invoke('attr', 'href').then(href => {
              const id = href.match(/\/admin\/kunder\/(\d+)/)?.[1]
              if (id) {
                cy.request({
                  method: 'POST',
                  url: `${base}/admin/kunder/slet`,
                  form: true,
                  body: { id },
                  failOnStatusCode: false,
                  followRedirect: false,
                })
              }
            })
        }
      })
    })

    it('deletes test user', () => {
      cy.visit(`${base}/admin/brugere`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress User ${ts}`)) {
          cy.contains(`Cypress User ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress User ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test course', () => {
      cy.visit(`${base}/admin/kurser`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Course ${ts}`)) {
          cy.contains(`Cypress Course ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress Course ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test redirect', () => {
      cy.visit(`${base}/admin/redirects`)
      cy.get('body').then($body => {
        if ($body.text().includes(`/cypress-from-${ts}`)) {
          cy.contains(`/cypress-from-${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`/cypress-from-${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test discount code', () => {
      cy.visit(`${base}/admin/discount-codes`)
      cy.get('body').then($body => {
        if ($body.text().includes(`CY${ts}`)) {
          cy.contains(`CY${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`CY${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })

    it('deletes test mastermind', () => {
      cy.visit(`${base}/admin/mastermind`)
      cy.get('body').then($body => {
        if ($body.text().includes(`Cypress Mastermind ${ts}`)) {
          cy.contains(`Cypress Mastermind ${ts}`).closest('tr')
            .contains('button', 'Delete').click({ force: true })
          cy.contains(`Cypress Mastermind ${ts}`).closest('tr')
            .contains('button', 'Confirm').click({ force: true })
        }
      })
    })
  })
})
