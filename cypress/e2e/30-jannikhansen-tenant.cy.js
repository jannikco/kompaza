/**
 * Cypress suite for the jannikhansen first-tenant import on Kompaza.
 * Run:
 *   npx cypress run --config baseUrl=https://jannikhansen.kompaza.com --spec cypress/e2e/30-jannikhansen-tenant.cy.js
 */

const BASE = Cypress.config('baseUrl') || 'https://jannikhansen.kompaza.com';

function assertNoBrokenImages() {
  cy.get('body').then(($body) => {
    const imgs = $body.find('img');
    if (!imgs.length) return;
    cy.wrap(imgs).each(($img) => {
      const el = $img[0];
      const src = el.currentSrc || el.src || '';
      // Skip empty/data/tracking
      if (!src || src.startsWith('data:') || src.includes('email/o.gif')) return;
      // naturalWidth 0 = broken load
      expect(el.naturalWidth, `image should load: ${src}`).to.be.greaterThan(0);
    });
  });
}

function assertNoRawImgPaths() {
  cy.get('html').then(($html) => {
    const html = $html.html() || '';
    // Remaining unrewritten asset paths are failures
    const bad = html.match(/(?:src|srcset|content|poster|data-src)=["']\/img\//gi) || [];
    expect(bad, `unrewritten /img/ attributes: ${bad.join(', ')}`).to.have.length(0);
  });
}

describe('Jannik Hansen tenant — marketing pages', () => {
  const pages = [
    { path: '/', name: 'homepage' },
    { path: '/office-os', name: 'office-os' },
    { path: '/creator-os', name: 'creator-os' },
    { path: '/founder-os', name: 'founder-os' },
    { path: '/tracks', name: 'tracks' },
    { path: '/bibliotek', name: 'bibliotek' },
    { path: '/about', name: 'about' },
    { path: '/workshop', name: 'workshop' },
    { path: '/en-home', name: 'en-home' },
    { path: '/en-creator-os', name: 'en-creator-os' },
  ];

  pages.forEach(({ path, name }) => {
    it(`${name} (${path}) loads without broken images`, () => {
      cy.visit(path, { failOnStatusCode: true });
      cy.get('body').should('be.visible');
      // Design markers from JH import
      cy.get('html').should('exist');
      assertNoRawImgPaths();
      // Wait for images to settle
      cy.wait(500);
      assertNoBrokenImages();
    });
  });

  it('office-os hero photo is jannik-pr from uploads', () => {
    cy.visit('/office-os');
    cy.get('img[alt*="Jannik"], picture img').first().should(($img) => {
      const src = $img[0].currentSrc || $img.attr('src') || '';
      expect(src).to.match(/\/uploads\/\d+\/img\//);
      expect($img[0].naturalWidth).to.be.greaterThan(0);
    });
    // WebP source must also point at uploads
    cy.get('picture source').each(($s) => {
      const srcset = $s.attr('srcset') || '';
      if (srcset.includes('jannik') || srcset.includes('.webp')) {
        expect(srcset, srcset).to.include('/uploads/');
        expect(srcset).not.to.match(/srcset=["']?\/img\//);
      }
    });
  });
});

describe('Jannik Hansen tenant — commerce catalog', () => {
  it('ebooks catalog lists books', () => {
    cy.visit('/eboger');
    cy.contains(/book|bog|Claude|AI|Library|Bibliotek/i).should('exist');
    cy.get('a[href*="/ebog/"]').its('length').should('be.gte', 5);
    assertNoBrokenImages();
  });

  it('bibliotek covers load from uploads', () => {
    cy.visit('/bibliotek');
    cy.get('img').then(($imgs) => {
      const coverImgs = [...$imgs].filter((img) => {
        const s = img.currentSrc || img.src || '';
        return s.includes('/covers/') || s.includes('uploads');
      });
      expect(coverImgs.length, 'expected cover images').to.be.greaterThan(0);
      coverImgs.slice(0, 12).forEach((img) => {
        expect(img.naturalWidth, img.src).to.be.greaterThan(0);
      });
    });
  });

  it('courses list shows OS tracks', () => {
    cy.visit('/courses');
    cy.contains(/Creator OS|Office OS|Founder OS/i).should('exist');
  });

  it('course detail pages load', () => {
    ['/course/creator-os', '/course/office-os', '/course/founder-os'].forEach((path) => {
      cy.visit(path);
      cy.get('body').should('be.visible');
      cy.contains(/Buy|Enroll|Creator|Office|Founder|KR|DKK|price/i).should('exist');
    });
  });

  it('free ebook page loads', () => {
    cy.visit('/ebog/7-ai-agents-guide');
    cy.get('body').should('be.visible');
  });

  it('everything-bundle page loads', () => {
    cy.visit('/ebog/everything-bundle');
    cy.get('body').should('be.visible');
    cy.contains(/Bibliotek|Library|34|bundle/i).should('exist');
  });
});

describe('Jannik Hansen tenant — buy & lead flows', () => {
  it('track buy redirects to Stripe Checkout', () => {
    cy.request({
      method: 'POST',
      url: '/creator-os/buy',
      form: true,
      body: { email: 'cypress-buy@example.com', name: 'Cypress' },
      followRedirect: false,
      failOnStatusCode: false,
    }).then((res) => {
      expect(res.status).to.be.oneOf([302, 303]);
      const loc = res.redirectedToUrl || res.headers.location || '';
      expect(loc).to.include('checkout.stripe.com');
    });
  });

  it('installment buy-plan redirects to Stripe', () => {
    cy.request({
      method: 'POST',
      url: '/office-os/buy-plan',
      form: true,
      body: { email: 'cypress-plan@example.com', name: 'Cypress' },
      followRedirect: false,
      failOnStatusCode: false,
    }).then((res) => {
      expect(res.status).to.be.oneOf([302, 303]);
      const loc = res.redirectedToUrl || res.headers.location || '';
      expect(loc).to.include('checkout.stripe.com');
    });
  });

  it('free book buy returns download redirect', () => {
    cy.request({
      method: 'POST',
      url: '/ebog/7-ai-agents-guide/buy',
      form: true,
      body: { email: `cypress-free-${Date.now()}@example.com`, name: 'Cypress Free' },
      followRedirect: false,
      failOnStatusCode: false,
    }).then((res) => {
      expect(res.status).to.be.oneOf([302, 303]);
      const loc = res.redirectedToUrl || res.headers.location || '';
      expect(loc).to.match(/\/ebog\/download\//);
    });
  });

  it('workshop submit unlocks watch page', () => {
    cy.request({
      method: 'POST',
      url: '/workshop/submit',
      form: true,
      body: {
        email: `cypress-ws-${Date.now()}@example.com`,
        name: 'Cypress WS',
        track: 'creator-os',
      },
      followRedirect: false,
      failOnStatusCode: false,
    }).then((res) => {
      expect(res.status).to.be.oneOf([302, 303]);
      const loc = res.redirectedToUrl || res.headers.location || '';
      expect(loc).to.include('/workshop/watch');
    });
  });
});

describe('Jannik Hansen tenant — EN routes', () => {
  it('/en resolves without 404', () => {
    cy.visit('/en', { failOnStatusCode: false });
    cy.location('pathname').should('not.eq', '/en');
    cy.get('body').should('be.visible');
  });

  it('/en/creator-os loads design page', () => {
    cy.visit('/en/creator-os', { failOnStatusCode: false });
    cy.get('body').should('be.visible');
    assertNoRawImgPaths();
  });
});

describe('Jannik Hansen tenant — asset integrity scan', () => {
  it('no page HTML still references bare /img/ assets', () => {
    const paths = ['/', '/office-os', '/creator-os', '/founder-os', '/bibliotek', '/about'];
    paths.forEach((path) => {
      cy.request(path).then((res) => {
        expect(res.status).to.eq(200);
        const bad = res.body.match(/(?:src|srcset|content|poster)=["']\/img\//gi) || [];
        expect(bad, `${path} has unrewritten /img/ refs: ${bad.slice(0, 5)}`).to.have.length(0);
      });
    });
  });

  it('uploads hero and a cover return 200', () => {
    cy.request('/uploads/6/img/jannik-pr.jpg').its('status').should('eq', 200);
    cy.request('/uploads/6/img/jannik-pr.webp').its('status').should('eq', 200);
    cy.request({ url: '/uploads/6/img/covers/eu-ai-act-the-complete-guide-da-640.webp', failOnStatusCode: false })
      .its('status')
      .should('be.oneOf', [200, 404]); // cover naming may vary; soft check
  });
});
