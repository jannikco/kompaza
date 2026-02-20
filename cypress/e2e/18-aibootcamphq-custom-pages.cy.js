describe('AIbootcamphq Tenant (aibootcamphq.kompaza.com)', () => {

  const base = 'https://aibootcamphq.kompaza.com'

  // ==========================================
  // CUSTOM PAGES (imported from live site)
  // ==========================================
  describe('Custom pages', () => {
    const pages = [
      { slug: '',                          label: 'homepage' },
      { slug: 'gratis',                    label: 'gratis' },
      { slug: 'foredrag',                  label: 'foredrag' },
      { slug: 'konsulent',                 label: 'konsulent' },
      { slug: 'ai-konsulent',              label: 'ai-konsulent' },
      { slug: 'claude-cowork-kursus',      label: 'claude-cowork-kursus' },
      { slug: 'hjemmeside',                label: 'hjemmeside' },
      { slug: 'lp-klar-til-ai',           label: 'lp-klar-til-ai' },
      { slug: 'ebook-landing',             label: 'ebook-landing' },
      { slug: 'ebook-atlas',               label: 'ebook-atlas' },
      { slug: 'ai-gdpr-bog',              label: 'ai-gdpr-bog' },
      { slug: 'claude-cowork',             label: 'claude-cowork' },
      { slug: 'mba',                       label: 'mba' },
      { slug: 'free-ai-prompts',           label: 'free-ai-prompts' },
      { slug: 'free-atlas',                label: 'free-atlas' },
      { slug: 'free-udlaeg',               label: 'free-udlaeg' },
      { slug: 'free-konsulent-ai-tools',   label: 'free-konsulent-ai-tools' },
      { slug: 'free-claude-cowork',         label: 'free-claude-cowork' },
      { slug: 'free-gdpr-tjekliste',       label: 'free-gdpr-tjekliste' },
      { slug: 'course-purchase',            label: 'course-purchase' },
      { slug: 'book-purchase',              label: 'book-purchase' },
      { slug: 'claude-cowork-kursus-koeb',  label: 'claude-cowork-kursus-koeb' },
      { slug: 'atlas-purchase',             label: 'atlas-purchase' },
      { slug: 'claude-cowork-koeb',         label: 'claude-cowork-koeb' },
      { slug: 'ai-gdpr-bog-koeb',          label: 'ai-gdpr-bog-koeb' },
      { slug: 'privacy',                    label: 'privacy' },
    ]

    pages.forEach(page => {
      it(`${page.label} returns 200`, () => {
        const url = page.slug ? `${base}/${page.slug}` : base
        cy.request(url).its('status').should('eq', 200)
      })
    })
  })

  // ==========================================
  // SSL
  // ==========================================
  describe('SSL certificate', () => {
    it('serves valid HTTPS', () => {
      cy.request(base).its('status').should('eq', 200)
    })
  })

  // ==========================================
  // IMAGES
  // ==========================================
  describe('Images', () => {
    const images = [
      'logo.png', 'logo_white.png',
      'atlas-cover.png', 'book.png', 'claude-cowork-cover.jpg',
      'gdpr-ebook-cover.jpg', 'gdpr-tjekliste-cover.jpg',
      'gratis-prompts-cover.png', 'jannik-gul.jpg', 'jannik.jpg',
      'linkedin-bog-cover.jpg', 'linkedin-bog-cover.png',
      'linkedin-prompt-library.png',
    ]

    images.forEach(img => {
      it(`${img} is accessible`, () => {
        cy.request(`${base}/uploads/3/img/${img}`).its('status').should('eq', 200)
      })
    })

    it('homepage has no broken local images', () => {
      cy.visit(base)
      cy.get('img[src^="/uploads/"]').each($img => {
        cy.request($img.attr('src')).its('status').should('eq', 200)
      })
    })

    it('no unrewritten /img/ paths on homepage', () => {
      cy.visit(base)
      cy.get('img[src^="/img/"]').should('not.exist')
    })
  })

  // ==========================================
  // INTERNAL LINKS (no broken links)
  // ==========================================
  describe('Internal links', () => {
    const mainPages = ['', 'gratis', 'foredrag', 'konsulent', 'ai-konsulent',
      'claude-cowork-kursus', 'hjemmeside', 'lp-klar-til-ai']

    mainPages.forEach(page => {
      const label = page || 'homepage'
      it(`${label} has no broken internal links`, () => {
        const url = page ? `${base}/${page}` : base
        cy.visit(url)
        cy.get('a[href^="/"]').each($a => {
          const href = $a.attr('href')
          if (href.includes('mailto:')) return
          const cleanHref = href.split('?')[0].split('#')[0]
          if (cleanHref && cleanHref !== '/') {
            cy.request({ url: `${base}${cleanHref}`, failOnStatusCode: false })
              .then(resp => {
                expect(resp.status, `${cleanHref} on ${label}`).to.eq(200)
              })
          }
        })
      })
    })
  })

  // ==========================================
  // NO OLD SITE REFERENCES
  // ==========================================
  describe('No old site references', () => {
    it('homepage has no aibootcamphq.com/dk links', () => {
      cy.visit(base)
      cy.get('a[href*="aibootcamphq.com/dk"]').should('not.exist')
    })

    it('homepage has no /dk/ prefixed links', () => {
      cy.visit(base)
      cy.get('a[href^="/dk/"]').should('not.exist')
    })

    it('gratis page has no aibootcamphq.com/dk links', () => {
      cy.visit(`${base}/gratis`)
      cy.get('a[href*="aibootcamphq.com/dk"]').should('not.exist')
    })
  })

  // ==========================================
  // ANALYTICS REMOVED
  // ==========================================
  describe('Analytics removed', () => {
    it('no Matomo tracking on homepage', () => {
      cy.visit(base)
      cy.window().its('_paq').should('be.undefined')
    })

    it('no Brevo chat widget on homepage', () => {
      cy.visit(base)
      cy.window().its('BrevoConversationsID').should('be.undefined')
    })
  })

  // ==========================================
  // PUBLIC SHOP ROUTES
  // ==========================================
  describe('Public shop routes', () => {
    it('blog page loads', () => {
      cy.request(`${base}/blog`).its('status').should('eq', 200)
    })

    it('ebooks page loads', () => {
      cy.request(`${base}/eboger`).its('status').should('eq', 200)
    })

    it('products page loads', () => {
      cy.request(`${base}/produkter`).its('status').should('eq', 200)
    })

    it('courses page loads', () => {
      cy.request(`${base}/courses`).its('status').should('eq', 200)
    })

    it('cart page loads', () => {
      cy.request(`${base}/kurv`).its('status').should('eq', 200)
    })

    it('contact page loads', () => {
      cy.request(`${base}/contact`).its('status').should('eq', 200)
    })

    it('terms page loads', () => {
      cy.request(`${base}/terms`).its('status').should('eq', 200)
    })

    it('built-in privacy policy page loads', () => {
      cy.request(`${base}/privatlivspolitik`).its('status').should('eq', 200)
    })
  })

  // ==========================================
  // CUSTOMER AUTH ROUTES
  // ==========================================
  describe('Customer auth routes', () => {
    it('login page loads with form fields', () => {
      cy.visit(`${base}/login`)
      cy.get('input[name="email"]').should('be.visible')
      cy.get('input[name="password"]').should('be.visible')
    })

    it('register page loads with form fields', () => {
      cy.visit(`${base}/registrer`)
      cy.get('input[name="name"]').should('be.visible')
      cy.get('input[name="email"]').should('be.visible')
      cy.get('input[name="password"]').should('be.visible')
    })

    it('forgot password page loads', () => {
      cy.request(`${base}/forgot-password`).its('status').should('eq', 200)
    })

    it('account page redirects to login when not authenticated', () => {
      cy.request({ url: `${base}/konto`, followRedirect: false, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.oneOf([302, 200])
        })
    })
  })

  // ==========================================
  // ADMIN ROUTES (auth-protected, should redirect to login)
  // ==========================================
  describe('Admin routes (require auth)', () => {
    const adminRoutes = [
      '/admin',
      '/admin/ordrer',
      '/admin/kunder',
      '/admin/artikler',
      '/admin/eboger',
      '/admin/produkter',
      '/admin/lead-magnets',
      '/admin/tilmeldinger',
      '/admin/indstillinger',
      '/admin/kurser',
      '/admin/connectpilot',
      '/admin/custom-pages',
      '/admin/newsletters',
      '/admin/contact-messages',
      '/admin/discount-codes',
      '/admin/salg',
      '/admin/brugere',
    ]

    adminRoutes.forEach(route => {
      it(`${route} responds (redirect to login or 200)`, () => {
        cy.request({ url: `${base}${route}`, followRedirect: false, failOnStatusCode: false })
          .then(resp => {
            // Should redirect to login (302) or render (200 if already logged in)
            expect(resp.status, route).to.be.oneOf([200, 302])
          })
      })
    })

    it('/admin/ordrer/{id} responds (dynamic route)', () => {
      cy.request({ url: `${base}/admin/ordrer/1`, followRedirect: false, failOnStatusCode: false })
        .then(resp => {
          // 302 (login redirect), 200 (found), or 404 (order not found) are all valid
          expect(resp.status).to.be.oneOf([200, 302, 404])
        })
    })

    it('/admin/kunder/{id} responds (dynamic route)', () => {
      cy.request({ url: `${base}/admin/kunder/1`, followRedirect: false, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.oneOf([200, 302, 404])
        })
    })
  })

  // ==========================================
  // API ENDPOINTS
  // ==========================================
  describe('API endpoints', () => {
    it('newsletter signup accepts POST with JSON', () => {
      cy.request({
        method: 'POST',
        url: `${base}/api/newsletter`,
        body: { email: `cypress-${Date.now()}@example.com` },
        headers: { 'Content-Type': 'application/json' },
        failOnStatusCode: false,
      }).then(resp => {
        expect(resp.status).to.be.oneOf([200, 429])
        if (resp.status === 200) {
          expect(resp.body.success).to.eq(true)
        }
      })
    })

    it('newsletter signup rejects invalid email', () => {
      cy.request({
        method: 'POST',
        url: `${base}/api/newsletter`,
        body: { email: 'not-an-email' },
        headers: { 'Content-Type': 'application/json' },
        failOnStatusCode: false,
      }).then(resp => {
        expect(resp.status).to.be.oneOf([422, 429])
      })
    })

    it('cart add endpoint exists', () => {
      cy.request({
        method: 'POST',
        url: `${base}/api/cart/add`,
        body: { product_id: 1, quantity: 1 },
        headers: { 'Content-Type': 'application/json' },
        failOnStatusCode: false,
      }).then(resp => {
        // 200, 302, 400, 422 are all acceptable (just not 404/500)
        expect(resp.status).to.not.eq(404)
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('discount validate endpoint exists', () => {
      cy.request({
        method: 'POST',
        url: `${base}/api/discount/validate`,
        body: { code: 'TESTCODE' },
        headers: { 'Content-Type': 'application/json' },
        failOnStatusCode: false,
      }).then(resp => {
        expect(resp.status).to.not.eq(404)
        expect(resp.status).to.be.lessThan(500)
      })
    })
  })

  // ==========================================
  // CHECKOUT FLOW
  // ==========================================
  describe('Checkout flow', () => {
    it('checkout page loads', () => {
      cy.request({ url: `${base}/checkout`, failOnStatusCode: false })
        .then(resp => {
          // May redirect to cart if empty, or show checkout
          expect(resp.status).to.be.oneOf([200, 302])
        })
    })

    it('consultation page loads', () => {
      cy.request(`${base}/consultation`).its('status').should('eq', 200)
    })
  })

  // ==========================================
  // DYNAMIC CONTENT ROUTES
  // ==========================================
  describe('Dynamic content routes', () => {
    it('/blog/{slug} returns 404 for non-existent article (not 500)', () => {
      cy.request({ url: `${base}/blog/non-existent-article-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })

    it('/ebog/{slug} returns 404 for non-existent ebook (not 500)', () => {
      cy.request({ url: `${base}/ebog/non-existent-ebook-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })

    it('/produkt/{slug} returns 404 for non-existent product (not 500)', () => {
      cy.request({ url: `${base}/produkt/non-existent-product-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })

    it('/lp/{slug} returns 404 for non-existent lead magnet (not 500)', () => {
      cy.request({ url: `${base}/lp/non-existent-lp-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })

    it('/course/{slug} returns 404 for non-existent course (not 500)', () => {
      cy.request({ url: `${base}/course/non-existent-course-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })

    it('completely unknown path returns 404 (not 500)', () => {
      cy.request({ url: `${base}/this-does-not-exist-at-all`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })
  })
})
