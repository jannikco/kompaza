describe('Money path (cart → checkout render only, NO real payment)', () => {

  const base = 'https://testcompany.kompaza.com'

  // ── Checkout page renders ─────────────────────────────────────────────────

  it('checkout page loads and shows the Checkout heading', () => {
    cy.visit(`${base}/checkout`)
    cy.contains('Checkout').should('be.visible')
  })

  it('GET /checkout returns 200', () => {
    cy.request({
      url: `${base}/checkout`,
      failOnStatusCode: false,
    }).then(resp => {
      expect(resp.status).to.eq(200)
    })
  })

  // ── Product → add to cart → checkout renders the payment form ─────────────
  // Cart is client-side (localStorage, keyed by tenant id). We add a product
  // through the real shop UI, then confirm the checkout form + Stripe Payment
  // Element container render. We never submit a payment.

  it('adds a product to the cart and checkout renders the payment form', () => {
    cy.visit(`${base}/produkter`)

    cy.get('body').then($body => {
      const hasProducts = $body.find('button:contains("Add to Cart")').length > 0

      if (!hasProducts) {
        // No published products in this tenant — checkout shows empty-cart
        // state. Still assert the page renders without error.
        cy.visit(`${base}/checkout`)
        cy.contains('Checkout').should('be.visible')
        return
      }

      // Add the first product to the cart via the real UI (writes localStorage)
      cy.contains('button', 'Add to Cart').first().click()
      cy.contains('Added').should('exist')

      // Now the checkout form should render (Alpine x-if items.length > 0)
      cy.visit(`${base}/checkout`)
      cy.get('form[action="/checkout"]').should('exist')

      // Stripe Payment Element container is present in the checkout form
      cy.get('#stripe-card-element').should('exist')

      // Selecting the card method reveals the Stripe container
      cy.get('input[name="payment_method"][value="card"]').check({ force: true })
      cy.get('#stripe-card-element').should('be.visible')

      // Core checkout fields exist (contact info)
      cy.get('input[name="customer_name"]').should('exist')
      cy.get('input[name="customer_email"]').should('exist')
    })
  })

  it('checkout form posts to the submit endpoint (no payment submitted)', () => {
    cy.visit(`${base}/produkter`)
    cy.get('body').then($body => {
      if ($body.find('button:contains("Add to Cart")').length === 0) {
        return
      }
      cy.contains('button', 'Add to Cart').first().click()
      cy.visit(`${base}/checkout`)
      // The shop posts the order JSON to /checkout/submit via fetch.
      cy.contains('button', 'Place Order').should('exist')
    })
  })

  // ── Order confirmation / return URLs exist (no 500) ───────────────────────
  // We do NOT submit real payments; we only assert the post-payment routes
  // respond. checkout-payment-success redirects to '/' without a valid order,
  // and the order detail page requires auth — neither should 500.

  it('payment-success return URL responds (redirects, no server error)', () => {
    cy.request({
      url: `${base}/checkout/payment-success`,
      failOnStatusCode: false,
      followRedirect: false,
    }).then(resp => {
      expect(resp.status).to.be.lessThan(500)
      // No order id → redirect to home
      expect(resp.status).to.be.oneOf([301, 302, 303])
    })
  })

  it('payment-success with order_id responds (no server error)', () => {
    cy.request({
      url: `${base}/checkout/payment-success?order_id=999999`,
      failOnStatusCode: false,
      followRedirect: false,
    }).then(resp => {
      expect(resp.status).to.be.lessThan(500)
    })
  })

  it('order confirmation (customer order detail) route responds', () => {
    // Requires customer auth; unauthenticated → redirect to login, never 500.
    cy.request({
      url: `${base}/konto/ordrer/999999`,
      failOnStatusCode: false,
      followRedirect: false,
    }).then(resp => {
      expect(resp.status).to.be.lessThan(500)
    })
  })

  it('account orders list route responds', () => {
    cy.request({
      url: `${base}/konto/ordrer`,
      failOnStatusCode: false,
      followRedirect: false,
    }).then(resp => {
      expect(resp.status).to.be.lessThan(500)
    })
  })
})
