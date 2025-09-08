<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Referral\ProfileModel;
use App\Services\ActiveUserService;
use App\Core\Auth;
use App\Models\Admin\Restaurant;

class ApiController extends Controller
{
    private $profileModel;
    private $restaurantModel;

    public function __construct()
    {
        // Note: These models might need their own `require_once` in api.php if autoloading fails.
        $this->profileModel = new ProfileModel();
        $this->restaurantModel = new Restaurant();
    }

    public function getAgents()
    {
        header('Content-Type: application/json');
        $userModel = new \App\Models\User\User();
        $agents = $userModel->getAllAgentsDetails();
        echo json_encode($agents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function formatWorkingHours($workingHours)
    {
        $formatted = [];
        foreach ($workingHours as $day => $hours) {
            if (!empty($hours['is_closed'])) {
                $formatted[$day] = 'مغلق';
            } else {
                $open = $hours['open_time'] ?? '';
                $close = $hours['close_time'] ?? '';
                
                if (empty($open) && empty($close)) {
                    $formatted[$day] = 'غير محدد';
                } else {
                    $formatted[$day] = $open . ' - ' . $close;
                }
            }
        }
        return $formatted;
    }
    
    public function heartbeat()
    {
        header('Content-Type: application/json');
        
        $userId = Auth::getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        require_once APPROOT . '/services/ActiveUserService.php';
        $activeUserService = new ActiveUserService();
        $activeUserService->recordUserActivity($userId);
        
        echo json_encode(['status' => 'ok']);
    }

    public function createRestaurant()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // Decode JSON body instead of using $_POST
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'name_ar' => $input['name_ar'] ?? null,
            'name_en' => $input['name_en'] ?? null,
            'category' => $input['category'] ?? null,
            'governorate' => $input['governorate'] ?? null,
            'city' => $input['city'] ?? null,
            'address' => $input['address'] ?? null,
            'is_chain' => isset($input['is_chain']) ? (int)$input['is_chain'] : 0,
            'num_stores' => isset($input['num_stores']) ? (int)$input['num_stores'] : null,
            'contact_name' => $input['contact_name'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'pdf_path' => null,
            'referred_by_user_id' => null // Initialize
        ];

        // Handle referral
        if (!empty($input['ref'])) {
            $userModel = new \App\Models\User\User();
            $referringUser = $userModel->findByUsername($input['ref']);
            if ($referringUser) {
                $data['referred_by_user_id'] = $referringUser['id'];
            }
        }

        // Basic validation
        if (empty($data['name_en'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'English name is required.']);
            return;
        }

        $restaurantId = $this->restaurantModel->create($data);

        if ($restaurantId) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Restaurant created successfully. You can now upload a PDF.',
                'restaurant_id' => $restaurantId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create restaurant in database.']);
        }
    }

    public function updateRestaurantPdf($id)
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $restaurant = $this->restaurantModel->getById($id);
        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Restaurant not found.']);
            return;
        }

        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == 0) {
            $uploadDir = APPROOT . '/uploads/pdfs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['pdf']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
                // If there was an old PDF, delete it
                if ($restaurant['pdf_path'] && file_exists($uploadDir . $restaurant['pdf_path'])) {
                    unlink($uploadDir . $restaurant['pdf_path']);
                }
                
                // Update the database with the new filename
                if ($this->restaurantModel->updatePdfPath($id, $fileName)) {
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'PDF uploaded and linked to restaurant successfully.',
                        'pdf_path' => $fileName
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to update database with new PDF path.']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to upload PDF.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No PDF file was uploaded or an error occurred.']);
        }
    }

    public function getReferredRestaurants($marketerId)
    {
        header('Content-Type: application/json');
        
        $restaurants = $this->profileModel->getReferredRestaurantsByMarketer($marketerId);
        
        if ($restaurants === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to retrieve restaurants.']);
            return;
        }
        
        echo json_encode(['success' => true, 'restaurants' => $restaurants], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function generateContract()
    {
        // Start output buffering to catch any stray warnings from TCPDF
        ob_start();

        // Prevent PHP warnings from breaking the JSON response
        ini_set('display_errors', 0);
        error_reporting(0);

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $restaurantId = $input['restaurant_id'] ?? null;
        $bankDetails = $input['bank_details'] ?? [];
        $signatureDataUrl = $input['signature'] ?? null;
        $signerDetails = $input['signer'] ?? [];

        if (!$restaurantId || !$signatureDataUrl || empty($bankDetails) || empty($signerDetails) || empty($bankDetails['bank_name']) || empty($bankDetails['account_name']) || empty($bankDetails['iban']) || empty($signerDetails['name']) || empty($signerDetails['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required data. Ensure restaurant_id, signature, and all bank/signer details are provided.']);
            return;
        }

        $restaurant = $this->restaurantModel->getById($restaurantId);
        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Restaurant not found.']);
            return;
        }

        // Handle signature image
        if (!preg_match('/^data:image\/(png|jpeg);base64,/', $signatureDataUrl)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid signature format. Must be a base64-encoded PNG or JPEG data URL.']);
            return;
        }
        list($type, $signatureData) = explode(';', $signatureDataUrl);
        list(, $signatureData)      = explode(',', $signatureData);
        $signatureImage = base64_decode($signatureData);
        
        $tempSignatureDir = APPROOT . '/cache/signatures/';
        if (!is_dir($tempSignatureDir)) {
            mkdir($tempSignatureDir, 0777, true);
        }
        $signatureImagePath = $tempSignatureDir . 'signature_' . $restaurantId . '_' . time() . '.png';
        file_put_contents($signatureImagePath, $signatureImage);

        try {
            // Prepare HTML content for PDF
            $html = $this->getContractHtml($restaurant, $bankDetails, $signerDetails, $signatureImagePath);

            // Generate PDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetAuthor('TaxiF Express');
            $pdf->SetTitle('Partnership Agreement - ' . $restaurant['name_en']);
            $pdf->setRTL(true);
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');

            // Save PDF to a secure location
            $pdfDir = APPROOT . '/uploads/pdfs/contracts/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }
            $pdfFileName = 'contract_' . $restaurantId . '_' . time() . '.pdf';
            $pdfRelativePath = 'contracts/' . $pdfFileName;
            $pdf->Output($pdfDir . $pdfFileName, 'F');

        } catch (\Exception $e) {
            // Clean the buffer to discard any warnings before sending our response
            ob_end_clean();

            // Log the actual error for debugging purposes
            error_log('TCPDF Generation Error: ' . $e->getMessage());

            // Send a clean JSON error response instead of raw PHP errors
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'An internal error occurred while generating the PDF. The issue has been logged.'
            ]);
            
            // Clean up the temporary signature file if it exists
            if (file_exists($signatureImagePath)) {
                unlink($signatureImagePath);
            }
            
            return; // Stop execution
        }

        unlink($signatureImagePath);
        
        // Save the contract path to the database
        $this->restaurantModel->updatePdfPath($restaurantId, $pdfRelativePath);

        $downloadUrl = $this->generateSecureDownloadUrl($pdfRelativePath);

        // Clean the buffer to discard any warnings before sending the final response
        ob_end_clean();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Contract PDF generated successfully.',
            'pdf_url' => $downloadUrl
        ]);
    }

    public function downloadContract()
    {
        $filePath = $_GET['file'] ?? null;
        $expiry = $_GET['expires'] ?? null;
        $receivedSignature = $_GET['signature'] ?? null;

        if (!$filePath || !$expiry || !$receivedSignature) {
            http_response_code(400);
            die('Invalid download link.');
        }

        if (time() > $expiry) {
            http_response_code(403);
            die('Download link has expired.');
        }

        // IMPORTANT: Store this key securely in your application config, not hard-coded.
        $secretKey = 'your-super-secret-key-for-pdf-signing-change-me';
        $dataToSign = $filePath . $expiry;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $secretKey);

        if (!hash_equals($expectedSignature, $receivedSignature)) {
            http_response_code(403);
            die('Invalid signature. Access denied.');
        }

        $fullFilePath = APPROOT . '/uploads/pdfs/' . $filePath;

        if (!file_exists($fullFilePath) || !is_readable($fullFilePath)) {
            http_response_code(404);
            die('File not found.');
        }
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($fullFilePath) . '"');
        header('Content-Length: ' . filesize($fullFilePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($fullFilePath);
        exit;
    }

    private function generateSecureDownloadUrl($filePath)
    {
        // IMPORTANT: Store this key securely in your application config, not hard-coded.
        $secretKey = 'your-super-secret-key-for-pdf-signing-change-me';
        $expiry = time() + 3600; // Link is valid for 1 hour
        $dataToSign = $filePath . $expiry;
        $signature = hash_hmac('sha256', $dataToSign, $secretKey);

        $queryParams = http_build_query([
            'file' => $filePath,
            'expires' => $expiry,
            'signature' => $signature
        ]);

        return URLROOT . '/api/downloadContract?' . $queryParams;
    }

    private function getContractHtml($restaurant, $bankDetails, $signerDetails, $signatureImagePath)
    {
        // Sanitize all data before outputting to HTML
        foreach ($restaurant as &$value) { $value = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
        foreach ($bankDetails as &$value) { $value = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
        foreach ($signerDetails as &$value) { $value = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }

        return <<<HTML
<style>
    .contract-container { direction: rtl; font-family: "dejavusans", sans-serif; }
    .contract-section { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    h2, h3, h4 { text-align: center; }
    p { line-height: 1.6; }
    .signature-pad-container { border: 1px solid #ccc; margin-top: 10px; text-align: center; padding: 10px; }
</style>
<div class="contract-container">
    <h2>📄 عقد شراكة وتقديم خدمات توصيل</h2>
    <h3>بين شركة TaxiF Express والمطعم</h3>
    <div class="contract-section">
        <p><strong>الطرف الأول:</strong> شركة النقل الذكي الأولى (TaxiF Express) ش.م.م</p>
        <p><strong>العنوان:</strong> سلطنة عمان – محافظة مسقط – السيب – الخوض – مبنى سكوير الخوض – الدور الأول – مكتب رقم 1093</p>
        <p><strong>ويمثلها:</strong> عمران بن علي الهطالي</p>
    </div>
    <div class="contract-section">
        <h3>الطرف الثاني:</h3>
        <p><strong>الاسم التجاري:</strong> {$restaurant['name_ar']}</p>
        <p><strong>العلامة التجارية:</strong> {$restaurant['name_en']}</p>
        <p><strong>العنوان:</strong> {$restaurant['address']}</p>
        <p><strong>رقم الهاتف:</strong> {$restaurant['phone']}</p>
        <p><strong>البريد الإلكتروني:</strong> {$restaurant['email']}</p>
        <p><strong>ويمثلها:</strong> {$restaurant['contact_name']}</p>
    </div>
    <div class="contract-section"><h4>المقدمة</h4><p>نظرًا لأن شركة TaxiF Express تعمل في مجال تقديم خدمات التوصيل من خلال تطبيق ذكي ، وترغب في تقديم هذه الخدمات للمطاعم ومقدمي الخدمات الغذائية، وحيث أن المطعم يرغب في الانضمام إلى المنصة والاستفادة من خدمات التوصيل الإلكتروني والدفع والتحصيل وخدمة العملاء عبر التطبيق، فقد اتفق الطرفان وهما بكامل أهليتهما القانونية على الشروط التالية:</p></div>
    <div class="contract-section"><h4>البند الأول: موضوع العقد</h4><p>1. تقوم الشركة بإدراج المطعم ضمن المنصة الإلكترونية الخاصة بها، لتمكين العملاء من الطلب إلكترونيًا.</p><p>2. تقوم الشركة بإرسال الكباتن المعتمدين لاستلام الطلبات من المطعم وتوصيلها للعملاء.</p><p>3. تقدم الشركة خدمات الدفع الإلكتروني والتحصيل لصالح المطعم، وتخصم العمولة المتفق عليها قبل التحويل.</p><p>4. يلتزم المطعم بتحديث حالة الطلبات عبر الجهاز المخصص فور جاهزية الطلب.</p></div>
    <div class="contract-section"><h4>البند الثاني: مدة العقد</h4><p>1. يبدأ العقد من تاريخ توقيعه ويستمر لمدة سنة ميلادية واحدة، قابلة للتجديد تلقائيًا.</p><p>2. يمكن لأي من الطرفين إنهاء العقد بإشعار كتابي قبل 30 يومًا من تاريخ الرغبة بإنهاء التعاقد.</p><p>3. للشركة الحق في الإنهاء الفوري دون إشعار في حال مخالفة المطعم للشروط الجوهرية للعقد أو التعليمات الصادرة من الشركة.</p></div>
    <div class="contract-section"><h4>البند الثالث: الرسوم والعمولات</h4><p>1. رسوم التسجيل، ورسوم التجديد السنوي، ونسبة العمولة تُحدد لاحقًا من قبل الشركة وفقًا للسياسات التشغيلية.</p><p>2. تُخصم العمولة تلقائيًا من المبلغ المحصل من العميل قبل تحويل المستحقات للمطعم.</p><p>3. يتم تحويل صافي المبلغ المستحق للمطعم حسب الجدول الزمني الذي تحدده الشركة وتخطر به المطعم.</p><p>4. يحق للشركة مراجعة أو تعديل نسبة العمولة أو آلية التحصيل أو مواعيد التحويل وفقًا لاحتياجات العمل والتغيرات التشغيلية، على أن يتم إخطار المطعم بالتعديلات.</p></div>
    <div class="contract-section"><h4>البند الرابع: المعدات والأجهزة</h4><p>1. تُسلّم الشركة للمطعم جهازًا لوحيًا (Tablet) مخصصًا لتلقي الطلبات (يتم الاتفاق على دفع ثمنه بين الشركة والمطعم )</p><p>2. يلتزم المطعم باستخدام الجهاز فقط لغرض التطبيق وعدم التعديل أو التفكيك أو الاستخدام غير المصرح.</p><p>3. تبقى ملكية الجهاز والبرامج التابعة له للشركة.</p><p>4. في حال تلف الجهاز أو إساءة استخدامه، يتحمل المطعم تكلفة الاستبدال أو الصيانة، ويتم خصمها من مستحقاته.</p><p>5. المطعم ملزم بإرجاع الجهاز بحالة جيدة عند انتهاء العقد أو الإلغاء، وفي حال عدم الإرجاع، تتحمل المنشأة تكلفة الجهاز حسب ما تقرره الشركة.</p></div>
    <div class="contract-section"><h4>البند الخامس: التزامات المطعم</h4><p>1. الالتزام بجودة الطلبات وتغليفها وتسليمها في الوقت المحدد.</p><p>2. الالتزام باستخدام النظام الإلكتروني وعدم تغيير حالة الطلبات دون مبرر.</p><p>3. المحافظة على سمعة الشركة، وعدم التواصل المباشر مع عملاء التطبيق خارج الإطار المسموح.</p><p>4. عدم التعامل مع تطبيقات منافسة باستخدام نفس الجهاز أو نفس العلامة التجارية خلال مدة سريان العقد.</p><p>5. الحفاظ على سرية بيانات العملاء وأي معلومات تجارية أو تقنية تخص الشركة.</p></div>
    <div class="contract-section"><h4>البند السادس: التزامات الشركة</h4><p>1. توفير كباتن مرخصين ومؤهلين لاستلام الطلبات من المطعم وتوصيلها للعملاء.</p><p>2. توفير الدعم الفني والتقني للمطعم حسب مواعيد العمل الرسمية للشركة.</p><p>3. صيانة الأنظمة ومتابعة التحصيلات وتحويلها وفقًا للسياسات المقررة من الشركة.</p></div>
    <div class="contract-section"><h4>البند السابع: السرية والبيانات</h4><p>1. يتعهد الطرفان بالحفاظ على سرية المعلومات التجارية، والتقنية، وقوائم العملاء وعدم إفشائها لأي طرف ثالث دون موافقة خطية.</p><p>2. لا يحق للمطعم مشاركة بيانات أو تقارير أو محتوى النظام مع أطراف أخرى دون إذن رسمي من الشركة.</p><p>3. في حال الإخلال بالسرية، يحق للشركة إنهاء العقد فورًا والمطالبة بالتعويض عن أي ضرر مباشر أو غير مباشر.</p></div>
    <div class="contract-section">
         <h4>البند الثامن: الحساب البنكي</h4>
         <p>تُحوّل مستحقات المطعم إلى الحساب التالي:</p>
         <p><strong>اسم البنك:</strong> {$bankDetails['bank_name']}</p>
         <p><strong>اسم الحساب:</strong> {$bankDetails['account_name']}</p>
         <p><strong>رقم الآيبان (IBAN):</strong> {$bankDetails['iban']}</p>
     </div>
    <div class="signature-section">
        <h3>التوقيع والختم</h3>
        <p><strong>اسم الموقّع (الطرف الثاني):</strong> {$signerDetails['name']}</p>
        <p><strong>الصفة:</strong> {$signerDetails['title']}</p>
        <p><strong>التوقيع:</strong></p>
        <div class="signature-pad-container">
            <img src="{$signatureImagePath}" width="200" height="100">
        </div>
    </div>
</div>
HTML;
    }
}
