import { Link } from 'react-router-dom';

function HomePage() {
  return (
    <div>
      <section className="hero">
        <div className="container">
          <h1>Your Daily Coffee Fix is Here</h1>
          <p>
            Welcome to C&B Daily Dose. Order your favorite iced coffee,
            flavored drinks, and snacks in one simple ordering platform.
          </p>
          <Link to="/menu" className="cta">Order Now</Link>
        </div>
      </section>

      <section className="page">
        <div className="container">
          <h2 className="section-title">Why Choose Us?</h2>

          <div className="grid">
            <div className="card">
              <h3>Freshly Made</h3>
              <p>Every drink is prepared fresh for better taste and quality.</p>
            </div>

            <div className="card">
              <h3>Budget Friendly</h3>
              <p>Affordable coffee and snacks for students and busy people.</p>
            </div>

            <div className="card">
              <h3>Easy Ordering</h3>
              <p>Simple menu, cart, checkout, and order tracking experience.</p>
            </div>
          </div>

        </div> {/* ✅ THIS WAS MISSING */}
      </section>
    </div>
  );
}

export default HomePage;