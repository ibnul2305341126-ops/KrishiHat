# KrishiHat frontend inventory

Baseline captured before redesign changes on 2026-10-17.

## Frontend stack and shared structure

| Area | Current file | Purpose and dependencies |
|---|---|---|
| Build entrypoints | `vite.config.js` | Laravel Vite compiles `resources/css/app.css` and `resources/js/app.js`; refresh enabled. |
| CSS entrypoint | `resources/css/app.css` | Global styles and responsive floating chat panel. Bootstrap CSS is imported from the JavaScript entrypoint. |
| JavaScript entrypoint | `resources/js/app.js` | Imports Axios setup, Bootstrap 5 JS; controls the homepage chat widget and its honest unavailable-service response. |
| Axios setup | `resources/js/bootstrap.js` | Configures Axios and `X-Requested-With`; imported by `app.js`. |
| Blade layouts | None | Each page currently owns its HTML document/head and repeats styling/navigation. |
| Reusable Blade components/partials | None | Repeated navigation, product cards, and alerts are currently inline. Chat markup is inline in the homepage. |
| Frontend dependencies | `package.json` | Bootstrap 5.3, Vite 6, Laravel Vite plugin, Axios. No Tailwind, Alpine, React, or Vue dependency found. |

## Existing pages and components

| Page/component | Current file | Purpose and dependencies |
|---|---|---|
| Marketplace homepage | `resources/views/welcome.blade.php` | Home/search/category filtering, product catalog cards, add-to-cart, guest/auth navigation, buyer order link, farmer dashboard link, flash states, footer, and chat widget. Uses `products`, `categories`, request filters, session cart, auth, named routes, Vite CSS/JS. |
| Preserved homepage backup | `resources/views/welcome.blade.backup.php` | Existing user backup; not rendered by a route. Preserve unchanged. |
| Product details | `resources/views/products/show.blade.php` | Product image/details, category, price/unit, stock, flash states, add-to-cart. Receives route-bound `Product`; currently loads Bootstrap from CDN. |
| Create product | `resources/views/products/create.blade.php` | Farmer product form; posts to `products.store`; validation errors; currently loads Bootstrap from CDN. |
| Edit product | `resources/views/products/edit.blade.php` | Farmer product edit form; PUT to `products.update`; validation errors; currently loads Bootstrap from CDN. |
| Farmer dashboard | `resources/views/farmer/dashboard.blade.php` | Farmer-owned product count and product table/edit actions, add product, home/logout and flash/validation states. Receives authenticated farmer's products. |
| Buyer orders list | `resources/views/orders/index.blade.php` | Authenticated buyer's orders and order detail links; receives buyer-scoped orders. |
| Order detail/tracking | `resources/views/orders/show.blade.php` | Order summary, status, delivery information and items; receives authorized order. |
| Cart | `resources/views/cart.blade.php` | Session cart lines, quantities, update/remove actions and checkout link; receives cart and matching products. |
| Checkout | `resources/views/checkout.blade.php` | Buyer delivery address/phone and order placement; receives products, cart and computed total. |
| Login | `resources/views/auth/login.blade.php` | Authentication form, validation feedback and login submission. |
| Registration | `resources/views/auth/register.blade.php` | Account form including buyer/farmer role, validation feedback and registration submission. |
| Chat launcher/panel (inline component) | Homepage markup in `welcome.blade.php`; styles in `resources/css/app.css`; behavior in `resources/js/app.js` | Accessible floating chat, message log, input, loading/error placeholders. README confirms no FastAPI endpoint/configuration; submission stays local and reports that it was not sent. |

## Routes and controller/backend dependencies

| Route name(s) | URL and handler | View/functionality |
|---|---|---|
| `home` | `GET /`, closure in `routes/web.php` | Queries products by optional name search/category; builds distinct categories; renders homepage. |
| `register`, `register.store` | `GET/POST /register`, `AuthController` | Registration page and account creation/login. |
| `login`, `login.store`, `logout` | `GET/POST /login`, `POST /logout`, `AuthController` | Authentication and logout. |
| `products.show` | `GET /products/{product}`, closure | Product details. |
| `cart.add`, `cart.update`, `cart.remove`, `cart.index` | POST/PUT/DELETE/GET `/cart...` | Session cart with stock checks and cart management; update/remove use `CartController`. |
| `checkout.index`, `checkout.store` | GET/POST `/checkout`, `CheckoutController` (auth) | Buyer-only checkout; validates stock and farmer association, creates order/items transactionally, decrements stock. |
| `farmer.dashboard` | `GET /farmer/dashboard`, `FarmerDashboardController@index` (auth) | Authenticated farmer dashboard. Controller scopes products to current farmer. |
| `products.create`, `products.store`, `products.edit`, `products.update` | `/farmer/products...`, `ProductController` (auth) | Farmer product management with ownership checks. |
| `orders.index`, `orders.show` | `GET /my-orders...`, `OrderController` (auth) | Buyer-owned order list and tracking/detail. |

No buyer dashboard or profile route/view currently exists. No route should be invented to imply either feature.

## Data relationships used by frontend

`Product` belongs to a `User` farmer and exposes name, category, description, price, unit, stock, and image. `User` has products. `Order` belongs to buyer and has order items; each `OrderItem` relates to product and farmer and records quantity, unit price, and subtotal. The UI may show seller names through the existing product farmer relation where populated. Product filtering currently supports name and exact category only; no price-filter route/query exists.

## Baseline change policy

Retain all listed views, the homepage backup, named routes, backend workflows, and chat behavior. Any consolidation or new shared Blade partial/layout should be recorded in the after inventory; no baseline component is removed unless a specific replacement and reason are documented.

## After redesign inventory

| Current page/component | Current file | Purpose and dependencies after redesign |
|---|---|---|
| Shared marketplace/account shell | `resources/views/layouts/app.blade.php` | New shared HTML shell with Bootstrap navigation, role-aware account links, cart count, footer, Vite assets, and global help chat. Used by marketplace, farmer product management, product details, cart, checkout, and buyer order pages. |
| Shared product card | `resources/views/components/product-card.blade.php` | New reusable real-data listing card with linked image/details, category, price/unit, stock, seller where related, and existing add-to-cart route. Included by homepage. |
| Marketplace homepage | `resources/views/welcome.blade.php` | Reworked marketplace-first hero with local `public/alu.jpg`, search, database categories/listings, buying/selling explainer, farmer call-to-action, empty/search states. Uses shared shell/card. |
| Farmer dashboard | `resources/views/farmer/dashboard.blade.php` | Reworked responsive summary and product management table; only displays product count and existing marketplace actions supported by controller/routes. |
| Product detail | `resources/views/products/show.blade.php` | Reworked detail with breadcrumbs, stock, seller when available, and quantity field capped to current stock. Uses shared shell. |
| Farmer product create/edit | `resources/views/products/create.blade.php`, `resources/views/products/edit.blade.php` | Retained forms, validation messages, existing named routes and fields; moved to shared shell and updated presentation. |
| Cart and checkout | `resources/views/cart.blade.php`, `resources/views/checkout.blade.php` | Retained session cart, stock-constrained quantity updates, checkout, and order submission; moved to shared shell and restyled. |
| Buyer orders list/detail | `resources/views/orders/index.blade.php`, `resources/views/orders/show.blade.php` | Existing buyer order history and tracking presented in shared shell; no buyer dashboard route was introduced. |
| Authentication | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php` | Existing authentication pages remain as they were; custom standalone presentation and password interactions remain intact. |
| Help chat | Markup now in `resources/views/layouts/app.blade.php`; existing styles/behavior in `resources/css/app.css` and `resources/js/app.js` | Same accessible floating local-only help experience now available across pages. README confirms no FastAPI service or endpoint exists; unavailable response remains honest. |
| Styles/build | `resources/css/app.css`, `resources/js/app.js`, `resources/js/bootstrap.js`, `vite.config.js`, `package.json` | Shared custom responsive Bootstrap styling remains built through existing Vite entrypoints. No Tailwind, Alpine, React, or Vue introduced. |
| Preserved backup | `resources/views/welcome.blade.backup.php` | Preserved unchanged and still not routed. |

## Change ledger

### Modified for redesign

`routes/web.php` (eager-load seller and sort marketplace listings by latest; existing add-to-cart route now validates and respects the requested quantity), `resources/css/app.css`, `resources/js/app.js` (allow both help entry points to open the same panel), `resources/views/welcome.blade.php`, `resources/views/farmer/dashboard.blade.php`, `resources/views/products/show.blade.php`, `resources/views/products/create.blade.php`, `resources/views/products/edit.blade.php`, `resources/views/cart.blade.php`, `resources/views/checkout.blade.php`, `resources/views/orders/index.blade.php`, and `resources/views/orders/show.blade.php`.

### Added for redesign

`resources/views/layouts/app.blade.php` and `resources/views/components/product-card.blade.php`.

### Removed

No page, route, backend component, backup, or working frontend component was removed. The homepage's former inline product-card markup was replaced with the reusable card partial. Its former inline chat markup was relocated to the shared layout so it appears on the shared-shell pages.

### Known limits carried forward

There is no actual featured-product flag, price filter, farmer orders page, profile page, or buyer dashboard route in the current schema/routes, so none was invented. Marketplace listing cards use real current products sorted newest first. Authentication views continue to use their existing standalone templates. Product categories are limited to categories present in the database.
