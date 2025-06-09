import os

# المسار الرئيسي
base_path = r"C:\xampp\htdocs\taxi"

# اسم ملف الإخراج (مثلاً على سطح المكتب)
output_file = os.path.expanduser(r"~/Desktop/all_file_paths.txt")

# جمع كل المسارات
with open(output_file, 'w', encoding='utf-8') as f:
    for root, dirs, files in os.walk(base_path):
        for file in files:
            full_path = os.path.join(root, file)
            f.write(full_path + '\n')

print(f"تم حفظ جميع المسارات في الملف: {output_file}")
