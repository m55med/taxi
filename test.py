import os
import re

# المسار الأساسي اللي فيه الكنترولرز
base_path = r"C:\xampp\htdocs\app\controllers\reports"

# Regular Expression للعثور على model('xxx/yyy') أو model("xxx/yyy")
pattern = re.compile(r"""\$this->model\((['"])([a-zA-Z0-9_/]+)\1\)""")

def capitalize_path(model_path):
    parts = model_path.split('/')
    return '/'.join(part[:1].upper() + part[1:] for part in parts)

def process_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    matches = pattern.findall(content)
    if not matches:
        return

    new_content = content

    for quote, model_path in matches:
        corrected = capitalize_path(model_path)
        original_full = f"$this->model({quote}{model_path}{quote})"
        corrected_full = f"$this->model({quote}{corrected}{quote})"
        if original_full != corrected_full:
            new_content = new_content.replace(original_full, corrected_full)
            print(f"[✓] Updated in: {file_path}")
            print(f"    {original_full} ➜ {corrected_full}")

    # الكتابة فقط لو حدث تغيير
    if new_content != content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)

def scan_directory(directory):
    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                process_file(os.path.join(root, file))

if __name__ == "__main__":
    scan_directory(base_path)
    print("\n✅ الانتهاء من تعديل جميع ملفات الكنترولرز.")
