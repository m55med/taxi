<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_main_title); ?> - TaxiF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-black': '<?php echo $color_brand_black; ?>',
                        'brand-yellow': '<?php echo $color_brand_yellow; ?>',
                        'brand-white': '<?php echo $color_brand_white; ?>',
                        'brand-gray': {
                            DEFAULT: '<?php echo $color_brand_gray_default; ?>',
                            light: '<?php echo $color_brand_gray_light; ?>',
                            text: '<?php echo $color_brand_gray_text; ?>',
                            subtext: '<?php echo $color_brand_gray_subtext; ?>'
                        }
                    },
                    fontFamily: { 'cairo': ['Cairo', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: <?php echo $color_brand_black; ?>;
            color: <?php echo $color_brand_gray_text; ?>;
        }
        .form-container {
            background-color: <?php echo $color_brand_gray_default; ?>;
            color: <?php echo $color_brand_gray_text; ?>;
        }
        .form-container label {
            color: <?php echo $color_brand_gray_subtext; ?>;
        }
        .form-container input, .form-container select {
            background-color: <?php echo $color_brand_gray_light; ?>;
            border-color: <?php echo $color_brand_gray_light; ?>;
            color: <?php echo $color_brand_gray_text; ?>;
        }
        .form-container input:focus, .form-container select:focus {
            border-color: <?php echo $color_brand_yellow; ?>;
            box-shadow: 0 0 0 2px <?php echo $color_brand_yellow . '40'; ?>;
        }
        .message-box a {
            color: <?php echo $color_brand_yellow; ?>;
            text-decoration: underline;
        }
        .message-box.success { background-color: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); color: #A7F3D0; }
        .message-box.error { background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #FECACA; }
        .message-box.info { background-color: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); color: #BFDBFE; }
    </style>
</head>
<body class="flex flex-col justify-center items-center min-h-screen p-4 selection:bg-brand-yellow selection:text-brand-black">

    <div class="form-container p-6 sm:p-10 rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-500 ease-in-out">
        
        <div class="text-center mb-6">
            <a href="#" class="inline-block">
                <h1 class="text-4xl font-bold text-brand-yellow">Taxi<span class="text-brand-black">F</span></h1>
                <p class="text-sm text-brand-gray-subtext">انضم إلى فريق السائقين</p>
            </a>
        </div>

        <?php if ($affiliate_id && isset($affiliate_name_for_display)): ?>
            <div class="mb-4 text-sm text-center p-3 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-yellow-300">
                أنت مدعو للانضمام بواسطة: <strong class="font-semibold"><?php echo htmlspecialchars($affiliate_name_for_display); ?></strong>.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($display_message)): ?>
            <div class="message-box p-4 mb-6 rounded-lg text-sm text-center border <?php echo htmlspecialchars($display_message_type); ?>">
                <?php echo $display_message; // HTML is allowed here for links ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$user_already_registered_in_this_visit): ?>
        <form action="" method="POST" id="referralForm" class="space-y-5" novalidate>
            <div>
                <label for="country_id" class="block mb-1.5 text-xs font-medium uppercase tracking-wider">الدولة</label>
                <select name="country_id" id="country_id" class="block w-full px-4 py-2.5 rounded-lg outline-none transition-all duration-200 ease-in-out text-sm" required>
                    <option value="">-- اختر الدولة --</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?php echo htmlspecialchars($country['id']); ?>"><?php echo htmlspecialchars($country['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="car_type_id" class="block mb-1.5 text-xs font-medium uppercase tracking-wider">نوع السيارة</label>
                <select name="car_type_id" id="car_type_id" class="block w-full px-4 py-2.5 rounded-lg outline-none transition-all duration-200 ease-in-out text-sm" required>
                    <option value="">-- اختر نوع السيارة --</option>
                    <?php foreach ($car_types as $car_type): ?>
                        <option value="<?php echo htmlspecialchars($car_type['id']); ?>"><?php echo htmlspecialchars($car_type['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="full_name" class="block mb-1.5 text-xs font-medium uppercase tracking-wider">الاسم الكامل</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_full_name_value); ?>"
                       class="block w-full px-4 py-2.5 rounded-lg outline-none transition-all duration-200 ease-in-out text-sm"
                       required placeholder="مثال: علي بن محمد الرئيسي">
            </div>
            
            <div>
                <label for="phone" class="block mb-1.5 text-xs font-medium uppercase tracking-wider">رقم الهاتف (مع مفتاح الدولة)</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_phone_value); ?>"
                       class="block w-full px-4 py-2.5 rounded-lg outline-none transition-all duration-200 ease-in-out text-sm"
                       required pattern="[0-9\\s\\-\\+\\(\\)]{7,20}" title="الرجاء إدخال رقم هاتف صحيح." placeholder="مثال: 9689xxxxxxx">
            </div>
            
            <button type="submit" name="submit_referral"
                    class="w-full bg-brand-yellow text-brand-black font-bold py-3 px-4 rounded-lg hover:bg-opacity-90 focus:outline-none focus:ring-4 focus:ring-yellow-600/50 transition-all duration-200 ease-in-out transform hover:scale-[1.03]">
                سجل الآن كسائق
            </button>
        </form>
        <?php else: ?>
            <div class="text-center mt-6">
                <a href="#" class="text-brand-yellow hover:underline text-sm">العودة إلى الصفحة الرئيسية</a>
            </div>
        <?php endif; ?>

        <p class="mt-8 text-xs text-center text-brand-gray-subtext">
            بالتسجيل، أنت توافق على شروط الخدمة و سياسة الخصوصية.
        </p>
    </div>

</body>
</html> 