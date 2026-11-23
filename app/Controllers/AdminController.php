<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class AdminController {
 public function dashboard(): void {
  Security::requireRole(['admin','nutritionist','support']); $pdo=DB::connection();
  $stats=[
    'customers'=>(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),
    'orders'=>(int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'assessments'=>(int)$pdo->query('SELECT COUNT(*) FROM assessments')->fetchColumn(),
    'review'=>(int)$pdo->query("SELECT COUNT(*) FROM assessments WHERE screening_status='professional_review'")->fetchColumn(),
    'plans'=>(int)$pdo->query('SELECT COUNT(*) FROM generated_plans')->fetchColumn(),
    'leads'=>(int)$pdo->query("SELECT COUNT(*) FROM generated_plans WHERE source='lead'")->fetchColumn(),
  ];
  $orders=$pdo->query('SELECT o.order_number,o.status,o.total_paise,o.created_at,u.name,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 10')->fetchAll();
  $assessments=$pdo->query('SELECT a.id,a.assessment_code,a.status,a.screening_status,a.submitted_at,u.name FROM assessments a JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 10')->fetchAll();
  View::render('admin/dashboard',['title'=>'Admin dashboard','stats'=>$stats,'orders'=>$orders,'assessments'=>$assessments]);
 }

 public function users(): void {
  Security::requireRole(['admin','nutritionist','support']); $pdo=DB::connection();
  $users=$pdo->query('SELECT id,name,email,mobile,role,status,created_at FROM users ORDER BY id DESC LIMIT 100')->fetchAll();
  View::render('admin/users',['title'=>'Users','users'=>$users]);
 }

 public function updateUserStatus(array $params): void {
  Security::requireRole(['admin']); Security::verifyCsrf();
  $id=(int)($params['id']??0); $status=$_POST['status']??'';
  if (!in_array($status,['active','blocked'],true)) { Security::redirect('/admin/users'); }
  $pdo=DB::connection(); $s=$pdo->prepare('UPDATE users SET status=? WHERE id=? AND role="customer"'); $s->execute([$status,$id]);
  $_SESSION['success']='User status updated.'; Security::redirect('/admin/users');
 }

 public function assessmentShow(array $params): void {
  Security::requireRole(['admin','nutritionist','support']); $pdo=DB::connection();
  $id=(int)($params['id']??0);
  $s=$pdo->prepare('SELECT a.*,u.name,u.email,u.mobile FROM assessments a JOIN users u ON u.id=a.user_id WHERE a.id=? LIMIT 1'); $s->execute([$id]); $assessment=$s->fetch();
  if (!$assessment) { http_response_code(404); View::render('public/404',['title'=>'Not found']); return; }
  $assessment['answers']=json_decode($assessment['answers'],true,512,JSON_THROW_ON_ERROR);
  $p=$pdo->prepare('SELECT * FROM generated_plans WHERE assessment_id=? ORDER BY id DESC LIMIT 1'); $p->execute([$id]); $plan=$p->fetch();
  if ($plan) { $plan['plan_json']=json_decode($plan['plan_json'],true,512,JSON_THROW_ON_ERROR); }
  View::render('admin/assessment',['title'=>'Assessment '.$assessment['assessment_code'],'assessment'=>$assessment,'plan'=>$plan]);
 }

 public function updateAssessmentStatus(array $params): void {
  Security::requireRole(['admin','nutritionist','support']); Security::verifyCsrf();
  $id=(int)($params['id']??0);
  $status=$_POST['status']??''; $screening=$_POST['screening_status']??'';
  $allowedStatus=['draft','submitted','reviewed','closed'];
  $allowedScreening=['pending','eligible','professional_review','referred','rejected'];
  if (!in_array($status,$allowedStatus,true) || !in_array($screening,$allowedScreening,true)) { Security::redirect('/admin/assessments/'.$id); }
  $pdo=DB::connection(); $s=$pdo->prepare('UPDATE assessments SET status=?,screening_status=? WHERE id=?'); $s->execute([$status,$screening,$id]);
  $_SESSION['success']='Assessment updated.'; Security::redirect('/admin/assessments/'.$id);
 }
}
