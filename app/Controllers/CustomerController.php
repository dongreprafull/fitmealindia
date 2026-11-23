<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class CustomerController {
 public function dashboard(): void {
  $u=Security::requireAuth();
  $pdo=DB::connection();
  $s=$pdo->prepare('SELECT id,assessment_code,status,screening_status,submitted_at FROM assessments WHERE user_id=? ORDER BY id DESC LIMIT 5'); $s->execute([$u['id']]);
  $plan=$pdo->prepare('SELECT * FROM generated_plans WHERE user_id=? AND source="assessment" ORDER BY id DESC LIMIT 1'); $plan->execute([$u['id']]); $latestPlan=$plan->fetch();
  if ($latestPlan) { $latestPlan['plan_json']=json_decode($latestPlan['plan_json'],true,512,JSON_THROW_ON_ERROR); }
  View::render('customer/dashboard',['title'=>'My dashboard','assessments'=>$s->fetchAll(),'latestPlan'=>$latestPlan]);
 }
 public function planDetail(array $params): void {
  $u=Security::requireAuth();
  $id=(int)($params['id']??0);
  $s=DB::connection()->prepare('SELECT * FROM generated_plans WHERE id=? AND user_id=? LIMIT 1'); $s->execute([$id,$u['id']]); $plan=$s->fetch();
  if (!$plan) { http_response_code(404); View::render('public/404',['title'=>'Plan not found']); return; }
  $plan['plan_json']=json_decode($plan['plan_json'],true,512,JSON_THROW_ON_ERROR);
  View::render('customer/plan',['title'=>'My meal plan','plan'=>$plan]);
 }
}
