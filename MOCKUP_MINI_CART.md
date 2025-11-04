# Mini-Cart Mockup Layout

This document describes the desired mini-cart layout based on the user's mockup screenshot.

## Current Mini-Cart Layout Analysis

```
┌─────────────────────────────────────────────┐
│  🔒 Your cart                               │ ← Black header
├─────────────────────────────────────────────┤
│                                             │
│  Pizza sea food Pesto                       │ ← Product title
│  ┌─────┐                                    │
│  │     │  Add    Vietnamese Sausage 50,000₫ │ ← Toppings list
│  │ 🍕  │         Vietnamese Sausage 50,000₫ │
│  │     │         Vietnamese Sausage 50,000₫ │
│  └─────┘         Vietnamese Sausage 50,000₫ │
│  [-] 1 [+]              300.000 VND    ✏️ ❌ │ ← Qty + Price + Actions
│                                             │
│  🍕 Pizza Spicy ✗ Pizza Teraviva           │ ← Paired pizza
│  ┌─────┐                                    │
│  │ 🍕  │  PIZZA SPICY 165.000 VND          │
│  └─────┘  Add    Onion 50.000 VND          │
│           Black Olive 60.000 VND            │
│           PIZZA TERAVIVA 168.000 VND        │
│           Add    Parma Ham 50.000 VND      │
│                                             │
├─────────────────────────────────────────────┤
│  [Shipping] [Pick up]                       │ ← Delivery tabs
│                                             │
│  Subtotal              350.000 VND          │
│  Shipping               50.000 VND          │
│  VAT(8%)                53.280 VND          │
│  Total                 719.280 VND          │ ← Red bold
│                                             │
│  ┌───────────────────────────────────────┐ │
│  │    Process your order                 │ │ ← Red button
│  └───────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

## Key Elements

### 1. Header Section
- **Background**: Black (#000)
- **Text**: White with lock icon (🔒)
- **Content**: "Your cart"
- **Styling**:
  - Padding: 15px 20px
  - Font-size: 18px
  - Font-weight: 600

### 2. Cart Items

#### Product Title
- **Font-size**: 16px
- **Font-weight**: 700 (bold)
- **Color**: Black (#000)
- **Layout**: Displays paired pizza with X separator for split pizzas
- **Icon**: Pizza icon for paired pizzas

#### Product Image
- **Size**: 90px × 90px (approximately)
- **Position**: Left side of item
- **Styling**:
  - Border-radius: 6px
  - Box-shadow: subtle shadow for depth
  - Object-fit: cover

#### Toppings Display
- **"Add" Label**: Shows only on first topping
- **Layout**:
  - Label on left (min-width: 35px)
  - Topping name in middle (flex: 1)
  - Price on right (aligned right)
- **Spacing**: Subsequent toppings indented with spacer
- **Font-size**: 13px
- **Colors**:
  - Label: #777 (medium gray)
  - Topping name: #555 (dark gray)
  - Price: #000 (black), bold

#### Pizza Half Sections (Paired Pizzas)
- **Pizza Name**:
  - Uppercase
  - Font-weight: 700
  - Font-size: 13px
  - Letter-spacing: 0.3px
- **Price Display**: Shown next to pizza name
- **Toppings**: Independent list for each half
- **Separation**: Clear visual separation between left and right halves

#### Quantity Controls
- **Layout**: Horizontal with [-] [1] [+] buttons
- **Buttons**:
  - Circular (32px diameter)
  - Border: 1px solid #ddd
  - Hover: Background changes to #cd0000 (red)
- **Input**:
  - Width: 50px
  - Height: 32px
  - Center-aligned text
  - Font-weight: 600

#### Item Price
- **Position**: Right side, aligned with quantity
- **Format**: "2 × 220.000 ₫"
- **Font-weight**: 600
- **Color**: Black (#000)

#### Action Icons
- **Edit Icon**: Pencil (✏️)
- **Delete Icon**: X (❌)
- **Position**: Top-right corner
- **Styling**:
  - 32px × 32px
  - Border-radius: 4px
  - Background: #f5f5f5
  - Hover: Background changes to #cd0000, color to white

### 3. Delivery Options
- **Tabs**: "Shipping" and "Pick up"
- **Active Tab**: Red background (#cd0000)
- **Inactive Tab**: White/light background
- **Font-weight**: 600
- **Border-radius**: 6px (top corners)

### 4. Totals Section

#### Subtotal, Shipping, VAT
- **Layout**: Two-column (label left, amount right)
- **Font-size**: 14px
- **Color**: #666 (gray)
- **Spacing**: 8px padding top/bottom

#### Total
- **Border-top**: 2px solid #000 (or 1px solid #ddd)
- **Font-size**: 18px
- **Font-weight**: 700 (bold)
- **Color**:
  - Label: #000 (black)
  - Amount: #cd0000 (red)
- **Spacing**: 12px padding-top, 8px margin-top

### 5. Checkout Button
- **Text**: "Process your order" or "Checkout"
- **Background**: #cd0000 (red)
- **Text Color**: White (#fff)
- **Font-size**: 16px
- **Font-weight**: 600
- **Padding**: 14px 20px
- **Border-radius**: 6px
- **Width**: 100% (full width)
- **Hover Effect**:
  - Background: #a00000 (darker red)
  - Transform: translateY(-2px) (lift effect)
  - Box-shadow: 0 4px 12px rgba(205, 0, 0, 0.3)

## Layout Structure (HTML)

```html
<div class="woocommerce-mini-cart-item">
  <a class="remove">×</a>

  <div class="mini-cart-title-wrapper">
    <img class="paired-pizza-icon" /> <!-- If paired -->
    <h3 class="mini-cart-product-title">Pizza Name</h3>
  </div>

  <div class="mini-cart-item-content">
    <div class="mini-cart-item-image">
      <img src="..." />
    </div>

    <div class="mini-cart-item-details">
      <!-- Whole Pizza Toppings -->
      <div class="pizza-topping-row">
        <span class="topping-label">Add</span>
        <span class="topping-details">
          <span class="topping-name">Vietnamese Sausage</span>
          <span class="topping-price">50.000₫</span>
        </span>
      </div>

      <!-- OR Paired Pizza -->
      <div class="pizza-half-section">
        <div class="pizza-half-title">PIZZA SPICY 165.000 VND</div>
        <div class="pizza-topping-row">
          <span class="topping-label">Add</span>
          <span class="topping-details">
            <span class="topping-name">Onion</span>
            <span class="topping-price">50.000₫</span>
          </span>
        </div>
      </div>

      <!-- Quantity and Price -->
      <div class="quantity">2 × 220.000 ₫</div>
    </div>
  </div>
</div>
```

## Design Specifications

### Colors
- **Primary Brand Red**: #cd0000
- **Dark Red (hover)**: #a00000
- **Black**: #000
- **Dark Gray**: #555
- **Medium Gray**: #777
- **Light Gray**: #ccc
- **Border Gray**: #ddd, #efefef
- **Background Gray**: #f5f5f5

### Typography
- **Font Family**: "Bricolage Grotesque", sans-serif (or default system font)
- **Product Title**: 16px, bold (700)
- **Pizza Half Title**: 13px, bold (700), uppercase
- **Toppings**: 13px, regular (400) for name, bold (600) for price
- **Totals**: 14px for line items, 18px for total
- **Buttons**: 16px, semi-bold (600)

### Spacing
- **Container Padding**: 20px 0 (top/bottom)
- **Item Gap**: 12px (between image and details)
- **Topping Margin**: 3px 0 (between each topping)
- **Section Margin**: 8px 0 12px 0 (pizza half sections)
- **Border**: 1px solid #efefef (between items)

### Interactive Elements
- **Hover Transitions**: all 0.3s ease
- **Button Hover**:
  - Scale: 1.05 or translateY(-2px)
  - Shadow: 0 4px 12px rgba(205, 0, 0, 0.3)
- **Icon Hover**:
  - Background: #cd0000
  - Color: #fff

## Responsive Behavior

### Mobile (< 550px)
- Maintain two-column layout (image + details)
- Reduce image size if needed
- Stack elements vertically
- Ensure touch targets are at least 44px × 44px

### Tablet (550px - 849px)
- Full layout as designed
- Slightly smaller font sizes if needed

### Desktop (> 850px)
- Full layout as designed
- Cart sidebar width: 320-400px

## Notes
- All prices should use Vietnamese Dong format (₫ symbol)
- Number formatting: Use dots as thousand separators (e.g., 50.000 ₫)
- Images should be lazy-loaded for performance
- Support for both whole pizzas and paired (half-and-half) pizzas
- "Add" label should only appear on the first topping in each section
- Subsequent toppings should be indented with a spacer element

---

**Created**: 2025-11-04
**Version**: 1.0
**Status**: Reference Document for Implementation
