# Quotations Module Documentation

## Overview

The Quotations module is responsible for managing sales quotations, including project costs for Bill of Quantities (
BOQ), Materials, Labor, and Overhead. It supports creating, editing, deleting, and exporting quotations.

## Architecture (MVC)

The module follows a standard MVC pattern within the application framework:

### 1. Model (`Models/Quotations.php`)

- **Responsibility**: Handles all database interactions.
- **Key Functions**:
    - `getPaged()`: Retrieves a paginated list of quotations for the main grid.
    - `getDetails()`: Fetches line items (BOQ, Materials, Labor, Overhead) for a specific quotation.
    - `save()`: A complex transaction that saves the quotation header, details, and terms.
    - `delete()`: Removes a quotation and its associated records.

### 2. Controller (`Controllers/Main.php` & `Controllers/Export.php`)

- **Main.php**: Acts as the API endpoint for the ExtJS frontend. It decodes JSON requests and interfaces with the
  `Quotations` model.
- **Export.php**: Handles generating PDF/Excel exports of quotations (if implemented).

### 3. View (`Views/`)

Built with ExtJS 6+, the UI is highly modular:

- **`index.php`**: The entry point that loads all required JavaScript files.
- **`grid.js`**: The main dashboard showing all quotations.
- **`form.js`**: The primary container for adding/editing a quotation. It uses a `TabPanel` to organize different
  aspects of the quotation.
- **`item-window.js`**: A reusable selection window for picking items from the inventory.

#### UI Tabs (`Views/tabs/`)

- **Header**: General project and client information.
- **BOQ**: Bill of Quantities - the main items being quoted.
- **Materials**: Detailed breakdown of materials required.
- **Labor**: Labor cost breakdown (men, days, hours, OT).
- **Overhead**: Additional costs (Contingency, VAT, Profit, etc.).
- **Summary**: A read-only view that aggregates costs from all tabs.
- **Terms**: Terms and conditions for the quotation.

## Data Flow

1. **Loading**: `grid.js` requests data from `Main/data` -> `Models/Quotations->getPaged()`.
2. **Editing**: Opening the form triggers multiple requests to `Main/details` and `Main/terms` to populate the stores
   for each tab.
3. **Saving**: `form.js` gathers data from all tab stores, serializes it into JSON, and POSTs it to `Main/save`. The
   controller passes this to `Models/Quotations->save()`, which performs the database updates inside a transaction.

## Calculations

- Costs are calculated client-side in the ExtJS stores/views for immediate feedback.
- The `Summary` tab listens to changes in the Detail, Material, Labor, and Overhead stores to update totals in
  real-time.
- Grand Total = Sub-total (BOQ/Materials/Labor) + Overhead.
