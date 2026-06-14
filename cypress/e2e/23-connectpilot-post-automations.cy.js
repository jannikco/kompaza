describe('ConnectPilot Post Automations', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // Dashboard shows post automation stats
  it('shows post automation stats on dashboard', () => {
    cy.visit(`${base}/admin/connectpilot`)
    cy.contains('Active Post Automations').should('be.visible')
    cy.contains('Auto-DMs Sent').should('be.visible')
    cy.contains('Recent Post Automations').should('be.visible')
  })

  // Sidebar link exists
  it('has Post Automations link in sidebar', () => {
    cy.visit(`${base}/admin/connectpilot`)
    cy.get('a[href="/admin/connectpilot/automations"]').should('exist')
  })

  // Index page
  it('shows post automations list page', () => {
    cy.visit(`${base}/admin/connectpilot/automations`)
    cy.get('main').contains('Post Automations').should('be.visible')
    cy.get('main').contains('Create Automation').should('be.visible')
  })

  // Create form loads
  it('loads create automation form with all sections', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.contains('Back to Post Automations').should('be.visible')
    cy.get('input[name="name"]').should('be.visible')
    cy.get('select[name="linkedin_account_id"]').should('be.visible')
    cy.get('select[name="status"]').should('be.visible')
    cy.get('input[name="post_url"]').should('be.visible')
    cy.get('input[name="trigger_keyword"]').should('be.visible')
    cy.get('textarea[name="auto_reply_template"]').should('be.visible')
    cy.get('textarea[name="dm_template"]').should('be.visible')
    cy.get('select[name="lead_magnet_id"]').should('be.visible')
  })

  // Form sections visible
  it('shows all form sections', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.contains('Basics').should('be.visible')
    cy.contains('LinkedIn Post').should('be.visible')
    cy.contains('Trigger Keyword').should('be.visible')
    cy.contains('Auto-Reply to Comment').should('be.visible')
    cy.contains('Auto-DM').should('be.visible')
    cy.contains('Automation Flow').should('be.visible')
  })

  // Validate post URL (AJAX)
  it('validates post URL via AJAX', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.get('input[name="post_url"]').type('https://www.linkedin.com/feed/update/urn:li:activity:7123456789012345678/')
    cy.contains('Validate').click()
    cy.contains('Post URL validated', { timeout: 10000 }).should('be.visible')
  })

  // Validate post URL - invalid
  it('shows error for invalid post URL', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.get('input[name="post_url"]').type('https://www.google.com')
    cy.contains('Validate').click()
    cy.contains('valid LinkedIn post URL', { timeout: 10000 }).should('be.visible')
  })

  // Variable insertion buttons
  it('has variable insertion buttons for DM template', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.get('button').contains('first_name').should('be.visible')
    cy.get('button').contains('lead_magnet_url').should('be.visible')
  })

  // Toggle auto-reply off
  it('can toggle auto-reply on/off via Alpine.js', () => {
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.get('textarea[name="auto_reply_template"]').should('be.visible')
    cy.contains('Auto-Reply to Comment').parent().find('input[type="checkbox"]').uncheck({ force: true })
    cy.get('textarea[name="auto_reply_template"]').should('not.be.visible')
  })

  // Helper: delete all test automations by name via API
  function cleanupTestAutomations() {
    cy.visit(`${base}/admin/connectpilot/automations`)
    cy.getCookie('csrf_token').then((cookie) => {
      const csrfToken = cookie ? cookie.value : ''
      cy.get('main').then(($main) => {
        const editLinks = $main.find('a[href*="/automations/edit?id="]')
        const idsToDelete = []
        editLinks.each((i, el) => {
          const row = Cypress.$(el).closest('tr')
          const name = row.find('.text-sm.font-medium.text-gray-900').text().trim()
          if (name === 'Cypress Test Automation' || name === 'Cypress Updated Automation') {
            const href = el.getAttribute('href')
            const id = new URL(href, base).searchParams.get('id')
            if (id) idsToDelete.push(id)
          }
        })
        idsToDelete.forEach((id) => {
          cy.request({
            method: 'POST',
            url: `${base}/admin/connectpilot/automations/delete`,
            form: true,
            body: { id, csrf_token: csrfToken },
            followRedirect: true,
          })
        })
      })
    })
  }

  // Full CRUD flow as a single ordered test
  it('creates, edits, views comments, and deletes a post automation', () => {
    // --- CLEANUP leftover test data from previous failed attempts ---
    cleanupTestAutomations()

    // --- CREATE ---
    cy.visit(`${base}/admin/connectpilot/automations/create`)
    cy.get('input[name="name"]').type('Cypress Test Automation')
    cy.get('input[name="post_url"]').type('https://www.linkedin.com/feed/update/urn:li:activity:7123456789012345678/')
    cy.get('input[name="trigger_keyword"]').type('GUIDE')
    cy.get('textarea[name="auto_reply_template"]').clear().type('Check your DMs!')
    cy.get('textarea[name="dm_template"]').clear().type('Hi, here is your guide!', { parseSpecialCharSequences: false })
    cy.get('form').submit()
    cy.url().should('include', '/admin/connectpilot/automations')
    cy.url().should('not.include', '/create')
    cy.get('main').contains('Cypress Test Automation').should('be.visible')

    // --- LIST: verify data ---
    cy.get('main').contains('GUIDE').should('be.visible')
    cy.get('main').contains('Active').should('be.visible')

    // --- EDIT: load and verify pre-filled ---
    cy.get('main').contains('Cypress Test Automation').parents('tr').find('a').contains('Edit').click()
    cy.url().should('include', '/automations/edit')
    cy.get('input[name="name"]').should('have.value', 'Cypress Test Automation')
    cy.get('input[name="trigger_keyword"]').should('have.value', 'GUIDE')
    // Stats card at top
    cy.get('main').contains('Comments').should('be.visible')
    cy.get('main').contains('Matches').should('be.visible')
    cy.get('main').contains('DMs Sent').should('be.visible')

    // --- UPDATE ---
    cy.get('input[name="name"]').clear().type('Cypress Updated Automation')
    cy.get('form').submit()
    cy.url().should('include', '/admin/connectpilot/automations')
    cy.url().should('not.include', '/edit')
    cy.contains('updated successfully').should('be.visible')
    cy.get('main').contains('Cypress Updated Automation').should('be.visible')

    // --- COMMENTS PAGE ---
    cy.get('main').contains('Cypress Updated Automation').parents('tr').find('a[href*="comments"]').click()
    cy.url().should('include', '/automations/comments')
    cy.contains('GUIDE').should('be.visible')
    cy.contains('All').should('be.visible')
    cy.contains('Matched Only').should('be.visible')
    cy.contains('Pending DM').should('be.visible')
    cy.contains('No comments detected yet').should('be.visible')

    // --- DELETE via API (overflow:hidden clips Alpine.js confirm button) ---
    cy.visit(`${base}/admin/connectpilot/automations`)
    cy.get('main').contains('Cypress Updated Automation').parents('tr').find('a[href*="/automations/edit?id="]').then(($editLink) => {
      const id = new URL($editLink.attr('href'), base).searchParams.get('id')
      cy.getCookie('csrf_token').then((cookie) => {
        cy.request({
          method: 'POST',
          url: `${base}/admin/connectpilot/automations/delete`,
          form: true,
          body: { id, csrf_token: cookie.value },
          followRedirect: true,
        })
      })
    })
    cy.visit(`${base}/admin/connectpilot/automations`)
    cy.get('main').contains('Cypress Updated Automation').should('not.exist')
  })

  // API endpoint tests
  it('POST /api/connectpilot/validate-post returns URN for valid URL', () => {
    cy.request({
      method: 'POST',
      url: `${base}/api/connectpilot/validate-post`,
      body: { post_url: 'https://www.linkedin.com/feed/update/urn:li:activity:7123456789012345678/' },
      headers: { 'Content-Type': 'application/json' },
    }).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.success).to.eq(true)
      expect(response.body.post_urn).to.eq('urn:li:activity:7123456789012345678')
    })
  })

  it('POST /api/connectpilot/validate-post rejects non-LinkedIn URL', () => {
    cy.request({
      method: 'POST',
      url: `${base}/api/connectpilot/validate-post`,
      body: { post_url: 'https://www.google.com' },
      headers: { 'Content-Type': 'application/json' },
    }).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.success).to.eq(false)
    })
  })
})
