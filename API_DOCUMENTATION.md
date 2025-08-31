# Establishments API Documentation

## API Endpoint
**URL:** `/api/establishments/create`
**Method:** POST
**Content-Type:** application/json

## API Request Example (cURL)

### Basic Example
```bash
curl -X POST "https://cs.taxif.com.com/api/establishments/create" \
  -H "Content-Type: application/json" \
  -d '{
    "establishment_name": "Best Pizza Restaurant",
    "legal_name": "Best Pizza LLC",
    "taxpayer_number": "123456789",
    "street": "Main Street 123",
    "house_number": "123A",
    "postal_zip": "12345",
    "establishment_email": "contact@bestpizza.com",
    "establishment_phone": "+1234567890",
    "owner_full_name": "John Smith",
    "owner_position": "Owner & Manager",
    "owner_email": "john@bestpizza.com", 
    "owner_phone": "+1234567891",
    "description": "Best pizza restaurant in the city, serving authentic Italian cuisine since 2020",
    "establishment_logo": "https://example.com/logo.png",
    "establishment_header_image": "https://example.com/header.jpg",
    "marketer_id": 5
  }'
```

### Minimal Example (Only required field)
```bash
curl -X POST "https://cs.taxif.com.com/api/establishments/create" \
  -H "Content-Type: application/json" \
  -d '{
    "establishment_name": "Simple Cafe"
  }'
```

### Complete Example with all fields
```bash
curl -X POST "https://cs.taxif.com.com/api/establishments/create" \
  -H "Content-Type: application/json" \
  -d '{
    "establishment_name": "Elite Restaurant & Lounge",
    "legal_name": "Elite Hospitality Group Inc.",
    "taxpayer_number": "987654321",
    "street": "Business District Avenue",
    "house_number": "456",
    "postal_zip": "54321",
    "establishment_email": "info@eliterestaurant.com",
    "establishment_phone": "+9876543210",
    "owner_full_name": "Sarah Johnson",
    "owner_position": "CEO & Founder",
    "owner_email": "sarah@eliterestaurant.com",
    "owner_phone": "+9876543211",
    "description": "Upscale dining experience featuring international cuisine, premium service, and elegant atmosphere. Perfect for business meetings and special occasions.",
    "establishment_logo": "https://cdn.eliterestaurant.com/assets/logo-official.png",
    "establishment_header_image": "https://cdn.eliterestaurant.com/assets/restaurant-header.jpg",
    "marketer_id": 3
  }'
```

## API Response Examples

### Successful Response (201 Created)
```json
{
  "success": true,
  "message": "Establishment created successfully",
  "establishment_id": 42,
  "data": {
    "id": "42",
    "establishment_name": "Best Pizza Restaurant",
    "legal_name": "Best Pizza LLC",
    "taxpayer_number": "123456789",
    "street": "Main Street 123",
    "house_number": "123A",
    "postal_zip": "12345",
    "establishment_email": "contact@bestpizza.com",
    "establishment_phone": "+1234567890",
    "owner_full_name": "John Smith",
    "owner_position": "Owner & Manager",
    "owner_email": "john@bestpizza.com",
    "owner_phone": "+1234567891",
    "description": "Best pizza restaurant in the city, serving authentic Italian cuisine since 2020",
    "establishment_logo": "https://example.com/logo.png",
    "establishment_header_image": "https://example.com/header.jpg",
    "marketer_id": "5",
    "created_at": "2024-01-15 14:30:45",
    "marketer_name": "Ahmed Mohamed"
  }
}
```

### Error Response - Missing Required Field (400 Bad Request)
```json
{
  "error": "Establishment name is required"
}
```

### Error Response - Invalid JSON (400 Bad Request)
```json
{
  "error": "Invalid JSON input"
}
```

### Error Response - Method Not Allowed (405)
```json
{
  "error": "Method not allowed"
}
```

### Error Response - Server Error (500)
```json
{
  "error": "Internal server error: Database connection failed"
}
```

## Field Descriptions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `establishment_name` | string | **Yes** | Name of the establishment |
| `legal_name` | string | No | Legal/official business name |
| `taxpayer_number` | string | No | Tax identification number |
| `street` | string | No | Street address |
| `house_number` | string | No | Building/house number |
| `postal_zip` | string | No | Postal/ZIP code |
| `establishment_email` | string | No | Business email address |
| `establishment_phone` | string | No | Business phone number |
| `owner_full_name` | string | No | Full name of the owner |
| `owner_position` | string | No | Owner's job title/position |
| `owner_email` | string | No | Owner's email address |
| `owner_phone` | string | No | Owner's phone number |
| `description` | text | No | Description of the establishment |
| `establishment_logo` | string | No | URL to the establishment logo |
| `establishment_header_image` | string | No | URL to header image |
| `marketer_id` | integer | No | ID of the associated marketer |

---

# Establishments Management Page

## Access Control
- **Admin/Developer**: Full access to all establishments + edit capabilities
- **Marketer**: Access only to their own establishments (filtered by marketer_id)

## Features

### 1. Summary Statistics Dashboard
- Total establishments count
- Establishments with email contact
- Establishments with phone contact  
- Establishments with description

### 2. Data Table
- Paginated display (25 records per page)
- Comprehensive establishment information
- Contact details with clickable links
- Marketer assignment status
- Creation date and time

### 3. Export Functionality
- **Excel (.xlsx)**: Full data export with professional formatting
- **CSV**: Compatible with spreadsheet applications
- **JSON**: Developer-friendly format
- All exports respect user permissions (marketer filter)

### 4. Admin Edit Functionality
- Only available to Admin and Developer roles
- Comprehensive form with all establishment fields
- Marketer assignment dropdown
- Real-time form validation

### 5. Navigation Integration
- Added to "Referral & Marketing" section
- Proper role-based visibility
- Breadcrumb navigation

## Access URLs
- **Main Page**: `/referral/establishments`
- **Export Excel**: `/referral/establishments/export?format=excel`
- **Export CSV**: `/referral/establishments/export?format=csv` 
- **Export JSON**: `/referral/establishments/export?format=json`
- **Edit (Admin only)**: `/referral/establishments/edit/{id}`

## Database Structure
All data is stored in the `establishments` table with foreign key relationship to the `users` table through `marketer_id`.

## Technical Implementation
- **MVC Architecture**: Proper separation of concerns
- **Role-Based Access Control**: Integrated with existing permission system
- **Responsive Design**: Mobile-friendly interface
- **Flash Messages**: User feedback for all actions
- **Error Handling**: Comprehensive error management
- **Security**: Input validation and SQL injection protection
