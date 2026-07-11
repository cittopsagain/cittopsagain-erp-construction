### Quotations Module Views

This directory contains the user interface components for the Quotations module, built using ExtJS. The code is
modularized into several files to improve maintainability and readability.

#### File Overview

1. **`index.php`**
    - **Purpose**: The entry point for the Quotations view.
    - **Function**: It serves as a bridge between the server-side PHP environment and the client-side ExtJS application.
      It includes the necessary JavaScript files in the correct dependency order.
    - **Order of Inclusion**:
        1. `item-window.js` (Base component for item selection)
        2. `form.js` (Add/Edit form that depends on the item window)
        3. `grid.js` (Main grid that depends on the form)

2. **`grid.js` (`App.view.quotations.Grid`)**
    - **Purpose**: Displays the list of all quotations.
    - **Key Features**:
        - Fetches data from `/Projects/Quotations/Main/data`.
        - Provides "Add", "Edit", and "Delete" actions.
        - Triggers the `App.view.quotations.Form` when adding or editing a record.

3. **`form.js` (`App.view.quotations.Form`)**
    - **Purpose**: A modal window containing the detailed form for creating or updating a quotation.
    - **Components**:
        - **Header Information**: General details like Client, Project Type, and Contact Person.
        - **Quotation Details**: A grid for adding line items to the quotation.
        - **Terms & Conditions**: A section for specifying terms.
    - **Interaction**: It calls `App.view.quotations.ItemWindow` when a user wants to add an item to the Quotation
      Details grid.

4. **`item-window.js` (`App.view.quotations.ItemWindow`)**
    - **Purpose**: A popup window for selecting items from the inventory.
    - **Function**: Displays a searchable list of items. When an item is selected, it returns the item data back to the
      `Form` via a callback function.

#### UI Tabs (`Views/tabs/`)

To keep the interface clean, the quotation details are split into several tabs:

1. **Header.js (`App.view.quotations.tabs.Header`)**
    - Contains general information: Client, Project Name, Quotation Number, and Project Type.

2. **BOQ.js (`App.view.quotations.tabs.BOQ`)**
    - Main Bill of Quantities grid. Items added here are primary deliverables.

3. **Materials.js (`App.view.quotations.tabs.Materials`)**
    - Detailed material cost breakdown.

4. **Labor.js (`App.view.quotations.tabs.Labor`)**
    - Labor cost tracking, including number of men, days, and overtime calculations.

5. **Overhead.js (`App.view.quotations.tabs.Overhead`)**
    - Handles markups, contingencies, and other non-itemized costs.

6. **Summary.js (`App.view.quotations.tabs.Summary`)**
    - A consolidated view showing the final totals. It recalculates whenever data in other tabs change.

7. **Terms.js (`App.view.quotations.tabs.Terms`)**
    - Editable list of terms and conditions.

#### How They Work Together

1. **Initialization**: When a user navigates to the Quotations module, `index.php` is loaded. It injects the JavaScript
   components into the page.
2. **Listing**: `grid.js` initializes and renders the main table of quotations.
3. **Opening Form**: Clicking "Add Quotation" or "Edit Quotation" in the grid creates an instance of
   `App.view.quotations.Form`.
4. **Selecting Items**: Inside the form, clicking "Add Item" opens `App.view.quotations.ItemWindow`. Once the user
   selects an item, it is added to the form's detail grid.
5. **Saving**: The form handles the submission of data (both header and details) to the server via AJAX. On success, it
   tells the grid to refresh its data.
