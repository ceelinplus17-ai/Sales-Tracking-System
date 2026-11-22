// 1) Live search & status filter on the sales table
const search = document.getElementById('search');
const filterBtns = document.querySelectorAll('.filter-btn');
let currentStatus = 'all';

function filterSalesTable() {
  const q = search ? search.value.trim().toLowerCase() : '';
  let shown = 0;
  let visibleQty = 0;
  let visibleTotal = 0;

  document.querySelectorAll('#salesTable tbody tr').forEach(tr => {
    const rowStatus = tr.dataset.status || '';
    const matchesStatus = (currentStatus === 'all') || (rowStatus === currentStatus);
    const matchesText = tr.textContent.toLowerCase().includes(q);
    const match = matchesStatus && matchesText;

    tr.hidden = !match;
    if (match) {
      shown++;
      const qtyCell = tr.children[4];
      const totalCell = tr.children[6];
      if (qtyCell) visibleQty += parseInt(qtyCell.textContent.replace(/[^0-9]/g, ''), 10) || 0;
      if (totalCell) visibleTotal += parseFloat(totalCell.textContent.replace(/[^0-9.]/g, '')) || 0;
    }
  });

  const none = document.getElementById('noResults');
  if (none) none.hidden = shown !== 0;

  const qtyOut = document.getElementById('visibleQty');
  const totalOut = document.getElementById('visibleTotal');
  if (qtyOut) qtyOut.textContent = visibleQty.toLocaleString('en-PH');
  if (totalOut) {
    totalOut.textContent = '₱' + visibleTotal.toLocaleString('en-PH', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
}

if (search) {
  search.addEventListener('input', filterSalesTable);
}

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentStatus = btn.dataset.filter || 'all';
    filterSalesTable();
  });
});

// 2) Export visible sales rows to CSV
const exportBtn = document.getElementById('exportCsvBtn');
if (exportBtn) {
  exportBtn.addEventListener('click', () => {
    const table = document.getElementById('salesTable');
    if (!table) return;
    const csvRows = [];
    const headers = Array.from(table.querySelectorAll('thead th'))
      .slice(0, 8)
      .map(th => '"' + th.textContent.trim().replace(/"/g, '""') + '"');
    csvRows.push(headers.join(','));

    table.querySelectorAll('tbody tr').forEach(tr => {
      if (tr.hidden) return;
      const cols = Array.from(tr.children)
        .slice(0, 8)
        .map(td => '"' + td.textContent.trim().replace(/"/g, '""') + '"');
      csvRows.push(cols.join(','));
    });

    const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'woodstock-yarn-sales-' + new Date().toISOString().slice(0, 10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
  });
}

// 3) Confirm before deleting
document.querySelectorAll('form[data-confirm]').forEach(f => {
  f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  });
});

// 4) Add / update form: fill the price from the product, show the running total
const product = document.getElementById('product');
const qty = document.getElementById('quantity');
const price = document.getElementById('unit_price');
const out = document.getElementById('totalOut');

function showTotal() {
  if (!out) return;
  const t = (parseFloat(qty.value) || 0) * (parseFloat(price.value) || 0);
  out.textContent = '₱' + t.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
if (product) {
  product.addEventListener('change', () => {
    const p = product.selectedOptions[0].dataset.price;
    if (p) price.value = parseFloat(p).toFixed(2);
    showTotal();
  });
  qty.addEventListener('input', showTotal);
  price.addEventListener('input', showTotal);
  showTotal();
}
