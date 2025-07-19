<?php

namespace App\Controllers\Admin;

use App\Models\Admin\Country;
use App\Core\Auth;
use App\Core\Controller;

class CountriesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $countryModel = $this->model('Admin/Country');
        $data = [
            'countries' => $countryModel->getAll()
        ];
        
        $this->view('admin/countries/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            
            if (!empty($name)) {
                $countryModel = $this->model('Admin/Country');
                if ($countryModel->create($name)) {
                    flash('country_message', 'Country added successfully.', 'success');
                } else {
                    flash('country_message', 'Failed to add country. It may already exist.', 'error');
                }
            } else {
                flash('country_message', 'Country name cannot be empty.', 'error');
            }
        }
        redirect('/admin/countries');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $countryModel = $this->model('Admin/Country');
            if ($countryModel->delete($id)) {
                flash('country_message', 'Country deleted successfully.', 'success');
            } else {
                flash('country_message', 'Failed to delete country.', 'error');
            }
        }
        redirect('/admin/countries');
    }
}
