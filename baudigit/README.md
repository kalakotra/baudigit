# Baudigit Project

## Overview
Baudigit is a web application designed to manage offers and invoices. This project includes classes and templates for handling the display and logic of both offers and invoices.

## Project Structure
The project is organized into the following main directories:

- **app/src/Pages**: Contains PHP classes for managing pages.
  - `InvoicePage.php`: Defines the `InvoicePage` class, extending the functionality of the `OfferPage` class.
  - `InvoicePageController.php`: Manages the logic for the `InvoicePage`.
  - `OfferPage.php`: Defines the `OfferPage` class.
  - `OfferPageController.php`: Manages the logic for the `OfferPage`.

- **themes/[your-theme-name]/templates/Layout**: Contains layout templates for rendering pages.
  - `InvoicePage.ss`: Layout template for the `InvoicePage`.
  - `OfferPage.ss`: Layout template for the `OfferPage`.

## Setup Instructions
1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Ensure you have the necessary dependencies installed.
4. Configure your web server to point to the `app` directory.
5. Access the application through your web browser.

## Class Descriptions
- **InvoicePage**: This class is responsible for the structure and functionality of the invoice page. It inherits properties and methods from the `OfferPage` class.
  
- **InvoicePageController**: This class handles the requests and rendering logic for the `InvoicePage`. It includes methods to manage user interactions and data processing.

- **OfferPage**: This class serves as a reference for the `InvoicePage`, providing a base structure for offer-related functionalities.

- **OfferPageController**: This class serves as a reference for the `InvoicePageController`, managing the logic for the offer page.

## Template Descriptions
- **InvoicePage.ss**: This template defines how the invoice page is rendered in the front end, including layout and design elements.

- **OfferPage.ss**: This template serves as a reference for the layout structure of the `InvoicePage`, ensuring consistency in design across the application.