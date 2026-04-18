import ProductCard from '../components/ProductCard';
import { products } from '../data/mockData';

function MenuPage() {
  return (
    <section className="page">
      <div className="container">
        <h2 className="section-title">Our Menu</h2>
        <div className="grid">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </div>
    </section>
  );
}

export default MenuPage;