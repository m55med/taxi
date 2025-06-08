import json
import csv

# مسار ملف الإدخال
input_file_path = r"C:\xampp\htdocs\taxi\test.txt"
# مسار ملف الإخراج
output_file_path = r"C:\xampp\htdocs\taxi\hospitals.csv"

# فتح ملف النص وقراءة المحتوى
with open(input_file_path, "r", encoding="utf-8") as file:
    data = file.read()

# محاولة لتحميل البيانات من JSON
try:
    hospitals = json.loads(data)
except json.JSONDecodeError as e:
    print("خطأ في قراءة JSON:", e)
    exit()

# تحديد أسماء الأعمدة من أول عنصر
fieldnames = list(hospitals[0].keys())

# كتابة البيانات في ملف CSV
with open(output_file_path, "w", newline="", encoding="utf-8-sig") as csvfile:
    writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
    writer.writeheader()
    for hospital in hospitals:
        writer.writerow(hospital)

print("تم التحويل بنجاح إلى:", output_file_path)
