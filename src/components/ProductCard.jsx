import { useState } from 'react';
import ProductModal from './ProductModal';

function ProductCard({ product }) {
  const [showModal, setShowModal] = useState(false);

  return (
    <>
      <div className="card">
        <img src={product.image} alt={product.name} />
        <h3>{product.name}</h3>
        <p>{product.description}</p>
        <div className="badge">{product.category}</div>
        <p className="price">₱{product.basePrice}</p>
        <button className="btn" onClick={() => setShowModal(true)}>
          Add to Cart
        </button>
      </div>

      {showModal && (
        <ProductModal
          product={product}
          onClose={() => setShowModal(false)}
        />
      )}
    </>
  );
}

export default ProductCard;