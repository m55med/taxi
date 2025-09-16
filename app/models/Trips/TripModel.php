<?php

namespace App\Models\Trips;

use App\Core\Model;
use PDO;
use Exception;

class TripModel extends Model
{
    /**
     * Bulk insert/update a chunk of trips.
     *
     * @param array $tripsChunk A chunk of trips data.
     * @return array An array with counts of inserted, updated, and errored rows.
     */
    public function processChunk(array $tripsChunk): array
    {
        if (empty($tripsChunk)) {
            return ['inserted' => 0, 'updated' => 0, 'errors' => 0];
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'errors' => 0];
        $columns = $this->getTableColumns();
        
        // --- Start: New logic to accurately count inserts vs. updates ---
        
        // 1. Extract all order_ids from the incoming chunk
        $orderIdsInChunk = array_column($tripsChunk, 'order_id');
        $orderIdsInChunk = array_filter($orderIdsInChunk); // Remove nulls/empty values

        // 2. Find out which of these order_ids already exist in the database
        $existingOrderIds = [];
        if (!empty($orderIdsInChunk)) {
            $placeholders = rtrim(str_repeat('?,', count($orderIdsInChunk)), ',');
            $sqlCheck = "SELECT order_id FROM trips WHERE order_id IN ($placeholders)";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute($orderIdsInChunk);
            $existingOrderIds = $stmtCheck->fetchAll(PDO::FETCH_COLUMN);
        }
        $existingOrderIds = array_flip($existingOrderIds); // Flip for fast O(1) lookups using isset()

        // --- End: New logic ---

        // Prepare a list of columns for the ON DUPLICATE KEY UPDATE part
        $updateColumns = [];
        foreach ($columns as $column) {
            // Don't update the primary key on duplicate
            if ($column !== 'order_id') { 
                $updateColumns[] = "`$column` = VALUES(`$column`)";
            }
        }

        $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
        $sql = "INSERT INTO trips (`" . implode('`,`', $columns) . "`) VALUES ($placeholders)
                ON DUPLICATE KEY UPDATE " . implode(', ', $updateColumns);
        
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare($sql);

            foreach ($tripsChunk as $trip) {
                try {
                    // This will throw an exception if order_id is missing, which is caught below
                    $sanitizedTrip = $this->sanitizeAndPrepareRow($trip, $columns);
                    $values = array_values($sanitizedTrip);

                    $stmt->execute($values);
                    
                    // Use our pre-fetched list for accurate counting instead of rowCount()
                    if (isset($existingOrderIds[$sanitizedTrip['order_id']])) {
                        $stats['updated']++;
                    } else {
                        $stats['inserted']++;
                    }

                } catch(\Exception $rowException) {
                    error_log('TripModel Row Processing Error: ' . $rowException->getMessage() . ' for row: ' . json_encode($trip));
                    $stats['errors']++;
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            // If the whole transaction fails, mark all rows in chunk as errored
            $stats['errors'] += count($tripsChunk) - ($stats['inserted'] + $stats['updated']);
            error_log('TripModel Chunk Transaction Error: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Sanitizes and prepares a single row of data to match DB schema.
     */
    private function sanitizeAndPrepareRow(array $row, array $columns): array
    {
        $preparedRow = [];
        $normalizedRow = [];
        
        // Normalize incoming keys (from excel/csv)
        foreach($row as $key => $value) {
            $normalizedKey = strtolower(str_replace([' ', '-'], '_', $key));
            $normalizedRow[$normalizedKey] = $value;
        }

        // Ensure all DB columns exist in the row with null as default
        foreach ($columns as $column) {
            $preparedRow[$column] = $normalizedRow[$column] ?? null;
        }
        
        // Data Type and Format sanitization
        $dateFields = ['created_at', 'requested_pickup_time', 'started_at', 'arrived_at', 'loaded_at', 'finished_at', 'closed_at'];
        foreach ($dateFields as $field) {
            $preparedRow[$field] = $this->parseDate($preparedRow[$field]);
        }
        
        if (isset($preparedRow['active'])) {
            $preparedRow['active'] = filter_var($preparedRow['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $numericFields = [
            'dropoffs_count', 'passengers_number', 'offer_count', 'reject_count', 'total_bid_count',
            'driver_bid_count', 'dispatcher_bid_count', 'passenger_cancellation_fee_omr', 'driver_cancellation_fee_omr',
            'trip_cost_omr', 'extra_cost_omr', 'total_cost_omr', 'coupon_discount_omr', 'tips_omr',
            'bonus_amount_omr', 'including_tax_omr', 'tax_omr', 'transactional_fee_omr', 'final_cost_omr',
            'unpaid_cost_omr', 'rounding_correction_value_omr', 'excess_payment_omr', 'rating_by_driver',
            'rating_by_passenger', 'price_multiplier'
        ];
        foreach ($numericFields as $field) {
            if (isset($preparedRow[$field])) {
                // Trim whitespace, as ' 123 ' is numeric but might cause issues.
                $value = trim($preparedRow[$field]);

// تحميل DateTime Helper للتعامل مع التوقيت
require_once APPROOT . '/helpers/DateTimeHelper.php';

                // Ensure that empty strings are treated as null, not 0.
                if ($value === '' || !is_numeric($value)) {
                    $preparedRow[$field] = null;
                } else {
                    // It's a valid numeric string, keep it as is.
                    // The PDO driver will handle it correctly.
                    $preparedRow[$field] = $value;
                }
            }
        }
        
        if (empty($preparedRow['order_id'])) {
            // If order_id is missing, we cannot insert it. This is a critical error for this row.
            throw new Exception("Missing required 'order_id' for a row.");
        }

        return $preparedRow;
    }

    /**
     * Parses a date string (from Excel/CSV) and converts it to 'Y-m-d H:i:s'.
     */
    public function parseDate($dateValue): ?string
    {
        if (empty($dateValue)) return null;
        
        // Handle Excel's numeric date format
        if (is_numeric($dateValue) && $dateValue > 25569) { // Basic check to avoid non-date numbers
            return gmdate("Y-m-d H:i:s", ($dateValue - 25569) * 86400);
        }

        if (is_string($dateValue)) {
            try {
                // Try to parse it as a standard date format
                return (new \DateTime($dateValue))->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Log if parsing fails, but don't stop the whole process
                error_log("Could not parse date: " . $dateValue);
            }
        }
        return null;
    }
    
    /**
     * Gets the column names for the 'trips' table from the database schema.
     */
    private function getTableColumns(): array
    {
        return [
            'order_id', 'created_at', 'author_id', 'order_source', 'bundle', 'requested_vehicle_type',
            'requested_pickup_time', 'origin_type', 'origin_location', 'origin_address', 'destination_type',
            'destination_location', 'destination_address', 'dropoff_type', 'dropoff_location', 'dropoff_address',
            'dropoffs_count', 'order_notes', 'passengers_number', 'client_documents', 'driver_payment_documents',
            'passenger_id', 'passenger_name', 'passenger_email', 'passenger_phone', 'passenger_operator_id',
            'passenger_operator_name', 'passenger_operator_email', 'driver_operator_id', 'driver_operator_name',
            'driver_operator_email', 'driver_id', 'driver_custom_key', 'driver_name', 'driver_email', 'driver_phone',
            'vehicle_type', 'vehicle_plate_number', 'vehicle_board_number', 'estimation_time', 'estimation_distance',
            'driver_rate_plan', 'offer_count', 'reject_count', 'total_bid_count', 'driver_bid_count',
            'dispatcher_bid_count', 'order_status', 'unpaid_reason', 'cancellation_reason', 'cancellation_comment',
            'trip_distance_km', 'trip_time', 'intermediate_driver_ids', 'passenger_cancellation_fee_omr',
            'driver_cancellation_fee_omr', 'trip_cost_omr', 'extra_cost_omr', 'total_cost_omr',
            'coupon_discount_omr', 'tips_omr', 'bonus_amount_omr', 'including_tax_omr', 'tax_omr',
            'transactional_fee_omr', 'final_cost_omr', 'unpaid_cost_omr', 'rounding_correction_value_omr',
            'excess_payment_omr', 'payment_method', 'payment_card', 'corporate_account', 'payment_errors',
            'rating_by_driver', 'rating_by_passenger', 'started_at', 'arrived_at', 'loaded_at', 'finished_at',
            'closed_at', 'service_space', 'active', 'linked_order', 'price_multiplier',
            'coupon_code', 'promo_campaign_name'
            // The location columns were removed from the hardcoded list as they are not in the provided CREATE TABLE.
            // 'started_location', 'arrived_location', 'loaded_location', 'finished_location', 'closed_location'
        ];
    }
} 