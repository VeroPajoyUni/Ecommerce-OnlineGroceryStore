<?php

/**
 * Controller Address — AnimaMarket
 *
 * Gestión de direcciones de entrega del cliente logueado.
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Model/Address.php';

class AddressController extends Controller {

    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = new Address($this->db);
    }

    public function index(){
        $user_id   = $_SESSION['user_id'];
        $addresses = $this->model->getByUser($user_id);
        $this->render('client/addresses/AddressIndexView', compact('addresses'));
    }

    public function create(){
        $this->render('client/addresses/AddressCreateView');
    }

    public function store(){
        $this->model->user_id    = $_SESSION['user_id'];
        $this->model->direccion  = $_POST['direccion'];
        $this->model->ciudad     = $_POST['ciudad'];
        $this->model->barrio     = $_POST['barrio']     ?? '';
        $this->model->referencia = $_POST['referencia'] ?? '';
        $this->model->create();
        $this->redirect('address', 'index');
    }

    public function edit(){
        $id      = $_GET['id'] ?? null;
        $address = $this->model->find($id);

        if(!$address){
            $this->redirect('address', 'index');
        }

        $this->render('client/addresses/AddressEditView', compact('address'));
    }

    public function update(){
        $this->model->id         = $_POST['id'];
        $this->model->direccion  = $_POST['direccion'];
        $this->model->ciudad     = $_POST['ciudad'];
        $this->model->barrio     = $_POST['barrio']     ?? '';
        $this->model->referencia = $_POST['referencia'] ?? '';
        $this->model->update();
        $this->redirect('address', 'index');
    }

    public function delete(){
        $id = $_GET['id'] ?? null;
        $this->model->delete($id);
        $this->redirect('address', 'index');
    }
}