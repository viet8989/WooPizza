# Task: Display custom tax for cart item

## Implement Task
### Display custom tax for cart item for pages:
1. mini-cart
2. cart
3. checkout
4. order-received 

[Describe what's broken or what needs to be implemented]

- **File location**: `path/to/file.js`
- **Function name**: `functionName()`
- **Expected behavior**: [what should happen]
- **Current behavior**: [what's happening now]

---

## Automated Testing Workflow

### Test Steps for Browser Agent:
1. Navigate to `https://terravivapizza.com/`
2. Auto select a product to add to cart if no item in cart
3. Open mini-cart
4. Open cart
5. Open checkout
6. Open order-received
7. Take screenshot for verification

### Expected Results:
- Display custom tax below total of every cart item
- Display custom tax below total of every cart item in checkout
- Display custom tax below total of every cart item in order-received
- Display custom tax below total of every cart item in order-details
- Display total tax above shipping fee in checkout
- Display total tax above shipping fee in order-received
- Display total tax above shipping fee in order-details 
- [Final state]

### Verification Code (optional):
```javascript
console.log('Result:', document.querySelector('.target').textContent);
```
