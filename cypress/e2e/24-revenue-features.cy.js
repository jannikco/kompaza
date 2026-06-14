describe('Revenue Features (admin + shop smoke)', () => {

  const base = 'https://testcompany.kompaza.com'

  // ── Admin pages (require tenant admin login) ──────────────────────────────

  describe('Admin revenue pages load', () => {
    beforeEach(() => {
      cy.tenantAdminLogin()
    })

    // GET → 200 (no 500s) for index + create pages
    const pages = [
      '/admin/order-bumps',
      '/admin/order-bumps/create',
      '/admin/upsells',
      '/admin/upsells/create',
      '/admin/payment-links',
      '/admin/payment-links/create',
      '/admin/invoices',
      '/admin/invoices/create',
      '/admin/abandoned-carts',
      '/admin/countdown-timers',
      '/admin/countdown-timers/create',
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

    // Index pages render their headings
    it('order bumps index shows heading', () => {
      cy.visit(`${base}/admin/order-bumps`)
      cy.contains('Order Bumps').should('be.visible')
    })

    it('upsells index shows heading', () => {
      cy.visit(`${base}/admin/upsells`)
      cy.contains('Upsells').should('be.visible')
    })

    it('payment links index shows heading', () => {
      cy.visit(`${base}/admin/payment-links`)
      cy.contains('Payment Links').should('be.visible')
    })

    it('invoices index shows heading', () => {
      cy.visit(`${base}/admin/invoices`)
      cy.contains('Invoices').should('be.visible')
    })

    it('abandoned carts index shows heading', () => {
      cy.visit(`${base}/admin/abandoned-carts`)
      cy.contains('Abandoned Carts').should('be.visible')
    })

    it('countdown timers index shows heading', () => {
      cy.visit(`${base}/admin/countdown-timers`)
      cy.contains('Countdown Timers').should('be.visible')
    })

    // Create pages present a form
    it('payment link create page has a form', () => {
      cy.visit(`${base}/admin/payment-links/create`)
      cy.get('form').should('exist')
    })

    it('invoice create page has a form', () => {
      cy.visit(`${base}/admin/invoices/create`)
      cy.get('form').should('exist')
    })

    it('countdown timer create page has a form', () => {
      cy.visit(`${base}/admin/countdown-timers/create`)
      cy.get('form').should('exist')
    })
  })

  // ── Shop pages (public, no login) ─────────────────────────────────────────

  describe('Shop revenue-facing pages load', () => {
    it('checkout page renders', () => {
      cy.visit(`${base}/checkout`)
      cy.contains('Checkout').should('be.visible')
    })

    // Unknown payment-link token → graceful 404 page (no 500)
    it('payment link page responds (no server error)', () => {
      const fakeToken = 'a'.repeat(32)
      cy.request({
        url: `${base}/pay/${fakeToken}`,
        failOnStatusCode: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })

    it('malformed payment-link token does not 500', () => {
      cy.request({
        url: `${base}/pay/not-a-real-token`,
        failOnStatusCode: false,
      }).then(resp => {
        expect(resp.status).to.be.lessThan(500)
      })
    })
  })
})
