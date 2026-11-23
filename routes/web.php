<?php
use Fitmeal\Controllers\{HomeController,AuthController,AssessmentController,CustomerController,AdminController};
$router->get('/', [HomeController::class,'index']);
$router->get('/plans', [HomeController::class,'plans']);
$router->get('/bmi-calculator', [HomeController::class,'bmi']);
$router->get('/how-it-works', [HomeController::class,'how']);
$router->get('/privacy-policy', [HomeController::class,'privacy']);
$router->get('/terms', [HomeController::class,'terms']);
$router->get('/register', [AuthController::class,'register']);
$router->post('/register', [AuthController::class,'store']);
$router->get('/login', [AuthController::class,'login']);
$router->post('/login', [AuthController::class,'authenticate']);
$router->post('/logout', [AuthController::class,'logout']);
$router->get('/assessment', [AssessmentController::class,'show']);
$router->post('/assessment', [AssessmentController::class,'store']);
$router->get('/dashboard', [CustomerController::class,'dashboard']);
$router->get('/admin', [AdminController::class,'dashboard']);
