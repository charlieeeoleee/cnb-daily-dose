import { createContext, useContext, useMemo, useState } from 'react';

const CartContext = createContext();

function areSameCustomization(a, b) {
  return (
    a.id === b.id &&
    a.size === b.size &&
    a.sweetness === b.sweetness &&
    a.ice === b.ice &&
    JSON.stringify(a.addons) === JSON.stringify(b.addons) &&
    a.notes === b.notes
  );
}

export function CartProvider({ children }) {
  const [cartItems, setCartItems] = useState([]);

  const addToCart = (customizedProduct) => {
    setCartItems((prev) => {
      const existing = prev.find((item) => areSameCustomization(item, customizedProduct));

      if (existing) {
        return prev.map((item) =>
          areSameCustomization(item, customizedProduct)
            ? { ...item, quantity: item.quantity + customizedProduct.quantity }
            : item
        );
      }

      return [...prev, customizedProduct];
    });
  };

  const increaseQty = (cartId) => {
    setCartItems((prev) =>
      prev.map((item) =>
        item.cartId === cartId ? { ...item, quantity: item.quantity + 1 } : item
      )
    );
  };

  const decreaseQty = (cartId) => {
    setCartItems((prev) =>
      prev
        .map((item) =>
          item.cartId === cartId ? { ...item, quantity: item.quantity - 1 } : item
        )
        .filter((item) => item.quantity > 0)
    );
  };

  const removeItem = (cartId) => {
    setCartItems((prev) => prev.filter((item) => item.cartId !== cartId));
  };

  const clearCart = () => {
    setCartItems([]);
  };

  const cartCount = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.quantity, 0),
    [cartItems]
  );

  const cartTotal = useMemo(
    () => cartItems.reduce((sum, item) => sum + item.totalPrice * item.quantity, 0),
    [cartItems]
  );

  return (
    <CartContext.Provider
      value={{
        cartItems,
        addToCart,
        increaseQty,
        decreaseQty,
        removeItem,
        clearCart,
        cartCount,
        cartTotal
      }}
    >
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  return useContext(CartContext);
}