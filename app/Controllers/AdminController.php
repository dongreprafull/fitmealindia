<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class AdminController {
 public function dashboard(): void {
  Security::requireRole(['admin','nutritionist','support']); $pdo=DB::connection();
  $stats=['customers'=>(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn(),'orders'=>(int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),'assessments'=>(int)$pdo->query('SELECT COUNT(*) FROM assessments')->fetchColumn(),'review'=>(int)$pdo->query("SELECT COUNT(*) FROM assessments WHERE screening_status='professional_review'")->fetchColumn()];
  $orders=$pdo->query('SELECT o.order_number,o.status,o.total_paise,o.created_at,u.name,u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 10')->fetchAll();
  $assessments=$pdo->query('SELECT a.assessment_code,a.status,a.screening_status,a.submitted_at,u.name FROM assessments a JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 10')->fetchAll();
  View::render('admin/dashboard',['title'=>'Admin dashboard','stats'=>$stats,'orders'=>$orders,'assessments'=>$assessments]);
 }
}
