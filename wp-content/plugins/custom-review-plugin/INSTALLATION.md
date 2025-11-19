# Custom Review Plugin - Quick Installation Guide

## Step-by-Step Installation

### 1. Activate the Plugin

1. Log in to your WordPress Admin panel
2. Go to **Plugins** in the left sidebar
3. Find **"Custom Review Plugin"** in the list
4. Click **"Activate"**

✅ The database table will be created automatically!

### 2. Add Your First Review

1. In WordPress Admin, look for the new **"Reviews"** menu (with a star icon ⭐)
2. Click **Reviews > Add New Review**
3. Fill in the form with a sample review (see example below)
4. Click **"Add Review"**

#### Sample Review Data (from your image):

```
Reviewer Name: Lucy EH
Location: Thành phố Hồ Chí Minh
Avatar URL: (leave empty)
Rating: ⭐⭐⭐⭐⭐ (5 stars)
Review Title: Amazing food!
Review Content:
We came for the first time today and the food was delicious! We will definitely come again. The pizza was so tasty as was the ravioli we ordered. Efficient and friendly waiting staff. Lovely ambience too.

Review Date: 2025-03-15
Reviewer Type: Couples
Contributions: 84
Source: Tripadvisor
Status: Approved
```

### 3. Add Reviews to Your Homepage

#### Option A: Using Page Editor

1. Go to **Pages > Home** (or your homepage)
2. Click **"Edit"**
3. In the content editor, add this shortcode:
   ```
   [customer_reviews]
   ```
4. Click **"Update"**
5. Visit your homepage to see the reviews!

#### Option B: Using Gutenberg Block Editor

1. Go to **Pages > Home**
2. Click **+ Add Block**
3. Search for **"Shortcode"** block
4. Paste: `[customer_reviews]`
5. Click **"Update"**

#### Option C: Add to Any Page/Post

Simply paste `[customer_reviews]` anywhere in your page content.

#### Option D: Add to Widget Area

1. Go to **Appearance > Widgets**
2. Add **"Text"** or **"HTML"** widget to desired area
3. Paste: `[customer_reviews]`
4. Click **"Save"**

### 4. Manage Reviews

Go to **Reviews > All Reviews** to:
- ✅ Approve or reject reviews
- 📝 Edit existing reviews
- 🗑️ Delete reviews
- 📊 Filter by status (Approved, Pending, Rejected)
- 🔄 Bulk actions (approve/reject/delete multiple reviews)

### Quick Tips

**Change status quickly:**
- In the reviews list, click on the status badge (e.g., "Approved")
- Select new status from dropdown
- Status updates instantly!

**Show limited reviews:**
```
[customer_reviews limit="5"]
```

**Show only approved reviews (default):**
```
[customer_reviews status="approved"]
```

## What You Get

✨ **Admin Features:**
- Full CRUD (Create, Read, Update, Delete) operations
- Quick status changes via AJAX
- Bulk actions
- Status filtering
- Form validation

✨ **Frontend Display:**
- Beautiful review cards matching your site design
- 5-star rating with green circles
- Reviewer avatars
- Vietnamese date formatting
- Responsive layout

## Need Help?

Check the full [README.md](README.md) file for:
- Detailed feature list
- Shortcode options
- Database structure
- Troubleshooting guide

---

**That's it! Your review system is ready to use!** 🎉
