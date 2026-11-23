<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class AuthController {
 public function register(): void { View::render('auth/register', ['title'=>'Create account']); }
 public function store(): void {
  Security::verifyCsrf(); $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $mobile=trim($_POST['mobile']??''); $password=$_POST['password']??'';
  if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12) { $_SESSION['error']='Enter a name, valid email and password of at least 12 characters.'; Security::redirect('/register'); }
  try { $pdo=DB::connection(); $s=$pdo->prepare("INSERT INTO users(name,email,mobile,password_hash,role,status) VALUES(?,?,?,?, 'customer','active')"); $s->execute([$name,$email,$mobile ?: null,password_hash($password,PASSWORD_DEFAULT)]); $newId=(int)$pdo->lastInsertId(); $profile=$pdo->prepare('INSERT INTO user_profiles(user_id) VALUES(?)'); $profile->execute([$newId]); session_regenerate_id(true); $_SESSION['user']=['id'=>$newId,'name'=>$name,'email'=>$email,'role'=>'customer']; Security::redirect('/dashboard'); }
  catch (\PDOException $e) { $_SESSION['error']='This email or mobile may already be registered. Please try logging in.'; Security::redirect('/register'); }
 }
 public function login(): void { View::render('auth/login',['title'=>'Login']); }
 public function authenticate(): void {
  Security::verifyCsrf(); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
  $s=DB::connection()->prepare('SELECT id,name,email,password_hash,role,status FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); $u=$s->fetch();
  if (!$u || $u['status']!=='active' || !password_verify($password,$u['password_hash'])) { $_SESSION['error']='Invalid login details.'; Security::redirect('/login'); }
  session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];
  Security::redirect(in_array($u['role'],['admin','nutritionist','support'],true)?'/admin':'/dashboard');
 }
 public function logout(): void { Security::verifyCsrf(); $_SESSION=[]; session_regenerate_id(true); Security::redirect('/'); }
}
