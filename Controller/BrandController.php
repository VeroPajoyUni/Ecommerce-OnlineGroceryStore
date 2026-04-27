<?php

/**
 * Controller Brand — AnimaMarket
 *
 * Gestión de marcas (admin) y filtrado de productos por marca (cliente).
 */

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Model/Brand.php';
require_once __DIR__ . '/../Model/Product.php';
require_once __DIR__ . '/../Model/ProductImage.php';

class BrandController extends Controller {

    private $model;

    public function __construct(){
        parent::__construct();
        $this->model = new Brand($this->db);
    }

    // ── ADMIN ──────────────────────────────────────────

    public function index(){
        $brands = $this->model->all();
        $this->render('admin/brand/BrandIndexView', compact('brands'));
    }

    public function create(){
        $this->render('admin/brand/BrandCreateView');
    }

    public function store(){
        if(empty($_POST['nombre'])){
            $this->redirect('brand', 'create', ['error' => 'campo_vacio']);
        }
        $this->model->nombre = $_POST['nombre'];
        $this->model->create();
        $this->redirect('brand', 'index');
    }

    public function edit(){
        $id    = $_GET['id'] ?? null;
        $brand = $this->model->find($id);

        if(!$brand){
            $this->redirect('brand', 'index');
        }

        $this->render('admin/brand/BrandEditView', compact('brand'));
    }

    public function update(){
        if(empty($_POST['id']) || empty($_POST['nombre'])){
            $this->redirect('brand', 'index');
        }
        $this->model->id     = $_POST['id'];
        $this->model->nombre = $_POST['nombre'];
        $this->model->update();
        $this->redirect('brand', 'index');
    }

    public function delete(){
        $id = $_GET['id'] ?? null;
        $this->model->delete($id);
        $this->redirect('brand', 'index');
    }

    // ── CLIENTE ────────────────────────────────────────

    /**
     * Muestra productos filtrados por nombre de marca.
     * Ruta: index.php?controller=brand&action=show&nombre=Coca-Cola
     */
    public function show(){
        $nombre       = $_GET['nombre'] ?? '';
        $productModel = new Product($this->db);
        $imageModel   = new ProductImage($this->db);
        $productos    = $productModel->getByBrand($nombre);
        $categorias   = $productModel->getCategorias();

        foreach($productos as &$p){
            $imagenes    = $imageModel->getByProduct($p['id']);
            $p['imagen'] = $imagenes[0]['url'] ?? null;
        }

        $this->render('client/ClientBrands',
            compact('productos', 'categorias', 'nombre')
        );
    }

    /**
     * Lista todas las marcas para la vista del cliente.
     * Ruta: index.php?controller=brand&action=shop
     */
    public function shop(){
        $brands     = $this->model->all();
        $categorias = (new Product($this->db))->getCategorias();
        $this->render('client/ClientBrands', compact('brands', 'categorias'));
    }
}