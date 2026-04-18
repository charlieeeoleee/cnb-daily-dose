import { Link } from 'react-router-dom';
import { useCart } from '../context/CartContext';

function CartPage() {
  const { cartItems, increaseQty, decreaseQty, removeItem, cartTotal } = useCart();

  return (
    <section className="page">
      <div className="container">
        <h2 className="section-title">Your Cart</h2>

        {cartItems.length === 0 ? (
          <div className="card">
            <p>Your cart is empty.</p>
            <br />
            <Link to="/menu" className="btn">Go to Menu</Link>
          </div>
        ) : (
          <>
            {cartItems.map((item) => (
              <div className="cart-item" key={item.id}>
                <div className="flex-between">
                  <div>
                    <h3>{item.name}</h3>
                    <p>₱{item.price} x {item.quantity}</p>
                    <p><strong>Subtotal:</strong> ₱{item.price * item.quantity}</p>
                  </div>

                  <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
                    <button className="btn-secondary btn" onClick={() => decreaseQty(item.id)}>-</button>
                    <button className="btn-secondary btn" onClick={() => increaseQty(item.id)}>+</button>
                    <button className="btn" onClick={() => removeItem(item.id)}>Remove</button>
                  </div>
                </div>
              </div>
            ))}

            <div className="summary-box">
              <h3>Total: ₱{cartTotal}</h3>
              <br />
              <Link to="/checkout" className="btn">Proceed to Checkout</Link>
            </div>
          </>
        )}
      </div>
    </section>
  );
}

export default CartPage;