<?php

namespace App\Controllers\Admin;

use App\Models\Admin\CarType;
use App\Core\Auth;
use App\Core\Controller;

class CarTypesController extends Controller {

    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        $carTypeModel = $this->model('Admin/CarType');
        $data = [
            'car_types' => $carTypeModel->getAll()
        ];
        
        $this->view('admin/car_types/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            
            if (!empty($name)) {
                $carTypeModel = $this->model('Admin/CarType');
                if ($carTypeModel->create($name)) {
                    flash('car_type_message', 'Car type added successfully.', 'success');
                } else {
                    flash('car_type_message', 'Failed to add car type. It may already exist.', 'error');
                }
            } else {
                flash('car_type_message', 'Car type name cannot be empty.', 'error');
            }
        }
        redirect('/admin/car_types');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $carTypeModel = $this->model('Admin/CarType');
            if ($carTypeModel->delete($id)) {
                flash('car_type_message', 'Car type deleted successfully.', 'success');
            } else {
                flash('car_type_message', 'Failed to delete car type.', 'error');
            }
        }
        redirect('/admin/car_types');
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
            
            if (!empty($name)) {
                $carTypeModel = $this->model('Admin/CarType');
                if ($carTypeModel->update($id, $name)) {
                    flash('car_type_message', 'Car type updated successfully.', 'success');
                } else {
                    flash('car_type_message', 'Failed to update car type. The name may already exist.', 'error');
                }
            } else {
                flash('car_type_message', 'Car type name cannot be empty.', 'error');
            }
        }
        redirect('/admin/car_types');
    }
} 