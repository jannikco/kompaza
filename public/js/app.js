// Kompaza - Global JavaScript

// Initialize Quill rich text editor
function initRichEditor(containerId, hiddenFieldId, options = {}) {
    const container = document.getElementById(containerId);
    if (!container) return null;

    const toolbarFull = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'image'],
        ['blockquote', 'code-block'],
        ['clean']
    ];

    const toolbarSimple = [
        ['bold', 'italic'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link'],
        ['clean']
    ];

    const quill = new Quill('#' + containerId, {
        theme: 'snow',
        modules: {
            toolbar: options.simple ? toolbarSimple : toolbarFull
        },
        placeholder: options.placeholder || ''
    });

    if (options.height) {
        quill.root.style.minHeight = (options.height - 42) + 'px';
    }

    if (hiddenFieldId) {
        const hidden = document.getElementById(hiddenFieldId);
        if (hidden) {
            hidden.value = quill.root.innerHTML;
            quill.on('text-change', function() {
                hidden.value = quill.root.innerHTML;
            });
        }
    }

    return quill;
}

// Auto-generate slug from title
function autoSlug(sourceId, targetId) {
    const source = document.getElementById(sourceId);
    const target = document.getElementById(targetId);
    if (!source || !target) return;

    source.addEventListener('input', function () {
        if (target.dataset.manual === 'true') return;
        target.value = slugify(this.value);
    });

    target.addEventListener('input', function () {
        this.dataset.manual = 'true';
    });
}

function slugify(text) {
    return text
        .toLowerCase()
        .replace(/[æ]/g, 'ae')
        .replace(/[ø]/g, 'oe')
        .replace(/[å]/g, 'aa')
        .replace(/[ä]/g, 'ae')
        .replace(/[ö]/g, 'oe')
        .replace(/[ü]/g, 'ue')
        .replace(/[^a-z0-9\-]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// Newsletter signup form handler
function newsletterSignup(formElement, endpoint = '/api/newsletter') {
    formElement.addEventListener('submit', async function (e) {
        e.preventDefault();
        const email = this.querySelector('[name="email"]').value;
        const name = this.querySelector('[name="name"]')?.value || '';
        const csrf = this.querySelector('[name="csrf_token"]')?.value || '';
        const button = this.querySelector('button[type="submit"]');
        const originalText = button.textContent;

        button.textContent = 'Sending...';
        button.disabled = true;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, name, csrf_token: csrf })
            });
            const data = await response.json();

            if (data.success) {
                button.textContent = 'Subscribed!';
                button.classList.add('bg-green-600');
                this.reset();
            } else {
                button.textContent = data.error || 'Error';
                button.classList.add('bg-red-600');
            }
        } catch (err) {
            button.textContent = 'Error';
            button.classList.add('bg-red-600');
        }

        setTimeout(() => {
            button.textContent = originalText;
            button.disabled = false;
            button.classList.remove('bg-green-600', 'bg-red-600');
        }, 3000);
    });
}

// Format money
function formatDKK(amount) {
    return new Intl.NumberFormat('da-DK', {
        style: 'currency',
        currency: 'DKK',
        minimumFractionDigits: 2
    }).format(amount);
}

// Cart functionality
const Cart = {
    async add(productId, quantity = 1) {
        const response = await fetch('/api/cart/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        return response.json();
    },

    async update(productId, quantity) {
        const response = await fetch('/api/cart/update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity })
        });
        return response.json();
    },

    async remove(productId) {
        const response = await fetch('/api/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        });
        return response.json();
    }
};
