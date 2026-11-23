<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;
use Fitmeal\Core\{DB,Security,View};
class AssessmentController {
 public function show(): void { Security::requireAuth(); View::render('customer/assessment',['title'=>'Your meal assessment']); }
 public function store(): void {
  $user=Security::requireAuth(); Security::verifyCsrf();
  $height=(float)($_POST['height_cm']??0); $weight=(float)($_POST['weight_kg']??0); $age=(int)($_POST['age']??0);
  $food=$_POST['food_preference']??''; $goal=$_POST['goal']??''; $region=trim($_POST['region']??''); $allergies=trim($_POST['allergies']??''); $medical=($_POST['medical_review']??'')==='yes';
  $allowedFood=['vegetarian','eggetarian','non_vegetarian','vegan']; $allowedGoal=['healthy_eating','weight_management','meal_organization'];
  if ($height<100 || $height>230 || $weight<25 || $weight>300 || $age<18 || $age>100 || !in_array($food,$allowedFood,true) || !in_array($goal,$allowedGoal,true)) { $_SESSION['error']='Please check your assessment details. This initial service is for adults aged 18 and above.'; Security::redirect('/assessment'); }
  $screen=($medical || $allergies!=='')?'professional_review':'pending';
  $code='FMA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
  $answers=['age'=>$age,'height_cm'=>$height,'weight_kg'=>$weight,'food_preference'=>$food,'goal'=>$goal,'region'=>$region,'allergies'=>$allergies,'medical_review_requested'=>$medical,'activity_level'=>$_POST['activity_level']??'sedentary','daily_food_budget'=>(int)($_POST['daily_food_budget']??0),'consent_service'=>isset($_POST['consent_service'])];
  if (!$answers['consent_service']) { $_SESSION['error']='Please provide consent to process the information required for this service.'; Security::redirect('/assessment'); }
  $pdo=DB::connection(); $pdo->beginTransaction();
  try {
   $p=$pdo->prepare('INSERT INTO user_profiles(user_id,height_cm,weight_kg,food_preference,city) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE height_cm=VALUES(height_cm),weight_kg=VALUES(weight_kg),food_preference=VALUES(food_preference),city=VALUES(city)'); $p->execute([$user['id'],$height,$weight,$food,$region]);
   $s=$pdo->prepare('INSERT INTO assessments(user_id,assessment_code,goal,daily_food_budget,region,answers,screening_status,status,submitted_at) VALUES(?,?,?,?,?,?,?,"submitted",NOW())');
   $s->execute([$user['id'],$code,$goal,$answers['daily_food_budget'],$region,json_encode($answers,JSON_THROW_ON_ERROR),$screen]); $assessmentId=(int)$pdo->lastInsertId();
   $c=$pdo->prepare('INSERT INTO user_consents(user_id,consent_type,granted,policy_version,created_at) VALUES(?,"service_data_processing",1,"v1",NOW())'); $c->execute([$user['id']]);
   $pdo->commit(); $_SESSION['success']='Assessment submitted. Reference: '.$code; Security::redirect('/dashboard');
  } catch (\Throwable $e) { $pdo->rollBack(); throw $e; }
 }
}
