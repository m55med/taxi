<?php



namespace App\Controllers\Api;



use App\Core\Controller;

use App\Models\Referral\ProfileModel;

use App\Services\ActiveUserService;

use App\Core\Auth;

use App\Models\Admin\Restaurant;

use App\Models\Admin\TeamMember;

use App\Models\Token\Token;

use App\Models\Tickets\Ticket;

use App\Core\Database;

use PDO;
use PDOException;

class ApiController extends Controller

{

    private $profileModel;

    private $restaurantModel;

    private $tokenModel;

    private $ticketModel;

    private $db;



    public function __construct()

    {

        // Note: These models might need their own `require_once` in api.php if autoloading fails.

        $this->profileModel = new ProfileModel();

        $this->restaurantModel = new Restaurant();

        $this->tokenModel = new Token();

        $this->ticketModel = new Ticket();

        $this->db = Database::getInstance();

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

    public function getExtensionUser()
    {
        header('Content-Type: application/json');

        // Get token from header
        $token = $_SERVER['HTTP_X_EXT_TOKEN'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate token
        if (!$this->tokenModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Get token info to retrieve user_id
        $tokenInfo = $this->tokenModel->getTokenInfo($token);

        if (!$tokenInfo) {
            http_response_code(401);
            echo json_encode(['error' => 'Token not found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $tokenInfo['user_id'];

        // Get user information with team details
        $userData = $this->getUserWithTeam($userId);

        if (!$userData) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Update token activity (extend its life)
        $this->tokenModel->updateTokenActivity($token);

        // Return user data
        echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function getUserWithTeam($userId)
    {
        try {
            // Get user basic info with team information
            $sql = "SELECT
                        u.id,
                        u.name,
                        u.username,
                        t.id as team_id,
                        t.name as team_name,
                        tl.id as team_leader_id,
                        tl.name as team_leader_name
                    FROM users u
                    LEFT JOIN team_members tm ON u.id = tm.user_id
                    LEFT JOIN teams t ON tm.team_id = t.id
                    LEFT JOIN users tl ON t.team_leader_id = tl.id
                    WHERE u.id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            // Format response according to specification
            return [
                'id' => (int)$result['id'],
                'name' => $result['name'],
                'username' => $result['username'],
                'team' => $result['team_id'] ? [
                    'id' => (int)$result['team_id'],
                    'name' => $result['team_name'],
                    'leader' => [
                        'id' => (int)$result['team_leader_id'],
                        'name' => $result['team_leader_name']
                    ]
                ] : null
            ];

        } catch (PDOException $e) {
            error_log("Error getting user with team: " . $e->getMessage());
            return null;
        }
    }

    public function createTicketFromExtension()
    {
        header('Content-Type: application/json');

        // Get token from header
        $token = $_SERVER['HTTP_X_EXT_TOKEN'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate token
        if (!$this->tokenModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Get token info to retrieve user_id
        $tokenInfo = $this->tokenModel->getTokenInfo($token);

        if (!$tokenInfo) {
            http_response_code(401);
            echo json_encode(['error' => 'Token not found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = $tokenInfo['user_id'];

        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            return;
        }

        try {
            // Check if ticket exists by number only (not requiring ticket details)
            $ticketExists = $this->checkTicketExistsByNumber($input['ticket_number']);

            if ($ticketExists) {
                // Get the ticket ID
                $ticketData = $this->getTicketByNumberOnly($input['ticket_number']);
                if (!$ticketData) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to retrieve ticket data']);
                    return;
                }

                // Create new ticket detail based on existing ticket
                $result = $this->createTicketDetailFromExtension($ticketData['id'], $input, $userId);
            } else {
                // Create new ticket - validate required fields
                $requiredFields = ['ticket_number', 'platform_id', 'category_id', 'subcategory_id', 'code_id'];
                foreach ($requiredFields as $field) {
                    if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: {$field}"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
                    }
                }
                $result = $this->createNewTicketFromExtension($input, $userId);
            }

            if ($result['success']) {
                // Update token activity
                $this->tokenModel->updateTokenActivity($token);

                echo json_encode([
                    'success' => true,
                    'message' => $ticketExists ? 'Ticket detail created successfully' : 'Ticket created successfully',
                    'ticket_id' => $result['ticket_id'],
                    'ticket_detail_id' => $result['ticket_detail_id']
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $result['message']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            error_log("Error creating ticket from extension: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    private function createTicketViaApi($data)
    {
        $this->db->beginTransaction();

        try {
            // Use Cairo-based exception: if Cairo time between 00:00-06:00, store -1 day; else store now (saved in UTC)
            $utcTimestamp = $this->getCurrentUTCWithCustomerException();

            $ticketSql = "INSERT INTO tickets (ticket_number, created_by, created_at) VALUES (:ticket_number, :created_by, :created_at)";
            $stmt = $this->db->prepare($ticketSql);
            $stmt->execute([
                ':ticket_number' => $data['ticket_number'],
                ':created_by' => $data['user_id'],
                ':created_at' => $utcTimestamp
            ]);
            $ticketId = $this->db->lastInsertId();

            // Create ticket detail
            $ticketDetailId = $this->createTicketDetailViaApi($ticketId, $data);

            // Handle VIP assignment if needed
            if (!empty($data['is_vip']) && !empty($data['marketer_id'])) {
                $this->db->prepare("INSERT INTO ticket_vip_assignments (ticket_detail_id, marketer_id) VALUES (:ticket_detail_id, :marketer_id)")
                    ->execute([
                        ':ticket_detail_id' => $ticketDetailId,
                        ':marketer_id' => $data['marketer_id']
                    ]);
            }

            // Handle coupons if provided
            if (!empty($data['coupons'])) {
                $this->processCouponsViaApi($ticketId, $ticketDetailId, $data);
            }

            $this->db->commit();

            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'ticket_detail_id' => $ticketDetailId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in createTicketViaApi: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create ticket'];
        }
    }

    private function createTicketDetailViaApi($ticketId, $data)
    {
        $utcTimestamp = $this->getCurrentUTCWithCustomerException();

        // Get current team ID for the user
        $teamId = $this->getCurrentTeamIdForUser($data['user_id']);

        $stmt = $this->db->prepare(
            "INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, created_by, edited_by, team_id_at_action, created_at, updated_at)
             VALUES (:ticket_id, :is_vip, :platform_id, :phone, :category_id, :subcategory_id, :code_id, :notes, :country_id, :assigned_team_leader_id, :created_by, :edited_by, :team_id_at_action, :created_at, :updated_at)"
        );

        $stmt->execute([
            ':ticket_id' => $ticketId,
            ':is_vip' => $data['is_vip'] ?? 0,
            ':platform_id' => $data['platform_id'],
            ':phone' => $data['phone'] ?? null,
            ':category_id' => $data['category_id'],
            ':subcategory_id' => $data['subcategory_id'],
            ':code_id' => $data['code_id'],
            ':notes' => $data['notes'] ?? null,
            ':country_id' => $data['country_id'] ?? null,
            ':assigned_team_leader_id' => $data['assigned_team_leader_id'],
            ':created_by' => $data['user_id'],
            ':edited_by' => $data['user_id'],
            ':team_id_at_action' => $teamId,
            ':created_at' => $utcTimestamp,
            ':updated_at' => $utcTimestamp
        ]);

        return $this->db->lastInsertId();
    }

    private function processCouponsViaApi($ticketId, $ticketDetailId, $data)
    {
        foreach ($data['coupons'] as $couponId) {
            if (empty($couponId)) continue;

            // Update coupon status
            $stmt = $this->db->prepare("UPDATE coupons SET is_used = 1, used_by = :user_id, used_in_ticket = :ticket_id, used_at = :used_at, held_by = NULL, held_at = NULL, used_for_phone = :phone WHERE id = :coupon_id");
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':ticket_id' => $ticketId,
                ':used_at' => $this->getCurrentUTCWithCustomerException(),
                ':phone' => $data['phone'] ?? null,
                ':coupon_id' => $couponId
            ]);

            // Add to ticket_coupons
            $stmt = $this->db->prepare("INSERT INTO ticket_coupons (ticket_id, ticket_detail_id, coupon_id) VALUES (:ticket_id, :ticket_detail_id, :coupon_id)");
            $stmt->execute([
                ':ticket_id' => $ticketId,
                ':ticket_detail_id' => $ticketDetailId,
                ':coupon_id' => $couponId
            ]);
        }
    }

    private function getCurrentTeamIdForUser($userId)
    {
        $stmt = $this->db->prepare("SELECT t.id FROM teams t JOIN team_members tm ON t.id = tm.team_id WHERE tm.user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    private function createTicketDetailFromExtension($ticketId, $input, $userId)
    {
        $this->db->beginTransaction();

        try {
            // Get the latest ticket detail to use as base (if exists)
            $latestDetail = $this->getLatestTicketDetail($ticketId);

            // If no existing ticket details, this will be the first detail
            // Use input data directly or provide defaults
            $defaultData = [
                'platform_id' => null,
                'category_id' => null,
                'subcategory_id' => null,
                'code_id' => null,
                'phone' => null,
                'notes' => null,
                'country_id' => null,
                'is_vip' => false,
                'marketer_id' => null
            ];

            // Merge input data with existing data (input takes precedence) or defaults
            $baseData = $latestDetail ?: $defaultData;

            // Get team leader for the user
            $teamLeaderId = $this->ticketModel->getTeamLeaderForUser($userId);

            if (empty($teamLeaderId)) {
                throw new \Exception('User is not assigned to a team with a leader');
            }

            $ticketData = [
                'ticket_id' => $ticketId,
                'platform_id' => $input['platform_id'] ?? $baseData['platform_id'],
                'category_id' => $input['category_id'] ?? $baseData['category_id'],
                'subcategory_id' => $input['subcategory_id'] ?? $baseData['subcategory_id'],
                'code_id' => $input['code_id'] ?? $baseData['code_id'],
                'phone' => $input['phone'] ?? $baseData['phone'],
                'notes' => $input['notes'] ?? $baseData['notes'],
                'country_id' => $input['country_id'] ?? $baseData['country_id'],
                'is_vip' => isset($input['is_vip']) ? $input['is_vip'] : ($baseData['is_vip'] ?? false),
                'marketer_id' => $input['marketer_id'] ?? $baseData['marketer_id'],
                'assigned_team_leader_id' => $teamLeaderId,
                'user_id' => $userId,
                'coupons' => $input['coupons'] ?? []
            ];

            // Validate required fields for ticket detail creation
            if (empty($ticketData['platform_id']) || empty($ticketData['category_id']) ||
                empty($ticketData['subcategory_id']) || empty($ticketData['code_id'])) {
                throw new \Exception('Platform, category, subcategory, and code are required for ticket detail creation');
            }

            // Create ticket detail
            $ticketDetailId = $this->createTicketDetailViaApi($ticketId, $ticketData);

            // Handle VIP assignment if applicable
            if ($ticketData['is_vip'] && $ticketData['marketer_id']) {
                $this->db->prepare("INSERT INTO ticket_vip_assignments (ticket_detail_id, marketer_id) VALUES (:ticket_detail_id, :marketer_id)")
                    ->execute([
                        ':ticket_detail_id' => $ticketDetailId,
                        ':marketer_id' => $ticketData['marketer_id']
                    ]);
            }

            // Handle coupons if provided
            if (!empty($ticketData['coupons'])) {
                $this->processCouponsViaApi($ticketId, $ticketDetailId, $ticketData);
            }

            $this->db->commit();

            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'ticket_detail_id' => $ticketDetailId
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in createTicketDetailFromExtension: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function createNewTicketFromExtension($input, $userId)
    {
        // Get team leader for the user
        $teamLeaderId = $this->ticketModel->getTeamLeaderForUser($userId);

        if (empty($teamLeaderId)) {
            return ['success' => false, 'message' => 'User is not assigned to a team with a leader'];
        }

        // Prepare ticket data
        $ticketData = [
            'ticket_number' => $input['ticket_number'],
            'platform_id' => $input['platform_id'],
            'category_id' => $input['category_id'],
            'subcategory_id' => $input['subcategory_id'],
            'code_id' => $input['code_id'],
            'phone' => $input['phone'] ?? null,
            'notes' => $input['notes'] ?? null,
            'country_id' => $input['country_id'] ?? null,
            'is_vip' => $input['is_vip'] ?? false,
            'marketer_id' => $input['marketer_id'] ?? null,
            'assigned_team_leader_id' => $teamLeaderId,
            'user_id' => $userId,
            'coupons' => $input['coupons'] ?? []
        ];

        return $this->createTicketViaApi($ticketData);
    }

    private function getLatestTicketDetail($ticketId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM ticket_details
            WHERE ticket_id = :ticket_id
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getLatestTicketDetailWithNames($ticketId)
    {
        $stmt = $this->db->prepare("
            SELECT
                td.*,
                p.name as platform_name,
                c.name as category_name,
                sc.name as subcategory_name,
                co.name as code_name,
                co2.name as country_name,
                u.name as creator_name,
                tl.name as team_leader_name,
                t.name as team_name,
                t.id as team_id,
                m.name as marketer_name,
                m.id as marketer_id
            FROM ticket_details td
            LEFT JOIN platforms p ON td.platform_id = p.id
            LEFT JOIN ticket_categories c ON td.category_id = c.id
            LEFT JOIN ticket_subcategories sc ON td.subcategory_id = sc.id
            LEFT JOIN ticket_codes co ON td.code_id = co.id
            LEFT JOIN countries co2 ON td.country_id = co2.id
            LEFT JOIN users u ON td.created_by = u.id
            LEFT JOIN users tl ON td.assigned_team_leader_id = tl.id
            LEFT JOIN teams t ON td.team_id_at_action = t.id
            LEFT JOIN ticket_vip_assignments tv ON td.id = tv.ticket_detail_id
            LEFT JOIN users m ON tv.marketer_id = m.id
            WHERE td.ticket_id = :ticket_id
            ORDER BY td.id DESC
            LIMIT 1
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTicketParams($ticketNumber)
    {
        header('Content-Type: application/json');

        // Get token from header
        $token = $_SERVER['HTTP_X_EXT_TOKEN'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate token
        if (!$this->tokenModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Check if ticket exists
            if (!$this->checkTicketExistsByNumber($ticketNumber)) {
                http_response_code(404);
                echo json_encode(['error' => 'Ticket not found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Get ticket data
            $ticket = $this->getTicketByNumberOnly($ticketNumber);

            if (!$ticket) {
                http_response_code(404);
                echo json_encode(['error' => 'Ticket not found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Get latest ticket detail
            $latestDetail = $this->getLatestTicketDetail($ticket['id']);

            if (!$latestDetail) {
                http_response_code(404);
                echo json_encode(['error' => 'No ticket details found'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Return the parameters needed to create a new detail
            echo json_encode([
                'success' => true,
                'ticket_number' => $ticketNumber,
                'current_params' => [
                    'platform_id' => (int)$latestDetail['platform_id'],
                    'category_id' => (int)$latestDetail['category_id'],
                    'subcategory_id' => (int)$latestDetail['subcategory_id'],
                    'code_id' => (int)$latestDetail['code_id'],
                    'phone' => $latestDetail['phone'],
                    'notes' => $latestDetail['notes'],
                    'country_id' => $latestDetail['country_id'] ? (int)$latestDetail['country_id'] : null,
                    'is_vip' => (bool)$latestDetail['is_vip'],
                    'marketer_id' => $latestDetail['marketer_id'] ? (int)$latestDetail['marketer_id'] : null
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting ticket params: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function getExtensionOptions()
    {
        header('Content-Type: application/json');

        // Get token from header
        $token = $_SERVER['HTTP_X_EXT_TOKEN'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate token
        if (!$this->tokenModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Update token activity
            $this->tokenModel->updateTokenActivity($token);
            // Get all platforms
            $platforms = $this->getAllPlatforms();

            // Get hierarchical categories, subcategories, and codes
            $categoriesHierarchy = $this->getCategoriesHierarchy();

            // Get countries
            $countries = $this->getAllCountries();

            // Get active marketers
            $marketers = $this->getAllMarketers();

            // Get active users for assignment
            $activeUsers = $this->getActiveUsers();

            // Get teams
            $teams = $this->getAllTeams();

            echo json_encode([
                'success' => true,
                'data' => [
                    'platforms' => $platforms,
                    'categories' => $categoriesHierarchy,
                    'countries' => $countries,
                    'marketers' => $marketers,
                    'users' => $activeUsers,
                    'teams' => $teams
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting extension options: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    private function getAllPlatforms()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM platforms WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name']
                ];
            }, $result);
        } catch (Exception $e) {
            error_log("Error getting platforms: " . $e->getMessage());
            return [];
        }
    }

    private function getCategoriesHierarchy()
    {
        try {
            // Get categories with their subcategories and codes
            $stmt = $this->db->prepare("
                SELECT
                    c.id as category_id, c.name as category_name,
                    sc.id as subcategory_id, sc.name as subcategory_name,
                    co.id as code_id, co.name as code_name
                FROM ticket_categories c
                LEFT JOIN ticket_subcategories sc ON c.id = sc.category_id
                LEFT JOIN ticket_codes co ON sc.id = co.subcategory_id
                WHERE c.name IS NOT NULL AND c.name != ''
                ORDER BY c.name, sc.name, co.name
            ");
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $categories = [];
            foreach ($results as $row) {
                $catId = $row['category_id'];

                if (!isset($categories[$catId])) {
                    $categories[$catId] = [
                        'id' => (int)$row['category_id'],
                        'name' => $row['category_name'],
                        'subcategories' => []
                    ];
                }

                if ($row['subcategory_id']) {
                    $subId = $row['subcategory_id'];

                    if (!isset($categories[$catId]['subcategories'][$subId])) {
                        $categories[$catId]['subcategories'][$subId] = [
                            'id' => (int)$row['subcategory_id'],
                            'name' => $row['subcategory_name'],
                            'codes' => []
                        ];
                    }

                    if ($row['code_id']) {
                        $categories[$catId]['subcategories'][$subId]['codes'][] = [
                            'id' => (int)$row['code_id'],
                            'name' => $row['code_name']
                        ];
                    }
                }
            }

            // Convert to indexed array
            $result = array_values(array_map(function($category) {
                $category['subcategories'] = array_values($category['subcategories']);
                return $category;
            }, $categories));

            return $result;

        } catch (Exception $e) {
            error_log("Error getting categories hierarchy: " . $e->getMessage());
            return [];
        }
    }

    private function getAllCountries()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name FROM countries WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name']
                ];
            }, $result);
        } catch (Exception $e) {
            error_log("Error getting countries: " . $e->getMessage());
            return [];
        }
    }

    private function getAllMarketers()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.username
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'VIP' AND u.status = 'active' AND u.name IS NOT NULL AND u.name != ''
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'username' => $item['username']
                ];
            }, $result);
        } catch (Exception $e) {
            error_log("Error getting marketers: " . $e->getMessage());
            return [];
        }
    }

    private function getActiveUsers()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.username, r.name as role_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.status = 'active' AND u.name IS NOT NULL AND u.name != ''
                ORDER BY u.name ASC
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'username' => $item['username'],
                    'role' => $item['role_name']
                ];
            }, $result);
        } catch (Exception $e) {
            error_log("Error getting active users: " . $e->getMessage());
            return [];
        }
    }

    private function getAllTeams()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.id, t.name, u.name as leader_name, u.id as leader_id
                FROM teams t
                LEFT JOIN users u ON t.team_leader_id = u.id
                WHERE t.name IS NOT NULL AND t.name != ''
                ORDER BY t.name ASC
            ");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function($item) {
                return [
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'leader' => $item['leader_name'] ? [
                        'id' => (int)$item['leader_id'],
                        'name' => $item['leader_name']
                    ] : null
                ];
            }, $result);
        } catch (Exception $e) {
            error_log("Error getting teams: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Trengo API base URL
     */
    private function getTrengoBaseUrl(): string
    {
        return "https://app.trengo.com/api/v2";
    }

    /**
     * Get Trengo API token
     * TODO: Move this to environment variables or config file
     */
    private function getTrengoToken(): string
    {
        return "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNzc2Y2Y2ODhmZmQ1MDhiMTJhYzE3YzNiMWQ1MzMzNzU1ZDYzMjVlMzMxYWM4ZjFjODIzMDYwMDY4YzQ1OGU5MzBmZTZmNTdmYzcxZTM1MzciLCJpYXQiOjE3NjQyNzUyNTksIm5iZiI6MTc2NDI3NTI1OSwiZXhwIjo0ODg4NDEyODU5LCJzdWIiOiI3MjQ3MTgiLCJzY29wZXMiOltdLCJhZ2VuY3lfaWQiOjIyNTU1fQ.qIwiEZFIIZ5GziIR6NMZR9uE0wLgh_GTiZsH4Oxd5onBflT_aYTug9c5tE3hpXC_grfju0KI_APduTNXcFpb_g";
    }

    /**
     * Make a GET request to Trengo API
     */
    private function trengoGet(string $path): ?array
    {
        $url = $this->getTrengoBaseUrl() . $path;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->getTrengoToken()
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Trengo API cURL Error: " . $curlError);
            return null;
        }

        if ($httpCode >= 400) {
            error_log("Trengo API HTTP Error: Status code {$httpCode}");
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Trengo API JSON Error: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Detect country from phone number
     */
    private function detectCountryFromPhone(?string $phone): ?array
    {
        if (empty($phone)) {
            return null;
        }

        $countryCodes = [
            "+973" => ["id" => 13, "name" => "Bahrain"],
            "+20"  => ["id" => 10, "name" => "Egypt"],
            "+962" => ["id" => 5, "name" => "Jordan"],
            "+965" => ["id" => 8, "name" => "Kuwait"],
            "+961" => ["id" => 9, "name" => "Lebanon"],
            "+968" => ["id" => 7, "name" => "Oman"],
            "+974" => ["id" => 6, "name" => "Qatar"],
            "+963" => ["id" => 11, "name" => "Syria"],
            "+263" => ["id" => 12, "name" => "Zimbabwe"],
        ];

        foreach ($countryCodes as $code => $data) {
            if (strpos($phone, $code) === 0) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Detect platform from contact channels
     */
    private function detectPlatformFromChannels(?array $channels): ?array
    {
        if (empty($channels) || !is_array($channels)) {
            return null;
        }

        $firstChannel = $channels[0] ?? null;
        if (!$firstChannel || !isset($firstChannel['type'])) {
            return null;
        }

        $channelType = strtoupper($firstChannel['type']);

        $platforms = [
            "CALL" => ["id" => 11, "name" => "Call"],
            "EMAIL" => ["id" => 12, "name" => "Email"],
            "FACEBOOK" => ["id" => 8, "name" => "Facebook"],
            "INSTAGRAM" => ["id" => 9, "name" => "instagram"],
            "TELEGRAM" => ["id" => 10, "name" => "Telegram"],
            "WHATSAPP" => ["id" => 7, "name" => "Whatsapp"],
            "VIP" => ["id" => 5, "name" => "VIP 👑"],
            "WA_BUSINESS" => ["id" => 7, "name" => "Whatsapp"],
        ];

        return $platforms[$channelType] ?? null;
    }

    /**
     * Get ticket context from Trengo API
     * This function mimics the Python get_ticket_context function
     */
    private function getTrengoTicketContext(string $ticketNumber): ?array
    {
        try {
            // Try to extract ticket ID from ticket number
            // If ticket_number is like "0000000000002", we need to extract the ID
            $ticketId = ltrim($ticketNumber, '0');
            if (empty($ticketId)) {
                // If all zeros, try the original number
                $ticketId = $ticketNumber;
            }

            // First, try to get ticket info to verify it exists
            $ticketInfo = $this->trengoGet("/tickets/{$ticketId}");
            if (!$ticketInfo) {
                // If direct ticket ID doesn't work, try the original ticket number
                if ($ticketId !== $ticketNumber) {
                    $ticketInfo = $this->trengoGet("/tickets/{$ticketNumber}");
                    if ($ticketInfo) {
                        $ticketId = $ticketNumber;
                    }
                }
                if (!$ticketInfo) {
                    return ["error" => "ticket not found in trengo"];
                }
            }

            // 1) Fetch ticket messages
            $messagesData = $this->trengoGet("/tickets/{$ticketId}/messages");
            if (!$messagesData || !isset($messagesData['data']) || empty($messagesData['data'])) {
                return ["error" => "no messages"];
            }

            $messages = $messagesData['data'];
            $firstMessage = $messages[0] ?? null;
            
            if (!$firstMessage || !isset($firstMessage['contact']['id'])) {
                return ["error" => "invalid message data"];
            }

            $contactId = $firstMessage['contact']['id'];

            // 2) Fetch contact
            $contact = $this->trengoGet("/contacts/{$contactId}");
            if (!$contact) {
                return ["error" => "contact not found"];
            }

            // 3) Get phone
            $phone = null;
            if (isset($contact['is_phone']) && $contact['is_phone'] && isset($contact['phone'])) {
                $phone = $contact['phone'];
            }

            // 4) Detect platform
            $platform = $this->detectPlatformFromChannels($contact['channels'] ?? null);

            // 5) Detect country
            $country = $this->detectCountryFromPhone($phone);

            return [
                "contact_id" => $contactId,
                "phone" => $phone,
                "platform" => $platform,
                "country" => $country
            ];

        } catch (\Exception $e) {
            error_log("Error getting Trengo ticket context: " . $e->getMessage());
            return ["error" => "trengo_api_error"];
        }
    }

    public function getTicketDetails($ticketNumber)
    {
        header('Content-Type: application/json');

        // Get token from header
        $token = $_SERVER['HTTP_X_EXT_TOKEN'] ?? '';

        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token is required'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        // Validate token
        if (!$this->tokenModel->isTokenValid($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // Check if ticket exists
            if (!$this->checkTicketExistsByNumber($ticketNumber)) {
                // Try to get ticket info from Trengo as helper data
                $trengoInfo = $this->getTrengoTicketContext($ticketNumber);
                
                $response = [
                    'success' => false,
                    'message' => 'Ticket not found',
                    'ticket_number' => $ticketNumber
                ];
                
                // Add Trengo helper info if available
                if ($trengoInfo && !isset($trengoInfo['error'])) {
                    $response['helper'] = $trengoInfo;
                }
                
                echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Get ticket data
            $ticket = $this->getTicketByNumberOnly($ticketNumber);

            if (!$ticket) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ticket not found',
                    'ticket_number' => $ticketNumber
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Get latest ticket detail with full information
            $latestDetail = $this->getLatestTicketDetailWithNames($ticket['id']);

            if (!$latestDetail) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No ticket details found',
                    'ticket_number' => $ticketNumber
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // Update token activity
            $this->tokenModel->updateTokenActivity($token);

            // Return the complete ticket detail information
            echo json_encode([
                'success' => true,
                'ticket_number' => $ticketNumber,
                'ticket_detail' => [
                    'id' => (int)$latestDetail['id'],
                    'name' => $latestDetail['detail_name'] ?? 'Ticket Detail #' . $latestDetail['id'],
                    'platform' => [
                        'id' => (int)$latestDetail['platform_id'],
                        'name' => $latestDetail['platform_name']
                    ],
                    'category' => [
                        'id' => (int)$latestDetail['category_id'],
                        'name' => $latestDetail['category_name']
                    ],
                    'subcategory' => [
                        'id' => (int)$latestDetail['subcategory_id'],
                        'name' => $latestDetail['subcategory_name']
                    ],
                    'code' => [
                        'id' => (int)$latestDetail['code_id'],
                        'name' => $latestDetail['code_name']
                    ],
                    'phone' => $latestDetail['phone'],
                    'notes' => $latestDetail['notes'],
                    'country' => $latestDetail['country_id'] ? [
                        'id' => (int)$latestDetail['country_id'],
                        'name' => $latestDetail['country_name']
                    ] : null,
                    'is_vip' => (bool)$latestDetail['is_vip'],
                    'marketer' => $latestDetail['marketer_id'] ? [
                        'id' => (int)$latestDetail['marketer_id'],
                        'name' => $latestDetail['marketer_name']
                    ] : null,
                    'assigned_team_leader' => $latestDetail['assigned_team_leader_id'] ? [
                        'id' => (int)$latestDetail['assigned_team_leader_id'],
                        'name' => $latestDetail['team_leader_name']
                    ] : null,
                    'team' => $latestDetail['team_id'] ? [
                        'id' => (int)$latestDetail['team_id'],
                        'name' => $latestDetail['team_name']
                    ] : null,
                    'created_by' => [
                        'id' => (int)$latestDetail['created_by'],
                        'name' => $latestDetail['creator_name']
                    ],
                    'created_at' => $latestDetail['created_at'],
                    'updated_at' => $latestDetail['updated_at']
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            error_log("Error getting ticket details: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    private function checkTicketExistsByNumber(string $ticketNumber): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_number = :ticket_number");
            $stmt->execute([':ticket_number' => $ticketNumber]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error checking ticket existence: " . $e->getMessage());
            return false;
        }
    }

    private function getTicketByNumberOnly(string $ticketNumber): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, ticket_number, created_by, created_at FROM tickets WHERE ticket_number = :ticket_number LIMIT 1");
            $stmt->execute([':ticket_number' => $ticketNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error getting ticket by number: " . $e->getMessage());
            return null;
        }
    }

    private function getCurrentUTCWithCustomerException(): string
    {
        $utcNow = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $cairoTime = $utcNow->setTimezone(new \DateTimeZone('Africa/Cairo'));
        $hour = (int) $cairoTime->format('H');

        if ($hour >= 0 && $hour < 5) {
            $cairoTime = $cairoTime->modify('-1 day');
        }

        $finalUtc = $cairoTime->setTimezone(new \DateTimeZone('UTC'));
        return $finalUtc->format('Y-m-d H:i:s');
    }

}

