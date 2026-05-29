document.addEventListener('DOMContentLoaded', () => {
  const orderType = document.querySelector('[data-delivery-toggle]');
  const address = document.querySelector('[data-delivery-address]');

  function toggleAddress() {
    if (!orderType || !address) return;
    address.style.display = orderType.value === 'Delivery' ? 'grid' : 'none';
  }

  if (orderType) {
    orderType.addEventListener('change', toggleAddress);
    toggleAddress();
  }
});

