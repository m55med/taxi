<?php



namespace App\Models\Reports\Documents;



use App\Core\Database;

use PDO;




// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

class DocumentsReport

{

    private $db;

    private $baseQuery = "FROM driver_documents_required ddr

                          JOIN drivers d ON ddr.driver_id = d.id

                          JOIN document_types dt ON ddr.document_type_id = dt.id

                          LEFT JOIN users u ON ddr.updated_by = u.id";



    public function __construct()

    {

        $this->db = Database::getInstance();

    }



    public function getStaffMembers()

    {

        $staffSql = "SELECT id, username FROM users WHERE role_id IN (SELECT id FROM roles WHERE name IN ('admin', 'quality_manager'))";

        $staffStmt = $this->db->prepare($staffSql);

        $staffStmt->execute();

        return $staffStmt->fetchAll(PDO::FETCH_ASSOC);

    }



    public function countDocuments($filters = [])

    {

        $queryParts = $this->buildQuery($filters);

        $sql = "SELECT COUNT(ddr.id) {$this->baseQuery} {$queryParts['where']}";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($queryParts['params']);

        return $stmt->fetchColumn();

    }



    public function getPaginatedDocuments($limit, $offset, $filters = [])

    {

        $queryParts = $this->buildQuery($filters);

        

        $sql = "SELECT

                    d.name as driver_name,

                    dt.name as document_type,

                    ddr.status as verification_status,

                    u.username as verified_by_name,

                    ddr.updated_at as verified_at,

                    ddr.note as verification_notes

                {$this->baseQuery}

                {$queryParts['where']}

                ORDER BY ddr.updated_at DESC

                LIMIT {$limit} OFFSET {$offset}";



        $stmt = $this->db->prepare($sql);

        $stmt->execute($queryParts['params']);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



        // تحويل التواريخ للعرض بالتوقيت المحلي


        return convert_dates_for_display($results, ['created_at', 'updated_at']);

    }



    private function buildQuery($filters)

    {

        $whereConditions = [];

        $params = [];

        

        if (!empty($filters['document_type'])) {

            // The view filter sends 'id', 'license' etc. We map them to DB values

            $docTypeMap = [

                'id' => 'Personal ID',

                'license' => 'Driver\'s licence',

                'vehicle_registration' => 'Vehicle\'s licence',

                'insurance' => 'Operating licence' // Assuming this mapping

            ];

            if(isset($docTypeMap[$filters['document_type']])) {

                $whereConditions[] = "dt.name = ?";

                $params[] = $docTypeMap[$filters['document_type']];

            }

        }

        if (!empty($filters['verification_status'])) {

            $statusMap = ['pending' => 'missing', 'verified' => 'submitted', 'rejected' => 'rejected'];

            if(isset($statusMap[$filters['verification_status']])) {

                 $whereConditions[] = "ddr.status = ?";

                 $params[] = $statusMap[$filters['verification_status']];

            }

        }

        if (!empty($filters['verified_by'])) {

            $whereConditions[] = "ddr.updated_by = ?";

            $params[] = $filters['verified_by'];

        }

        if (!empty($filters['original_date_from'])) {

            $whereConditions[] = "DATE(CONVERT_TZ(ddr.updated_at, '+00:00', '+02:00')) >= ?";

            $params[] = $filters['original_date_from'];

        }

        if (!empty($filters['original_date_to'])) {

            $whereConditions[] = "DATE(CONVERT_TZ(ddr.updated_at, '+00:00', '+02:00')) <= ?";

            $params[] = $filters['original_date_to'];

        }



        return [

            'where' => !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "",

            'params' => $params

        ];

    }

} 