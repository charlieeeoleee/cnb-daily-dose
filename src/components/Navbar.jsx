import { Link } from 'react-router-dom';
import { useCart } from '../context/CartContext';

function Navbar() {
  const { cartCount } = useCart();

  return (
    <nav className="navbar">
      <div className="container navbar-content">
        <Link to="/" className="brand">C&B Daily Dose</Link>

        <div className="nav-links">
          <Link to="/">Home</Link>
          <Link to="/menu">Menu</Link>
          <Link to="/cart">Cart ({cartCount})</Link>
          <Link to="/orders">My Orders</Link>
          <Link to="/login">Login</Link>
          <Link to="/admin">Admin</Link>
        </div>
      </div>
    </nav>
  );
}

export default Navbar;