# Laravel QuickBooks Package Architecture

## Overview
This document outlines the architecture for the E3-Development-Solutions/quickbooks Laravel package, which integrates QuickBooks Online with Laravel and Filament.

## Package Information
- **Namespace**: E3-Development-Solutions\QuickBooks
- **Composer Package**: e3-development-solutions/quickbooks
- **Laravel Version**: 12.x
- **Filament Version**: 3.x
- **QuickBooks SDK**: intuit/quickbooks-php-sdk v3

## Directory Structure
```
laravel-quickbooks-package/
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       └── InstallCommand.php
│   ├── Exceptions/
│   │   ├── QuickBooksAuthException.php
│   │   ├── QuickBooksEntityException.php
│   │   └── QuickBooksException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── QuickBooksAuthController.php
│   │   └── Middleware/
│   │       └── QuickBooksAuthenticated.php
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Invoice.php
│   │   ├── Item.php
│   │   ├── PurchaseOrder.php
│   │   └── SalesOrder.php
│   ├── Services/
│   │   ├── QuickBooksBaseService.php
│   │   ├── CustomerService.php
│   │   ├── InvoiceService.php
│   │   ├── ItemService.php
│   │   ├── PurchaseOrderService.php
│   │   ├── SalesOrderService.php
│   │   └── Traits/
│   │       ├── HasQuickBooksCustomer.php
│   │       ├── HasQuickBooksInvoice.php
│   │       ├── HasQuickBooksItem.php
│   │       ├── HasQuickBooksPurchaseOrder.php
│   │       ├── HasQuickBooksSalesOrder.php
│   │       └── HasQuickBooksAuthentication.php
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── CustomerResource.php
│   │   │   ├── InvoiceResource.php
│   │   │   ├── ItemResource.php
│   │   │   ├── PurchaseOrderResource.php
│   │   │   └── SalesOrderResource.php
│   │   └── Pages/
│   │       ├── Auth/
│   │       │   └── Connect.php
│   │       └── Dashboard.php
│   ├── Facades/
│   │   └── QuickBooks.php
│   ├── QuickBooksServiceProvider.php
│   └── config/
│       └── quickbooks.php
├── database/
│   └── migrations/
│       └── 2025_05_20_000000_add_quickbooks_fields_to_users_table.php
├── tests/
│   ├── Feature/
│   │   ├── AuthenticationTest.php
│   │   └── EntityTest.php
│   └── Unit/
│       ├── CustomerTest.php
│       ├── InvoiceTest.php
│       ├── ItemTest.php
│       ├── PurchaseOrderTest.php
│       └── SalesOrderTest.php
├── composer.json
└── README.md
```

## Component Descriptions

### Service Provider
The `QuickBooksServiceProvider` will:
- Register the package configuration
- Register the package routes
- Register the package migrations
- Register the package commands
- Register the package services
- Register the package Filament resources

### Authentication Flow
1. **OAuth2 Authentication**:
   - Implement OAuth2 flow using the QuickBooks SDK
   - Store tokens in the user model
   - Provide refresh token logic
   - Handle token expiration

2. **User Model Integration**:
   - Migration to add QuickBooks fields to users table:
     - `qb_access_token`
     - `qb_refresh_token`
     - `qb_token_expires`
     - `qb_realm_id`
   - Trait `HasQuickBooksAuthentication` for easy integration
   - Abstract class for inheritance option

### Entity Services
For each QuickBooks entity (Customer, Invoice, Item, Purchase Order, Sales Order):
1. **Service Class**:
   - Extends `QuickBooksBaseService`
   - Implements CRUD operations
   - Handles error cases and exceptions
   - Provides mapping between Laravel and QuickBooks models

2. **Model Class**:
   - Represents the QuickBooks entity in Laravel
   - Provides accessors and mutators for QuickBooks fields
   - Implements validation rules

3. **Trait**:
   - For easy integration with existing models
   - Provides relationship methods
   - Implements scopes for filtering

### Filament Integration
For each QuickBooks entity:
1. **Resource Class**:
   - Implements Filament resource for the entity
   - Provides form fields and validation
   - Implements table columns and filters
   - Provides actions for CRUD operations

2. **Pages**:
   - List page for viewing all entities
   - Create/Edit pages for managing entities
   - Detail page for viewing entity details
   - Custom pages for specific operations

### Configuration
The `config/quickbooks.php` file will include:
- Authentication settings
- API endpoints
- Entity mappings
- Customization options

### Migrations
The package will include migrations for:
- Adding QuickBooks fields to users table
- Creating tables for storing QuickBooks entity mappings (if needed)

### Facades
The `QuickBooks` facade will provide easy access to:
- Authentication methods
- Entity services
- Utility methods

## Implementation Plan

### Phase 1: Core Infrastructure
1. Set up package structure
2. Implement service provider
3. Create configuration file
4. Implement base authentication service
5. Create user model integration

### Phase 2: Entity Services
1. Implement Customer service and model
2. Implement Invoice service and model
3. Implement Item service and model
4. Implement Purchase Order service and model
5. Implement Sales Order service and model

### Phase 3: Filament Integration
1. Create base Filament resource
2. Implement entity-specific resources
3. Create custom pages for authentication and operations

### Phase 4: Testing and Documentation
1. Write unit tests for all components
2. Create comprehensive documentation
3. Provide usage examples
