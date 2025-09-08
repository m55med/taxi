<?php



namespace App\Controllers\Admin;



use App\Models\Admin\Country;

use App\Core\Auth;

use App\Core\Controller;



class CountriesController extends Controller {



    private $countryModel;



    public function __construct() {

        Auth::checkAdmin();

        $this->countryModel = $this->model('Admin/Country');

    }



    public function index() {

        $data = [

            'countries' => $this->countryModel->getAll()

        ];

        

        $this->view('admin/countries/index', $data);

    }



    public function store() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));

            

            if (!empty($name)) {

                if ($this->countryModel->create($name)) {

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



    public function delete() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            if(empty($id)) {
                 flash('country_message', 'Invalid country ID.', 'error');
                 redirect('/admin/countries');
                 return;
            }

            $deleteResult = $this->countryModel->delete($id);

            if ($deleteResult === true) {
                flash('country_message', 'Country deleted successfully.', 'success');
            } else {
                // If it's not true, it's an error message string from the model
                flash('country_message', $deleteResult, 'error');
            }
        }

        redirect('/admin/countries');
    }
}

