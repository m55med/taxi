import requests

url = "https://cs.taxif.com/api/establishments/create"
files = {
    "establishment_logo": ("logo.png", open(r"C:\Users\tymyt\Downloads\taxifom\assets\images\google-app.png", "rb"), "image/png"),
    "establishment_header_image": ("header.png", open(r"C:\Users\tymyt\Downloads\taxifom\assets\images\google-app.png", "rb"), "image/png"),
}
data = {
    "establishment_name": "مطعم التجربة",
    "legal_name": "شركة مطعم التجربة",
    "establishment_email": "demo@test.com",
    "establishment_phone": "+201234567890",
    "owner_full_name": "محمد احمد",
    "owner_position": "المدير",
    "owner_email": "owner@test.com",
    "owner_phone": "+201098765432",
    "description": "اختبار رفع بيانات المؤسسة وصور",
    "marketer_id": "115"
}

response = requests.post(url, data=data, files=files)
print("Status:", response.status_code)
print("Response:", response.text)
