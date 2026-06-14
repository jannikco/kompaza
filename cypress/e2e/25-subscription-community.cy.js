describe('Subscription & Community (admin + shop smoke)', () => {

  const base = 'https://testcompany.kompaza.com'

  // ── Admin pages (require tenant admin login) ──────────────────────────────

  describe('Admin subscription/community pages load', () => {
    beforeEach(() => {
      cy.tenantAdminLogin()
    })

    const pages = [
      '/admin/memberships',
      '/admin/memberships/create',
      '/admin/prompts',
      '/admin/prompts/create',
      '/admin/prompts/categories',
      '/admin/community',
      '/admin/community/channels',
      '/admin/webinars',
      '/admin/webinars/create',
      '/admin/live-sessions',
      '/admin/live-sessions/create',
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

    it('memberships index shows heading', () => {
      cy.visit(`${base}/admin/memberships`)
      cy.contains('Membership Plans').should('be.visible')
    })

    it('prompts index shows heading', () => {
      cy.visit(`${base}/admin/prompts`)
      cy.contains('Prompts').should('be.visible')
    })

    it('community index shows heading', () => {
      cy.visit(`${base}/admin/community`)
      cy.contains('Community').should('be.visible')
    })

    it('webinars index shows new webinar action', () => {
      cy.visit(`${base}/admin/webinars`)
      cy.contains('New Webinar').should('be.visible')
    })

    it('live sessions index shows heading', () => {
      cy.visit(`${base}/admin/live-sessions`)
      cy.contains('Live Sessions').should('be.visible')
    })

    it('membership create page has a form', () => {
      cy.visit(`${base}/admin/memberships/create`)
      cy.get('form').should('exist')
    })

    it('prompt create page has a form', () => {
      cy.visit(`${base}/admin/prompts/create`)
      cy.get('form').should('exist')
    })

    it('live session create page has a form', () => {
      cy.visit(`${base}/admin/live-sessions/create`)
      cy.get('form').should('exist')
    })
  })

  // ── Shop pages (public) ───────────────────────────────────────────────────
  // /membership, /prompts and /community are feature-gated: they return 200
  // when the tenant has the feature enabled, or 404 when disabled. Either way
  // they must never 500. When enabled, the expected heading is shown.

  describe('Shop subscription/community pages respond', () => {
    const gated = [
      { path: '/membership', heading: 'Membership Plans' },
      { path: '/prompts', heading: 'Prompt Library' },
      { path: '/community', heading: 'Community' },
    ]

    gated.forEach(({ path, heading }) => {
      it(`GET ${path} responds without server error`, () => {
        cy.request({
          url: `${base}${path}`,
          failOnStatusCode: false,
        }).then(resp => {
          expect(resp.status).to.be.lessThan(500)
          expect(resp.status).to.be.oneOf([200, 404])
        })
      })

      it(`${path} shows its heading when the feature is enabled`, () => {
        cy.request({
          url: `${base}${path}`,
          failOnStatusCode: false,
        }).then(resp => {
          if (resp.status === 200) {
            cy.visit(`${base}${path}`)
            cy.contains(heading).should('be.visible')
          }
        })
      })
    })

    // Live Q&A sessions page is not feature-gated.
    it('live Q&A page loads and shows heading', () => {
      cy.visit(`${base}/live-qa`)
      cy.contains('Live Q&A Sessions').should('be.visible')
    })
  })
})
