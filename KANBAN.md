# 📋 Toko GlobalTeknik - Development Kanban Board

## 🔴 URGENT / High Priority

### 📦 Stock & Inventory Issues
- [ ] **[BUG] Fix stock not detected when creating sales invoice without delivery note (surat jalan)**
  - Priority: CRITICAL
  - Impact: Stock management integrity
  - Files: `TransaksiController.php`, `StockService.php`

### 💰 Sales Invoice Issues  
- [ ] **[BUG] Fix payment method not updating in sales invoice**
  - Priority: HIGH
  - Impact: Payment tracking accuracy
  - Files: `TransaksiController.php`, related views

- [ ] **[BUG] Fix search not updating in sales invoice**
  - Priority: HIGH
  - Impact: User experience
  - Files: Sales invoice views, search functionality

### 📊 Unit Conversion Feature
- [ ] **[FEATURE] Add support for large units (satuan besar) in sales invoice**
  - Priority: HIGH
  - Impact: Sales flexibility
  - Files: `TransaksiController.php`, `TransaksiItem.php`, invoice views
  
- [ ] **[FEATURE] Add support for large units (satuan besar) in delivery note**
  - Priority: HIGH
  - Impact: Delivery documentation
  - Files: `SuratJalanController.php`, delivery note views

---

## 🟡 MEDIUM Priority

### 🎨 UI/UX Improvements
- [ ] **[UI] Make search product popup larger**
  - Priority: MEDIUM
  - Impact: Better user experience
  - Files: Product search modal/popup views
  
- [ ] **[UI] Add remaining quantity display in product search**
  - Priority: MEDIUM
  - Impact: Better inventory visibility
  - Files: Product search views, Stock model

- [ ] **[UI] Change column name "ukuran" to "ukuran/type"**
  - Priority: MEDIUM
  - Impact: Clarity
  - Files: Product-related views, migrations if needed

- [ ] **[CONFIG] Change "jual" to 35%**
  - Priority: MEDIUM
  - Impact: Pricing logic
  - Files: Configuration or pricing calculation files
  - Note: Need clarification on what "jual" refers to

### 👤 Business Logic Updates
- [ ] **[FEATURE] Make salesman field optional**
  - Priority: MEDIUM
  - Impact: Flexibility in sales process
  - Files: Validation rules, database migration, sales-related forms

---

## 🟢 LOW Priority / New Features

### 📄 Receipt/Nota System Overhaul
- [ ] **[FEATURE] Create small receipt format (nota kecil)**
  - Priority: LOW
  - Impact: Print flexibility
  - Files: New view file for small receipt format
  
- [ ] **[FEATURE] Create large receipt format (nota besar)**
  - Priority: LOW
  - Impact: Print flexibility
  - Files: New view file for large receipt format

- [ ] **[FEATURE] Create temporary receipt with "belum dilunasi" note**
  - Priority: LOW
  - Impact: Payment tracking
  - Files: Temporary receipt view
  - Note: Add text "Note: nota ini belum dilunasi"

- [ ] **[ENHANCEMENT] Match receipt format with provided template**
  - Priority: LOW
  - Impact: Professional appearance
  - Files: All receipt view files
  - Dependencies: Requires the provided template reference

### 📋 Price Quotation System
- [ ] **[FEATURE] Create price quotation form (penawaran harga)**
  - Priority: LOW
  - Impact: Sales process enhancement
  - Features needed:
    - Checkbox selection for products
    - Customer assignment
    - PDF export functionality
    - Catalog-style layout
  - Files: 
    - New controller: `PenawaranHargaController.php`
    - New model: `PenawaranHarga.php` (if persistence needed)
    - New views: form and PDF template
    - Routes: web.php

### 🖼️ Branding
- [ ] **[ASSET] Update store picture/logo to GlobalTeknik**
  - Priority: LOW
  - Impact: Branding consistency
  - Files: 
    - Image assets in `public/` directory
    - Views displaying store logo
    - Configuration files with store information

---

## 📊 Backlog / Research Needed

### 🔍 Items Requiring Clarification
- [ ] **Clarify "jual jadi 35%" requirement**
  - What does this refer to? Profit margin? Markup? Discount?
  - Which module/feature?
  
- [ ] **Obtain receipt format template**
  - Need reference for "format nota dengan yang diberikan"
  - Should include all three types: small, large, temporary

---

## ✅ Completed

_No tasks completed yet_

---

## 📝 Notes & Guidelines

### Development Workflow
1. **Backlog** → Items that need clarification or are not yet ready
2. **To Do** → Ready to be worked on (requirements clear)
3. **In Progress** → Currently being developed
4. **Review** → Awaiting code review or testing
5. **Done** → Completed and deployed

### Priority Levels
- 🔴 **CRITICAL/HIGH**: Bugs affecting core functionality, data integrity
- 🟡 **MEDIUM**: UI/UX improvements, minor features
- 🟢 **LOW**: New features, cosmetic changes, nice-to-haves

### Before Starting Each Task
- [ ] Review related code and dependencies
- [ ] Check for existing similar functionality
- [ ] Create feature branch from `main`
- [ ] Write/update tests if applicable
- [ ] Update documentation

### Testing Checklist (Per Task)
- [ ] Unit tests pass
- [ ] Manual testing completed
- [ ] Edge cases considered
- [ ] Database migrations tested (up and down)
- [ ] No console errors
- [ ] Cross-browser testing (if UI change)

---

## 🎯 Sprint Planning Suggestion

### Sprint 1 (Week 1-2) - Critical Fixes
- Fix stock detection issue in sales invoice
- Fix payment method update in sales invoice
- Fix search update in sales invoice
- Add large unit support in invoice and delivery note

### Sprint 2 (Week 3-4) - UX Improvements
- Enlarge product search popup
- Add remaining quantity display
- Make salesman optional
- Change column name ukuran → ukuran/type
- Update "jual" configuration

### Sprint 3 (Week 5-6) - Receipt System
- Create small receipt format
- Create large receipt format
- Create temporary receipt with unpaid note
- Match formats with template

### Sprint 4 (Week 7-8) - New Features
- Implement price quotation system
- PDF export for quotation
- Update store branding/logo

---

## 🔗 Related Documentation
- [Multi Database Feature](MULTI_DATABASE_FEATURE.md)
- [Multi Database Setup](MULTI_DATABASE_SETUP.md)
- [User Permission](README(USERPERMISSION).md)

---

## 📞 Contact & Collaboration
- Update this board regularly as you progress
- Move tasks between sections as status changes
- Add comments/notes to tasks as needed
- Link related commits/PRs to tasks

---

**Last Updated:** October 21, 2025
**Project:** Toko GlobalTeknik
**Framework:** Laravel
**Developer:** [Your Name]

