<?php
require_once __DIR__ . '/app/controllers/AdopcionesController.php';
$controller = new AdopcionesController();
$action = $_GET['action'] ?? 'create';

switch ($action) {
  case 'create':
  default:
    $controller->create();
    break;
}