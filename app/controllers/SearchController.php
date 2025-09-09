<?php

namespace App\Controllers;

use App\Core\Controller;

class SearchController extends Controller
{
    private $searchModel;

    public function __construct()
    {
        parent::__construct();
        $this->searchModel = $this->model('Search/SearchModel');
    }

    /**
     * Main search page
     */
    public function index()
    {
        // Check if there's a search query
        $query = $_GET['q'] ?? '';

        if (!empty($query)) {
            // Perform search and redirect to first result if exact match
            $results = $this->searchModel->searchTicketsAndPhones($query, 1);

            if (!empty($results)) {
                // Redirect to the first result
                header('Location: ' . $results[0]['url']);
                exit;
            }
        }

        // If no query or no results, show search page
        $data = [
            'page_title' => 'Search',
            'query' => $query,
            'results' => []
        ];

        $this->view('search/index', $data);
    }

    /**
     * API endpoint for search suggestions/autocomplete
     */
    public function suggestions()
    {
        header('Content-Type: application/json');

        try {
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? 'ticket'; // ticket or all
            $limit = (int)($_GET['limit'] ?? 5);

            if (empty($query) || strlen($query) < 2) {
                echo json_encode(['suggestions' => []]);
                return;
            }

            $suggestions = $this->searchModel->getSearchSuggestions($query, $limit);

            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions,
                'query' => $query
            ]);

        } catch (\Exception $e) {
            error_log('Search suggestions error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Search failed',
                'suggestions' => []
            ]);
        }
    }

    /**
     * Perform search and return results
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        $limit = (int)($_GET['limit'] ?? 20);

        if (empty($query)) {
            $data = [
                'page_title' => 'Search Results',
                'query' => '',
                'results' => [],
                'total' => 0
            ];
            $this->view('search/results', $data);
            return;
        }

        $results = $this->searchModel->searchTicketsAndPhones($query, $limit);

        $data = [
            'page_title' => 'Search Results',
            'query' => $query,
            'results' => $results,
            'total' => count($results)
        ];

        $this->view('search/results', $data);
    }
}
