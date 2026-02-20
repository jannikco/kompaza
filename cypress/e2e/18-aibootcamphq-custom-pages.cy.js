describe('AIbootcamphq Custom Pages (aibootcamphq.kompaza.com)', () => {

  const base = 'https://aibootcamphq.kompaza.com'

  // All imported custom pages
  const pages = [
    { slug: '',                        title: 'AI BootCamp',     isHomepage: true },
    { slug: 'gratis',                  title: 'Gratis' },
    { slug: 'foredrag',                title: 'Foredrag' },
    { slug: 'konsulent',               title: 'Konsulent' },
    { slug: 'ai-konsulent',            title: 'AI Konsulent' },
    { slug: 'claude-cowork-kursus',    title: 'Claude' },
    { slug: 'hjemmeside',              title: 'hjemmeside' },
    { slug: 'lp-klar-til-ai',         title: 'AI' },
    { slug: 'ebook-landing',           title: 'LinkedIn' },
    { slug: 'ebook-atlas',             title: 'Atlas' },
    { slug: 'ai-gdpr-bog',            title: 'GDPR' },
    { slug: 'claude-cowork',           title: 'Claude' },
    { slug: 'mba',                     title: 'MBA' },
    { slug: 'free-ai-prompts',         title: 'Prompts' },
    { slug: 'free-atlas',              title: 'Atlas' },
    { slug: 'free-udlaeg',             title: 'udl' },
    { slug: 'free-konsulent-ai-tools', title: 'AI' },
    { slug: 'free-claude-cowork',      title: 'Claude' },
    { slug: 'free-gdpr-tjekliste',     title: 'GDPR' },
    { slug: 'course-purchase',         title: 'Kursus' },
    { slug: 'book-purchase',           title: 'bog' },
    { slug: 'claude-cowork-kursus-koeb', title: 'Claude' },
    { slug: 'atlas-purchase',          title: 'Atlas' },
    { slug: 'claude-cowork-koeb',      title: 'Claude' },
    { slug: 'ai-gdpr-bog-koeb',       title: 'GDPR' },
    { slug: 'privacy',                 title: 'Privat' },
  ]

  describe('Page loading', () => {
    pages.forEach(page => {
      const url = page.slug ? `${base}/${page.slug}` : base
      const label = page.slug || 'homepage'

      it(`${label} returns 200 and renders`, () => {
        cy.visit(url)
        cy.get('body').should('be.visible')
        cy.title().should('not.be.empty')
      })
    })
  })

  describe('SSL certificate', () => {
    it('serves valid HTTPS (no insecure warning)', () => {
      cy.request(base).then(response => {
        expect(response.status).to.eq(200)
      })
    })
  })

  describe('Images', () => {
    it('logo image loads on homepage', () => {
      cy.request(`${base}/uploads/3/img/logo.png`).then(response => {
        expect(response.status).to.eq(200)
        expect(response.headers['content-type']).to.include('image/png')
      })
    })

    it('logo_white image loads', () => {
      cy.request(`${base}/uploads/3/img/logo_white.png`).then(response => {
        expect(response.status).to.eq(200)
      })
    })

    it('all tenant images are accessible', () => {
      const images = [
        'logo.png', 'logo_white.png',
        'atlas-cover.png', 'book.png', 'claude-cowork-cover.jpg',
        'gdpr-ebook-cover.jpg', 'gdpr-tjekliste-cover.jpg',
        'gratis-prompts-cover.png', 'jannik-gul.jpg', 'jannik.jpg',
        'linkedin-bog-cover.jpg', 'linkedin-bog-cover.png',
        'linkedin-prompt-library.png',
      ]
      images.forEach(img => {
        cy.request(`${base}/uploads/3/img/${img}`).then(response => {
          expect(response.status).to.eq(200)
        })
      })
    })

    it('homepage has no broken local images', () => {
      cy.visit(base)
      cy.get('img[src^="/uploads/"]').each($img => {
        const src = $img.attr('src')
        cy.request(src).its('status').should('eq', 200)
      })
    })

    it('no /img/ paths remain (should all be /uploads/3/img/)', () => {
      cy.visit(base)
      cy.get('img[src^="/img/"]').should('not.exist')
    })
  })

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
          // Skip anchors with query params (they might be dynamic) and mailto
          if (href.includes('mailto:')) return
          // Only check simple paths
          const cleanHref = href.split('?')[0].split('#')[0]
          if (cleanHref && cleanHref !== '/') {
            cy.request({
              url: `${base}${cleanHref}`,
              failOnStatusCode: false,
            }).then(response => {
              expect(response.status, `${cleanHref} on ${label}`).to.eq(200)
            })
          }
        })
      })
    })
  })

  describe('No old site references', () => {
    it('homepage has no links to aibootcamphq.com', () => {
      cy.visit(base)
      cy.get('a[href*="aibootcamphq.com/dk"]').should('not.exist')
    })

    it('homepage has no /dk/ prefixed links', () => {
      cy.visit(base)
      cy.get('a[href^="/dk/"]').should('not.exist')
    })

    it('gratis page has no links to aibootcamphq.com', () => {
      cy.visit(`${base}/gratis`)
      cy.get('a[href*="aibootcamphq.com/dk"]').should('not.exist')
    })
  })

  describe('Analytics removed', () => {
    it('homepage has no Matomo tracking', () => {
      cy.visit(base)
      cy.window().then(win => {
        expect(win._paq).to.be.undefined
      })
    })

    it('homepage has no Brevo chat widget', () => {
      cy.visit(base)
      cy.window().then(win => {
        expect(win.BrevoConversationsID).to.be.undefined
      })
    })
  })

  describe('API endpoints', () => {
    it('newsletter signup API accepts POST', () => {
      cy.request({
        method: 'POST',
        url: `${base}/api/newsletter`,
        body: { email: `cypress-test-${Date.now()}@example.com` },
        headers: { 'Content-Type': 'application/json' },
        failOnStatusCode: false,
      }).then(response => {
        expect(response.status).to.be.oneOf([200, 429])
        if (response.status === 200) {
          expect(response.body.success).to.eq(true)
        }
      })
    })
  })
})
