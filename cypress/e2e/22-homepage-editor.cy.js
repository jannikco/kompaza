describe('Homepage Editor', () => {

  const base = 'https://testcompany.kompaza.com'

  beforeEach(() => {
    cy.tenantAdminLogin()
  })

  // ── 1. Page loads ────────────────────────────────────────────────────────

  it('loads the homepage editor page', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('main').contains('Homepage Editor').should('be.visible')
  })

  it('displays template picker with 3 options', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('input[name="_template"][value="starter"]').should('exist')
    cy.get('input[name="_template"][value="bold"]').should('exist')
    cy.get('input[name="_template"][value="elegant"]').should('exist')
  })

  it('displays hero CTA inputs', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('main').contains('Hero Call-to-Action Buttons').should('be.visible')
    cy.get('input[x-model="hero.cta_primary_text"]').should('be.visible')
    cy.get('input[x-model="hero.cta_primary_url"]').should('be.visible')
    cy.get('input[x-model="hero.cta_secondary_text"]').should('be.visible')
    cy.get('input[x-model="hero.cta_secondary_url"]').should('be.visible')
  })

  it('displays sections list', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('main').contains('Sections').should('be.visible')
    cy.get('[x-data="homepageEditor()"]').should('exist')
  })

  // ── 2. Sidebar link ─────────────────────────────────────────────────────

  it('has Homepage link in admin sidebar', () => {
    cy.visit(`${base}/admin`)
    cy.get('aside a[href="/admin/homepage"]').should('be.visible')
    cy.get('aside a[href="/admin/homepage"]').contains('Homepage')
  })

  // ── 3. Settings page links to editor ────────────────────────────────────

  it('settings page links to homepage editor instead of template picker', () => {
    cy.visit(`${base}/admin/indstillinger`)
    cy.get('main').contains('Homepage Design').should('be.visible')
    cy.get('a[href="/admin/homepage"]').contains('Open Homepage Editor').should('be.visible')
    // Template radio buttons should NOT be in settings anymore
    cy.get('input[name="homepage_template"]').should('not.exist')
  })

  // ── 4. Template switching ───────────────────────────────────────────────

  it('can select different templates', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.contains('label', 'Bold').click()
    cy.get('input[name="_template"][value="bold"]').should('be.checked')
    cy.contains('label', 'Elegant').click()
    cy.get('input[name="_template"][value="elegant"]').should('be.checked')
    cy.contains('label', 'Starter').click()
    cy.get('input[name="_template"][value="starter"]').should('be.checked')
  })

  // ── 5. Section toggling ─────────────────────────────────────────────────

  it('can toggle sections on/off', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('.peer.sr-only').first().click({ force: true })
    cy.get('[x-data="homepageEditor()"]').should('exist')
  })

  // ── 6. Section expand/collapse ──────────────────────────────────────────

  it('can expand section to edit heading', () => {
    cy.visit(`${base}/admin/homepage`)
    // Click the last button in the first section header (the expand chevron)
    cy.get('.bg-gray-50').first().find('button').last().click()
    // Wait for collapse animation and check heading input exists
    cy.wait(500)
    cy.get('input[x-model="sec.heading"]').first().should('exist')
  })

  // ── 7. Hero CTA editing ─────────────────────────────────────────────────

  it('can edit hero CTA text', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('input[x-model="hero.cta_primary_text"]').clear().type('Shop Now')
    cy.get('input[x-model="hero.cta_primary_text"]').should('have.value', 'Shop Now')
    cy.get('input[x-model="hero.cta_primary_url"]').clear().type('/produkter')
    cy.get('input[x-model="hero.cta_primary_url"]').should('have.value', '/produkter')
  })

  // ── 8. Add richtext section ─────────────────────────────────────────────

  it('can add a richtext section', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('.bg-gray-50').then($sections => {
      const initialCount = $sections.length
      cy.contains('button', 'Add Section').click()
      cy.contains('button', 'Rich Text Block').click()
      // Should have one more section header
      cy.get('.bg-gray-50').should('have.length', initialCount + 1)
    })
  })

  // ── 9. Section reordering ───────────────────────────────────────────────

  it('has move up/down buttons on sections', () => {
    cy.visit(`${base}/admin/homepage`)
    // First section's up button should be disabled
    cy.get('.bg-gray-50').first().within(() => {
      cy.get('button').first().should('be.disabled')
    })
  })

  // ── 10. Save and verify ─────────────────────────────────────────────────

  it('saves homepage config and shows success message', () => {
    cy.visit(`${base}/admin/homepage`)

    cy.get('input[x-model="hero.cta_primary_text"]').clear().type('Browse Products')
    cy.get('input[x-model="hero.cta_primary_url"]').clear().type('/produkter')
    cy.get('input[x-model="hero.cta_secondary_text"]').clear().type('Read Our Blog')
    cy.get('input[x-model="hero.cta_secondary_url"]').clear().type('/blog')

    cy.contains('button', 'Save Homepage').click()

    // Should redirect back with success flash
    cy.url().should('include', '/admin/homepage')
    cy.contains('Homepage updated successfully', { timeout: 10000 }).should('exist')
  })

  it('persists saved config on reload', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.get('input[x-model="hero.cta_primary_text"]').should('have.value', 'Browse Products')
    cy.get('input[x-model="hero.cta_primary_url"]').should('have.value', '/produkter')
  })

  // ── 11. Frontend renders correctly ──────────────────────────────────────

  it('tenant homepage still renders without errors', () => {
    cy.request({
      url: `${base}/`,
      failOnStatusCode: false,
    }).then(resp => {
      expect(resp.status).to.eq(200)
      expect(resp.body).to.include('Browse Products')
    })
  })

  it('hero CTA buttons appear on the homepage', () => {
    cy.visit(`${base}/`)
    cy.contains('a', 'Browse Products').should('be.visible')
    cy.contains('a', 'Read Our Blog').should('be.visible')
  })

  // ── 12. Template change reflects on frontend ───────────────────────────

  it('changing template to bold saves and renders', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.contains('label', 'Bold').click()
    cy.contains('button', 'Save Homepage').click()
    cy.url().should('include', '/admin/homepage')
    cy.contains('Homepage updated successfully', { timeout: 10000 }).should('exist')

    // Verify frontend has bold gradient
    cy.visit(`${base}/`)
    cy.get('.bold-hero-gradient').should('exist')
  })

  it('restores starter template', () => {
    cy.visit(`${base}/admin/homepage`)
    cy.contains('label', 'Starter').click()
    cy.contains('button', 'Save Homepage').click()
    cy.url().should('include', '/admin/homepage')
    cy.contains('Homepage updated successfully', { timeout: 10000 }).should('exist')
  })

  // ── 13. Section disable hides from frontend ────────────────────────────

  it('disabling newsletter section hides it from homepage', () => {
    cy.visit(`${base}/admin/homepage`)

    // Find the newsletter badge text, go up to the section header, find the toggle
    cy.contains('span', 'Newsletter')
      .closest('.bg-gray-50')
      .find('input[type="checkbox"]')
      .uncheck({ force: true })

    cy.contains('button', 'Save Homepage').click()
    cy.contains('Homepage updated successfully', { timeout: 10000 }).should('exist')

    // Verify newsletter is hidden on frontend
    cy.visit(`${base}/`)
    cy.contains('Stay Updated').should('not.exist')
  })

  it('re-enabling newsletter section shows it on homepage', () => {
    cy.visit(`${base}/admin/homepage`)

    cy.contains('span', 'Newsletter')
      .closest('.bg-gray-50')
      .find('input[type="checkbox"]')
      .check({ force: true })

    cy.contains('button', 'Save Homepage').click()
    cy.contains('Homepage updated successfully', { timeout: 10000 }).should('exist')

    cy.visit(`${base}/`)
    cy.contains('Stay Updated').should('be.visible')
  })
})
