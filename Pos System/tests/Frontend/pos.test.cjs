const assert = require('node:assert/strict');
const { test } = require('node:test');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '../..');
const terminal = fs.readFileSync(path.join(root, 'resources/views/pos/index.blade.php'), 'utf8');
const history = fs.readFileSync(path.join(root, 'resources/views/pos/history.blade.php'), 'utf8');
const attack = '<img src=x onerror="window.reviewMarker=1">';

function between(source, start, end) {
    const offset = source.indexOf(start);
    assert.ok(offset >= 0);
    const limit = source.indexOf(end, offset + start.length);
    assert.ok(limit > offset);
    return source.slice(offset, limit).replace(/\{\{[\s\S]*?\}\}/g, match => match.includes('loyalty_redemption_value') ? '1' : 'Rs.');
}

function context(functions, data = {}) {
    const nodes = new Map();
    const element = () => ({
        innerHTML: '', textContent: '', value: '', style: {}, dataset: {}, children: [],
        classList: { add() {}, remove() {} }, addEventListener() {},
        querySelectorAll() { return []; }, appendChild(child) { this.children.push(child); },
    });
    const sandbox = {
        document: {
            getElementById(id) {
                if (!nodes.has(id)) nodes.set(id, element());
                return nodes.get(id);
            },
            createElement: element,
        },
        showToast() {}, alert() { throw new Error('Unexpected alert'); },
        ...data,
    };
    vm.createContext(sandbox);
    vm.runInContext(functions.join('\n'), sandbox);
    return { sandbox, nodes };
}

const escape = between(terminal, 'function escapeHtml(', 'function searchProducts(');

test('parked-sale notes and customer names render as text', async () => {
    const { sandbox, nodes } = context([
        escape, between(terminal, 'async function openHeldSalesModal()', 'function closeHeldSalesModal()'),
    ], { fetch: async () => ({ json: async () => [{ id: 1, reference_note: attack,
        customer: { name: attack }, total_amount: 100, created_at: '2026-10-12T12:00:00Z' }] }) });
    await sandbox.openHeldSalesModal();
    const html = nodes.get('heldSalesList').innerHTML;
    assert.ok(!html.includes(attack));
    assert.equal((html.match(/&lt;img/g) || []).length, 2);
});

test('product cards escape names and attach clicks without inline product JSON', async () => {
    const { sandbox, nodes } = context([
        escape, between(terminal, 'function searchProducts(', 'function addToCart('),
    ], { fetch: async () => ({ json: async () => [{ id: 1, name: attack, barcode: attack,
        unit: attack, stock_quantity: 5, selling_price: 100 }] }) });
    sandbox.searchProducts('');
    await new Promise(resolve => setImmediate(resolve));
    const html = nodes.get('productGrid').innerHTML;
    assert.ok(!html.includes(attack));
    assert.ok(!html.includes('onclick="addToCart('));
    assert.ok(html.includes('data-product-index="0"'));
    assert.equal((html.match(/&lt;img/g) || []).length, 3);
});

test('cart item names cannot insert an HTML element', () => {
    const { sandbox, nodes } = context([
        escape, between(terminal, 'function renderCart()', 'function changeQty('),
    ], { cart: [{ name: attack, unit_price: 100, qty: 1, subtotal: 100 }], recalc() {} });
    sandbox.renderCart();
    assert.ok(!nodes.get('cartItems').innerHTML.includes(attack));
    assert.ok(nodes.get('cartItems').innerHTML.includes('&lt;img'));
});

test('refund modal escapes stored product names', () => {
    const { sandbox, nodes } = context([
        escape, between(history, 'function openRefundModal(', 'function closeRefundModal()'),
    ]);
    sandbox.document.getElementById('sale-items-1').textContent = JSON.stringify([
        { id: 1, product_name: attack, quantity: 2, refunded_quantity: 0, unit_price: 100 },
    ]);
    sandbox.openRefundModal({ getAttribute(name) { return name === 'data-sale-id' ? '1' : 'INV-00001'; } });
    const html = nodes.get('refundItemsTable').children[0].innerHTML;
    assert.ok(!html.includes(attack));
    assert.ok(html.includes('&lt;img'));
});

test('displayed tax and loyalty amount agree with checkout and cap redemption', () => {
    const { sandbox, nodes } = context([between(terminal, 'function recalc()', 'function calcChange()')], {
        cart: [{ subtotal: 100 }], calcChange() {},
    });
    for (const [id, value] of Object.entries({ discountType: 'fixed', discountValue: '0', taxPercent: '10', pointsRedeemed: '10' })) {
        sandbox.document.getElementById(id).value = value;
    }
    sandbox.document.getElementById('availPoints').textContent = '1000';
    sandbox.recalc();
    assert.equal(nodes.get('totalDisp').textContent, '99.00');
    assert.equal(nodes.get('taxDisp').textContent, '+ 9.00');
    nodes.get('pointsRedeemed').value = '1000';
    sandbox.recalc();
    assert.equal(nodes.get('pointsRedeemed').value, 100);
    assert.equal(nodes.get('totalDisp').textContent, '0.00');
});
