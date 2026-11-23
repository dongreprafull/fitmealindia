<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\View;
class HomeController { public function index(): void { View::render('public/home', ['title' => 'Personalized Indian Meal Planning']); } public function plans(): void { View::render('public/plans', ['title' => 'Plans & Pricing']); } public function bmi(): void { View::render('public/bmi', ['title' => 'BMI Calculator']); } public function how(): void { View::render('public/how', ['title' => 'How it works']); } public function privacy(): void { View::render('public/privacy', ['title' => 'Privacy information']); } public function terms(): void { View::render('public/terms', ['title' => 'Terms of service']); } }
