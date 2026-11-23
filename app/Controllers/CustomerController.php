<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class CustomerController {
 public function dashboard(): void { $u=Security::requireAuth(); $s=DB::connection()->prepare('SELECT id,assessment_code,status,screening_status,submitted_at FROM assessments WHERE user_id=? ORDER BY id DESC LIMIT 5'); $s->execute([$u['id']]); View::render('customer/dashboard',['title'=>'My dashboard','assessments'=>$s->fetchAll()]); }
}
