# BYTEROVER MCP HANDBOOK
# Bayswater Laravel Course Fee Calculator

*Generated: 2025-01-27*
*Project Type: Laravel Web Application*
*Purpose: Course fee calculation and quotation management system*

---

## Layer 1: System Overview

### Purpose
Bayswater Laravel application is a comprehensive course fee calculator and quotation management system for Bayswater Education. The system manages educational courses, accommodations, pricing, and generates detailed quotations for students and agents.

### Tech Stack
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Authentication**: Laravel Breeze
- **PDF Generation**: barryvdh/laravel-dompdf
- **Frontend**: Blade templates with Tailwind CSS
- **Database**: MySQL/SQLite (configurable)
- **Testing**: PHPUnit with Faker
- **Development Tools**: Laravel Tinker, Pail, Pint, Sail

### Architecture Pattern
**MVC (Model-View-Controller) with Service Layer**
- Models: Eloquent ORM for data entities
- Views: Blade templates with component-based UI
- Controllers: Admin and standard controllers with middleware
- Services: FeeCalculatorService for business logic
- Middleware: Role-based access control (Admin, Agent restrictions)

### Key Technical Decisions
- Role-based access with custom middleware (IsAdmin, RestrictCalculator*)
- Service layer pattern for complex calculations
- PDF generation for quotations
- AJAX-driven dynamic form interactions
- Component-based view architecture

---

## Layer 2: Module Map

### Core Business Modules

#### 1. **Course Management Module**
- **Models**: Course, CourseType, CoursePrice, CourseSchedule
- **Controllers**: Admin\CourseController, Admin\CourseTypeController, Admin\CoursePriceController, Admin\CourseScheduleController
- **Responsibility**: Manages course catalog, pricing, and scheduling

#### 2. **Location & School Module**
- **Models**: Country, City, Region, School
- **Controllers**: Admin\CountryController, Admin\CityController, Admin\RegionController, Admin\SchoolController
- **Responsibility**: Geographic and institutional data management

#### 3. **Accommodation Module**
- **Models**: Accommodation, AccommodationPrice
- **Controllers**: Admin\AccommodationController, Admin\AccommodationPriceController
- **Responsibility**: Student housing options and pricing

#### 4. **Quotation & Calculator Module**
- **Models**: Quotation, Payment
- **Controllers**: Admin\QuotationController, Admin\QuotationPdfController
- **Services**: FeeCalculatorService
- **Responsibility**: Fee calculation engine and quotation generation

#### 5. **User & Access Control Module**
- **Models**: User
- **Controllers**: Admin\UserController, ProfileController
- **Middleware**: IsAdmin, RestrictCalculatorDashboard, RestrictCalculatorProfile
- **Responsibility**: User management and role-based access

### Supporting Modules

#### 6. **Configuration Module**
- **Models**: Setting, Currency, DiscountRule
- **Controllers**: Admin\SettingController, Admin\CurrencyController, Admin\DiscountRuleController
- **Responsibility**: System configuration and business rules

#### 7. **Add-ons & Extras Module**
- **Models**: Addon, Airport
- **Controllers**: Admin\AddonController, Admin\AirportController
- **Responsibility**: Additional services and transfer options

---

## Layer 3: Integration Guide

### API Endpoints

#### Public Routes
- `GET /` → Redirects to login
- `GET /dashboard` → Main dashboard (auth + middleware)

#### Calculator Routes (Agent Access)
- `GET /calculator` → Fee calculator interface
- `POST /calculator/calculate` → Calculate fees
- `POST /calculator/pdf` → Generate PDF quotation
- `POST /calculator/print` → Print quotation

#### AJAX Endpoints
- `GET /schools/{school}/details` → School information
- `GET /airports/by-city/{city}` → Airport listings
- `GET /accommodations/by-school/{school}` → School accommodations

#### Admin Routes (Admin Only)
- Resource routes for all admin controllers
- Bulk operations and management interfaces

### Configuration Files
- `config/app.php` → Application settings
- `config/database.php` → Database configuration
- `config/auth.php` → Authentication settings
- `.env` → Environment variables

### Database Integration
- **Migrations**: Located in `database/migrations/`
- **Seeders**: Database seeding in `database/seeders/`
- **Factories**: Test data generation in `database/factories/`

### External Dependencies
- **PDF Generation**: DomPDF library integration
- **Authentication**: Laravel Breeze integration
- **Frontend Assets**: Vite build system with Tailwind CSS

---

## Layer 4: Extension Points

### Design Patterns

#### 1. **Service Layer Pattern**
- **Location**: `app/Services/FeeCalculatorService.php`
- **Purpose**: Centralized business logic for fee calculations
- **Extension**: Add new calculation methods or pricing rules

#### 2. **Middleware Pattern**
- **Location**: `app/Http/Middleware/`
- **Purpose**: Request filtering and access control
- **Extension**: Create custom middleware for new access rules

#### 3. **Component Pattern**
- **Location**: `app/View/Components/`
- **Purpose**: Reusable UI components
- **Extension**: Add new Blade components for UI consistency

### Customization Areas

#### 1. **Fee Calculation Logic**
- **File**: `app/Services/FeeCalculatorService.php`
- **Extension Points**: Add new fee types, discount rules, seasonal pricing

#### 2. **PDF Template Customization**
- **Controllers**: `Admin\QuotationPdfController`
- **Views**: PDF blade templates
- **Extension**: Custom PDF layouts and branding

#### 3. **Role-Based Access**
- **Middleware**: Custom access control rules
- **Extension**: New user roles and permissions

#### 4. **Dynamic Form Interactions**
- **Location**: Blade templates with JavaScript
- **Extension**: Enhanced AJAX interactions and form validation

### Development Patterns

#### 1. **Model Relationships**
- Extensive use of Eloquent relationships
- **Pattern**: Consistent naming and foreign key conventions
- **Extension**: Follow established relationship patterns

#### 2. **Controller Structure**
- **Admin Controllers**: Full CRUD operations
- **Standard Controllers**: Specific business operations
- **Extension**: Maintain controller separation by user role

#### 3. **Migration Strategy**
- **Pattern**: Incremental database changes
- **Extension**: Follow Laravel migration best practices

### Recent Development Areas
- Christmas supplement calculations
- Accommodation pricing enhancements
- Guardianship fee calculations
- Frontend JavaScript improvements

---

## Quick Navigation

### For New Developers
1. Start with `memory-bank/projectbrief.md`
2. Review models in `app/Models/` for data structure
3. Examine `routes/web.php` for application flow
4. Check `app/Services/FeeCalculatorService.php` for business logic

### For Feature Development
1. Follow MVC + Service pattern
2. Use existing middleware for access control
3. Maintain consistent API endpoint structure
4. Follow established database relationship patterns

### For Debugging
1. Check `storage/logs/` for application logs
2. Use Laravel Tinker for model testing
3. Review test files in `tests/` directory
4. Examine debug PHP files in project root

---

*This handbook provides a comprehensive overview of the Bayswater Laravel application architecture and development patterns. Use it as a reference for understanding the codebase structure and extending functionality.*