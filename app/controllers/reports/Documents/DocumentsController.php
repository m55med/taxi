<?php



namespace App\Controllers\Reports\Documents;



use App\Core\Controller;



class DocumentsController extends Controller

{

    private $documentsReportModel;



    public function __construct()

    {

        parent::__construct();

        $this->initialize();

    }



    private function initialize()

    {

        $this->documentsReportModel = $this->model('Reports/Documents/DocumentsReport');

    }



    public function index()

    {

        $filters = [

            'document_type' => $_GET['document_type'] ?? '',

            'verification_status' => $_GET['verification_status'] ?? '',

            'verified_by' => $_GET['verified_by'] ?? '',

            'date_from' => $_GET['date_from'] ?? '',

            'date_to' => $_GET['date_to'] ?? ''

        ];

        // ======= تحويل التواريخ من Cairo إلى UTC قبل الاستعلام =======
        // حفظ التواريخ الأصلية للعرض
        $filters['original_date_from'] = $filters['date_from'];
        $filters['original_date_to'] = $filters['date_to'];

        // تحويل التواريخ من Cairo إلى UTC للبحث في قاعدة البيانات
        if (!empty($filters['date_from'])) {
            $dateFromCairo = new \DateTimeImmutable($filters['date_from'] . ' 00:00:00', new \DateTimeZone('Africa/Cairo'));
            $filters['date_from'] = $dateFromCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }

        if (!empty($filters['date_to'])) {
            $dateToCairo = new \DateTimeImmutable($filters['date_to'] . ' 23:59:59', new \DateTimeZone('Africa/Cairo'));
            $filters['date_to'] = $dateToCairo->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d');
        }



        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;

        $offset = ($page - 1) * $limit;



        $totalRecords = $this->documentsReportModel->countDocuments($filters);

        $totalPages = ceil($totalRecords / $limit);



        $documents = $this->documentsReportModel->getPaginatedDocuments($limit, $offset, $filters);

        $staff_members = $this->documentsReportModel->getStaffMembers();



        $data = [

            'documents' => $documents,

            'staff_members' => $staff_members,

            'pagination' => [

                'page' => $page,

                'limit' => $limit,

                'total_pages' => $totalPages,

                'total_records' => $totalRecords

            ],

            'filters' => $filters

        ];



        $this->view('reports/Documents/index', $data);

    }

}

