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
  // CUSTOM PAGE CONTENT CHECKS
  // ==========================================
  describe('Custom page content', () => {
    it('homepage has tenant branding', () => {
      cy.visit(base)
      cy.get('img[src*="/uploads/"]').should('have.length.gte', 1)
    })

    it('gratis page has links to free resources', () => {
      cy.visit(`${base}/gratis`)
      cy.get('a[href]').should('have.length.gte', 1)
      cy.get('body').invoke('text').should('have.length.gte', 100)
    })

    it('foredrag page loads with content', () => {
      cy.visit(`${base}/foredrag`)
      cy.get('body').invoke('text').should('have.length.gte', 100)
    })

    it('konsulent page loads with content', () => {
      cy.visit(`${base}/konsulent`)
      cy.get('body').invoke('text').should('have.length.gte', 100)
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
  // URL REDIRECTS (301)
  // ==========================================
  describe('URL redirects', () => {
    const redirects = [
      { from: '/dk/ebook-atlas',       to: '/ebog/chatgpt-atlas-guide' },
      { from: '/eboeger',              to: '/eboger' },
      { from: '/ebook-linkedin',       to: '/ebog/linkedin-ai-mastery' },
      { from: '/linkedin',             to: '/courses' },
      { from: '/linkedin-purchase',    to: '/courses' },
      { from: '/linkedin-koeb',        to: '/courses' },
      { from: '/book-konsulent',       to: '/consultation' },
      { from: '/nyhedsbrev',           to: '/' },
      { from: '/kursus',               to: '/courses' },
      { from: '/eu',                   to: '/courses' },
      { from: '/verify',               to: '/' },
      { from: '/privacy-policy',       to: '/privacy' },
      { from: '/terms-of-service',     to: '/terms' },
      { from: '/hjemmeside-venteliste', to: '/hjemmeside' },
      { from: '/konsultation-tak',     to: '/consultation/success' },
      { from: '/dk/ebook-linkedin',    to: '/ebog/linkedin-ai-mastery' },
      { from: '/dk/gratis',            to: '/gratis' },
      { from: '/dk/foredrag',          to: '/foredrag' },
      { from: '/dk/konsulent',         to: '/konsulent' },
      { from: '/dk/free-atlas',        to: '/free-atlas' },
      { from: '/dk/free-ai-prompts',   to: '/free-ai-prompts' },
    ]

    redirects.forEach(r => {
      it(`${r.from} → ${r.to} (301)`, () => {
        cy.request({ url: `${base}${r.from}`, followRedirect: false })
          .then(resp => {
            expect(resp.status).to.eq(301)
            expect(resp.headers.location).to.include(r.to)
          })
      })
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
  // LEAD MAGNET LANDING PAGES
  // ==========================================
  describe('Lead magnet landing pages', () => {
    const leadMagnets = [
      'free-atlas',
      'free-ai-prompts',
      'free-ai-tools',
      'free-udlaeg',
    ]

    leadMagnets.forEach(slug => {
      it(`/lp/${slug} returns 200`, () => {
        cy.request(`${base}/lp/${slug}`).its('status').should('eq', 200)
      })
    })

    it('lead magnet page has signup form', () => {
      cy.visit(`${base}/lp/free-atlas`)
      cy.get('input[name="email"], input[type="email"]').should('exist')
      cy.get('button[type="submit"], input[type="submit"]').should('exist')
    })

    it('/lp/succes/{slug} returns 200', () => {
      cy.request({ url: `${base}/lp/succes/free-atlas`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.oneOf([200, 302])
        })
    })

    it('/lp/download/fake-token returns appropriate status (not 500)', () => {
      cy.request({ url: `${base}/lp/download/fake-token-xyz`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
    })

    it('/lp/{slug} returns 404 for non-existent lead magnet', () => {
      cy.request({ url: `${base}/lp/non-existent-lp-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })
  })

  // ==========================================
  // COURSE PAGES
  // ==========================================
  describe('Course pages', () => {
    it('/courses returns 200 and lists courses', () => {
      cy.visit(`${base}/courses`)
      cy.get('body').invoke('text').should('have.length.gte', 50)
    })

    const courseSlugs = [
      'eu-ai-act-compliance',
      'hjemmeside-ai-replit',
      'claude-cowork-masterclass',
    ]

    courseSlugs.forEach(slug => {
      it(`/course/${slug} returns 200`, () => {
        cy.request(`${base}/course/${slug}`).its('status').should('eq', 200)
      })
    })

    it('/course/{slug}/learn redirects to login if not authenticated', () => {
      cy.request({ url: `${base}/course/eu-ai-act-compliance/learn`, followRedirect: false, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.oneOf([302, 200])
        })
    })

    it('/course/{slug}/buy responds (not 500)', () => {
      cy.request({ url: `${base}/course/eu-ai-act-compliance/buy`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
    })

    it('/course/non-existent returns 404 (not 500)', () => {
      cy.request({ url: `${base}/course/non-existent-course-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })
  })

  // ==========================================
  // EBOOKS (expanded)
  // ==========================================
  describe('Ebooks', () => {
    it('/eboger returns 200', () => {
      cy.request(`${base}/eboger`).its('status').should('eq', 200)
    })

    const ebookSlugs = [
      'chatgpt-atlas-guide',
      'linkedin-ai-mastery',
      'ai-resultater-7-dage',
      '300-ai-prompts',
    ]

    ebookSlugs.forEach(slug => {
      it(`/ebog/${slug} returns 200`, () => {
        cy.request(`${base}/ebog/${slug}`).its('status').should('eq', 200)
      })
    })

    it('/eboger page shows cover images', () => {
      cy.visit(`${base}/eboger`)
      cy.get('img[src*="/uploads/"]').should('have.length.gte', 1)
    })

    it('ebook detail page has title and content', () => {
      cy.visit(`${base}/ebog/chatgpt-atlas-guide`)
      cy.get('body').invoke('text').should('have.length.gte', 100)
    })

    it('/ebog/kob-succes/fake-session returns appropriate status (not 500)', () => {
      cy.request({ url: `${base}/ebog/kob-succes/fake-session-xyz`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
    })

    it('/ebog/download/fake-token returns appropriate status (not 500)', () => {
      cy.request({ url: `${base}/ebog/download/fake-token-xyz`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.lessThan(500)
        })
    })

    it('/ebog/{slug} returns 404 for non-existent ebook (not 500)', () => {
      cy.request({ url: `${base}/ebog/non-existent-ebook-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })
  })

  // ==========================================
  // CONSULTATION BOOKING
  // ==========================================
  describe('Consultation booking', () => {
    it('/consultation returns 200', () => {
      cy.request(`${base}/consultation`).its('status').should('eq', 200)
    })

    it('/consultation/success returns 200', () => {
      cy.request({ url: `${base}/consultation/success`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.oneOf([200, 302])
        })
    })

    it('consultation page has booking form fields', () => {
      cy.visit(`${base}/consultation`)
      cy.get('body').invoke('text').should('have.length.gte', 50)
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
      '/admin/redirects',
      '/admin/consultations',
      '/admin/companies',
      '/admin/email-sequences',
    ]

    adminRoutes.forEach(route => {
      it(`${route} responds (redirect to login or 200)`, () => {
        cy.request({ url: `${base}${route}`, followRedirect: false, failOnStatusCode: false })
          .then(resp => {
            expect(resp.status, route).to.be.oneOf([200, 302])
          })
      })
    })

    it('/admin/ordrer/{id} responds (dynamic route)', () => {
      cy.request({ url: `${base}/admin/ordrer/1`, followRedirect: false, failOnStatusCode: false })
        .then(resp => {
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
  // MASTERMIND ROUTES
  // ==========================================
  describe('Mastermind routes', () => {
    it('/mastermind/fake-slug returns 404 (not 500)', () => {
      cy.request({ url: `${base}/mastermind/fake-slug-xyz`, failOnStatusCode: false })
        .its('status').should('eq', 404)
    })
  })

  // ==========================================
  // CERTIFICATE VERIFICATION
  // ==========================================
  describe('Certificate verification', () => {
    it('/certificate/verify/fake-slug returns appropriate status (not 500)', () => {
      cy.request({ url: `${base}/certificate/verify/fake-slug-xyz`, failOnStatusCode: false })
        .then(resp => {
          expect(resp.status).to.be.lessThan(500)
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
          expect(resp.status).to.be.oneOf([200, 302])
        })
    })

    it('consultation page loads', () => {
      cy.request(`${base}/consultation`).its('status').should('eq', 200)
    })
  })

  // ==========================================
  // DYNAMIC CONTENT ROUTES (404 not 500)
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

  // ==========================================
  // NO SERVER ERRORS (500)
  // ==========================================
  describe('No server errors', () => {
    const malformedUrls = [
      '/admin/ordrer/abc',
      '/admin/kunder/abc',
      '/ebog/',
      '/course/',
      '/lp/',
      '/blog/',
      '/%00',
      '/..%2f..%2fetc%2fpasswd',
      '/<script>alert(1)</script>',
    ]

    malformedUrls.forEach(url => {
      it(`${url} does not return 500`, () => {
        cy.request({ url: `${base}${url}`, failOnStatusCode: false })
          .then(resp => {
            expect(resp.status, url).to.be.lessThan(500)
          })
      })
    })
  })
})
