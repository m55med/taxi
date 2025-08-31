# Establishments API - Image Upload Documentation

## Overview
تم تطوير نظام رفع الصور في API الخاص بالـ establishments ليدعم رفع الصور مباشرة إلى السيرفر بدلاً من حفظ URLs في قاعدة البيانات.

## Security Features
- **Protected Storage**: الصور محفوظة في مجلد محمي غير قابل للوصول المباشر
- **Authentication Required**: يتطلب تسجيل دخول للوصول للصور  
- **Role-based Access**: فقط المستخدمين بصلاحيات admin, developer, marketer يمكنهم الوصول للصور
- **File Validation**: فحص نوع الملف والحجم والأمان
- **Image Optimization**: تغيير حجم الصور تلقائياً حسب النوع

## API Endpoint for Creating Establishments with Images

### URL
```
POST /api/establishments/create
```

### Content Type
```
Content-Type: multipart/form-data
```

### Parameters

#### Text Fields
- `establishment_name` (required) - اسم المؤسسة
- `legal_name` - الاسم القانوني
- `taxpayer_number` - رقم دافع الضرائب
- `street` - الشارع
- `house_number` - رقم المنزل
- `postal_zip` - الرمز البريدي
- `establishment_email` - البريد الإلكتروني للمؤسسة
- `establishment_phone` - هاتف المؤسسة
- `owner_full_name` - اسم المالك الكامل
- `owner_position` - منصب المالك
- `owner_email` - بريد المالك الإلكتروني
- `owner_phone` - هاتف المالك
- `description` - الوصف
- `marketer_id` - معرف المسوق

#### File Fields
- `establishment_logo` - ملف شعار المؤسسة (اختياري)
- `establishment_header_image` - ملف صورة الهيدر (اختياري)

### Image Requirements
- **Supported Formats**: JPG, JPEG, PNG, WebP
- **Maximum Size**: 5MB per image
- **Logo Dimensions**: سيتم تغيير الحجم تلقائياً إلى 400x400 بكسل كحد أقصى
- **Header Dimensions**: سيتم تغيير الحجم تلقائياً إلى 1200x400 بكسل كحد أقصى

## cURL Examples

### Example 1: Create establishment with both logo and header image
```bash
curl -X POST "https://yourserver.com/api/establishments/create" \
  -F "establishment_name=مطعم الأصالة" \
  -F "legal_name=شركة الأصالة للمطاعم المحدودة" \
  -F "taxpayer_number=123456789" \
  -F "street=شارع الملك فهد" \
  -F "house_number=1234" \
  -F "postal_zip=12345" \
  -F "establishment_email=info@alasala-restaurant.com" \
  -F "establishment_phone=+966501234567" \
  -F "owner_full_name=أحمد محمد السعد" \
  -F "owner_position=المدير العام" \
  -F "owner_email=ahmed@alasala-restaurant.com" \
  -F "owner_phone=+966501234568" \
  -F "description=مطعم متخصص في الأكلات الشعبية السعودية" \
  -F "marketer_id=5" \
  -F "establishment_logo=@/path/to/logo.png" \
  -F "establishment_header_image=@/path/to/header.jpg"
```

### Example 2: Create establishment with logo only
```bash
curl -X POST "https://yourserver.com/api/establishments/create" \
  -F "establishment_name=كافيه البن العربي" \
  -F "establishment_email=info@arabiancoffee.com" \
  -F "establishment_phone=+966501234567" \
  -F "marketer_id=3" \
  -F "establishment_logo=@/path/to/coffee-logo.png"
```

### Example 3: Create establishment without images (text only)
```bash
curl -X POST "https://yourserver.com/api/establishments/create" \
  -H "Content-Type: application/json" \
  -d '{
    "establishment_name": "محل الإلكترونيات الحديثة",
    "legal_name": "شركة الإلكترونيات الحديثة",
    "establishment_email": "info@modernelectronics.com",
    "establishment_phone": "+966501234567",
    "marketer_id": 2
  }'
```

## Success Response with Images

```json
{
  "success": true,
  "message": "Establishment created successfully",
  "establishment_id": 15,
  "images": {
    "logo": "logos/15_logo_1640995200_abc123.png",
    "header": "headers/15_header_1640995201_def456.jpg"
  },
  "data": {
    "id": 15,
    "establishment_name": "مطعم الأصالة",
    "legal_name": "شركة الأصالة للمطاعم المحدودة",
    "taxpayer_number": "123456789",
    "street": "شارع الملك فهد",
    "house_number": "1234",
    "postal_zip": "12345",
    "establishment_email": "info@alasala-restaurant.com",
    "establishment_phone": "+966501234567",
    "owner_full_name": "أحمد محمد السعد",
    "owner_position": "المدير العام",
    "owner_email": "ahmed@alasala-restaurant.com",
    "owner_phone": "+966501234568",
    "description": "مطعم متخصص في الأكلات الشعبية السعودية",
    "establishment_logo": "logos/15_logo_1640995200_abc123.png",
    "establishment_header_image": "headers/15_header_1640995201_def456.jpg",
    "marketer_id": 5,
    "marketer_name": "سارة أحمد",
    "created_at": "2024-01-01 12:00:00"
  }
}
```

## Error Responses

### File Upload Error
```json
{
  "success": true,
  "message": "Establishment created successfully",
  "establishment_id": 15,
  "images": {
    "logo": "logos/15_logo_1640995200_abc123.png",
    "header_error": "File size exceeds maximum allowed size (5MB)."
  },
  "data": {
    // ... establishment data
  }
}
```

### Validation Error
```json
{
  "error": "Establishment name is required"
}
```

### File Type Error
```json
{
  "error": "Invalid file type. Only JPEG, PNG, and WebP images are allowed."
}
```

## Accessing Images

### Image URL Format
```
GET /establishment/image/{imagePath}
```

### Example
```
https://yourserver.com/establishment/image/logos%2F15_logo_1640995200_abc123.png
```

**Note**: يتطلب تسجيل الدخول للوصول للصور

## Security Notes

1. **File Storage**: الصور محفوظة في `app/uploads/establishments/` وهو مجلد محمي
2. **Access Control**: ملف `.htaccess` يمنع الوصول المباشر للصور
3. **Authentication**: كل طلب لعرض الصور يتطلب تسجيل دخول صحيح
4. **Authorization**: فقط المستخدمين بصلاحيات مناسبة يمكنهم الوصول
5. **File Validation**: فحص شامل لنوع الملف وحجمه وأمانه
6. **Path Security**: التحقق من مسار الملف لمنع الوصول لملفات خارج المجلد المسموح

## File Organization

```
app/uploads/establishments/
├── .htaccess (حماية المجلد)
├── logos/
│   ├── 15_logo_1640995200_abc123.png
│   └── 16_logo_1640995300_xyz789.jpg
└── headers/
    ├── 15_header_1640995201_def456.jpg
    └── 16_header_1640995301_ghi012.png
```

## Integration with Edit Form

الصفحة `/referral/establishments/edit/{id}` تدعم الآن:
- رفع صور جديدة
- معاينة الصور قبل الرفع
- عرض الصور الحالية
- Drag & Drop للصور
- حذف الصور القديمة عند رفع صور جديدة
