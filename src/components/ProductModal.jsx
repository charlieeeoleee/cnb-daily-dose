import { useMemo, useState } from 'react';
import { useCart } from '../context/CartContext';
import {
  sizeOptions,
  sweetnessOptions,
  iceOptions,
  addonOptions
} from '../data/mockData';

function ProductModal({ product, onClose }) {
  const { addToCart } = useCart();

  const isDrink = product.type === 'drink';

  const [quantity, setQuantity] = useState(1);
  const [size, setSize] = useState(isDrink ? sizeOptions[0].label : 'Regular');
  const [sweetness, setSweetness] = useState('100%');
  const [ice, setIce] = useState('Normal Ice');
  const [addons, setAddons] = useState([]);
  const [notes, setNotes] = useState('');

  const addonTotal = useMemo(() => {
    return addons.reduce((sum, addon) => sum + addon.price, 0);
  }, [addons]);

  const sizePrice = useMemo(() => {
    if (!isDrink) return 0;
    const found = sizeOptions.find((option) => option.label === size);
    return found ? found.price : 0;
  }, [size, isDrink]);

  const singleItemTotal = product.basePrice + sizePrice + addonTotal;
  const overallTotal = singleItemTotal * quantity;

  const toggleAddon = (addon) => {
    const exists = addons.find((item) => item.label === addon.label);

    if (exists) {
      setAddons((prev) => prev.filter((item) => item.label !== addon.label));
    } else {
      setAddons((prev) => [...prev, addon]);
    }
  };

  const handleAddToCart = () => {
    const customizedProduct = {
      cartId: `${product.id}-${Date.now()}`,
      id: product.id,
      name: product.name,
      image: product.image,
      basePrice: product.basePrice,
      totalPrice: singleItemTotal,
      quantity,
      size: isDrink ? size : 'Regular',
      sweetness: isDrink ? sweetness : 'N/A',
      ice: isDrink ? ice : 'N/A',
      addons,
      notes,
      category: product.category
    };

    addToCart(customizedProduct);
    onClose();
  };

  return (
    <div className="modal-backdrop">
      <div className="modal-card">
        <button className="modal-close" onClick={onClose}>×</button>

        <div className="modal-product-header">
          <img src={product.image} alt={product.name} />
          <div>
            <h2>{product.name}</h2>
            <p>{product.description}</p>
            <p className="price">Base Price: ₱{product.basePrice}</p>
          </div>
        </div>

        <div className="form-group">
          <label>Quantity</label>
          <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
            <button
              type="button"
              className="btn-secondary btn"
              onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
            >
              -
            </button>
            <strong>{quantity}</strong>
            <button
              type="button"
              className="btn-secondary btn"
              onClick={() => setQuantity((prev) => prev + 1)}
            >
              +
            </button>
          </div>
        </div>

        {isDrink && (
          <>
            <div className="form-group">
              <label>Size</label>
              <select value={size} onChange={(e) => setSize(e.target.value)}>
                {sizeOptions.map((option) => (
                  <option key={option.label} value={option.label}>
                    {option.label} {option.price > 0 ? `( +₱${option.price} )` : ''}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Sweetness Level</label>
              <select value={sweetness} onChange={(e) => setSweetness(e.target.value)}>
                {sweetnessOptions.map((option) => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Ice Level</label>
              <select value={ice} onChange={(e) => setIce(e.target.value)}>
                {iceOptions.map((option) => (
                  <option key={option} value={option}>
                    {option}
                  </option>
                ))}
              </select>
            </div>
          </>
        )}

        <div className="form-group">
          <label>Add-ons</label>
          <div className="addons-list">
            {addonOptions.map((addon) => {
              const checked = addons.some((item) => item.label === addon.label);

              return (
                <label key={addon.label} className="addon-item">
                    <div className="addon-item-content">
                     <input
                      type="checkbox"
                      checked={checked}
                      onChange={() => toggleAddon(addon)}
                    />
                     <span>{addon.label} (+₱{addon.price})</span>
                    </div>
                </label>
              );
            })}
          </div>
        </div>

        <div className="form-group">
          <label>Special Notes</label>
          <textarea
            rows="3"
            placeholder="Example: Less sweet, no straw, extra napkin..."
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
          />
        </div>

        <div className="summary-box">
          <p><strong>Single Item Price:</strong> ₱{singleItemTotal}</p>
          <p><strong>Quantity:</strong> {quantity}</p>
          <p><strong>Total:</strong> ₱{overallTotal}</p>
        </div>

        <div style={{ marginTop: '16px', display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
          <button className="btn" onClick={handleAddToCart}>
            Add to Cart
          </button>
          <button className="btn btn-secondary" onClick={onClose}>
            Cancel
          </button>
        </div>
      </div>
    </div>
  );
}

export default ProductModal;