<?php
require_once __DIR__ . '/includes/auth.php';
$errors=[]; $success='';
$values=['reporter_name'=>'','reporter_contact'=>'','incident_type'=>'','description'=>'','location'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
    checkCsrf();
    foreach($values as $k=>$v){$values[$k]=trim((string)($_POST[$k]??''));}
    if($values['reporter_name']==='' || strlen($values['reporter_name'])>100) $errors[]='Enter your name (maximum 100 characters).';
    if($values['reporter_contact']!=='' && strlen($values['reporter_contact'])>20) $errors[]='Contact number must be 20 characters or fewer.';
    if(!in_array($values['incident_type'],['flood','fire','landslide','road_obstruction','other'],true)) $errors[]='Select a valid incident type.';
    if($values['description']==='' || strlen($values['description'])>10000) $errors[]='Provide an incident description (maximum 10,000 characters).';
    if($values['location']==='' || strlen($values['location'])>255) $errors[]='Provide the incident location (maximum 255 characters).';
    if(!$errors){
        try{
            $stmt=db()->prepare("INSERT INTO incidents (reported_by,reporter_name,reporter_contact,incident_type,description,location,status) VALUES (NULL,?,?,?,?,?,'reported')");
            $stmt->execute([$values['reporter_name'],$values['reporter_contact']?:null,$values['incident_type'],$values['description'],$values['location']]);
            $success='Your incident report has been submitted. The barangay team will review the information.';
            $values=['reporter_name'=>'','reporter_contact'=>'','incident_type'=>'','description'=>'','location'=>''];
        }catch(Throwable $e){error_log($e->getMessage());$errors[]='The report could not be submitted. Please try again later or contact the barangay directly.';}
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Report Incident | Surong Guisad</title><link rel="stylesheet" href="assets/style.css"></head><body><?php publicHeader('report-incident.php'); ?><main class="section"><div class="wrap"><div class="section-head"><div><div class="kicker">Community reporting</div><h2>Submit an Incident Report</h2></div><p>This form is open to the public. You do not need an account. Do not use this form instead of contacting emergency services during immediate danger.</p></div><div class="panel" style="max-width:780px">
<?php if($success): ?><div class="notice success"><?= h($success) ?></div><?php endif; ?>
<?php if($errors): ?><div class="notice error"><strong>Please correct the following:</strong><ul><?php foreach($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="report-incident.php"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><div class="form-grid">
<div class="field"><label for="reporter_name">Your name *</label><input id="reporter_name" name="reporter_name" maxlength="100" value="<?= h($values['reporter_name']) ?>" required></div>
<div class="field"><label for="reporter_contact">Contact number (optional)</label><input id="reporter_contact" name="reporter_contact" maxlength="20" value="<?= h($values['reporter_contact']) ?>"></div>
<div class="field"><label for="incident_type">Incident type *</label><select id="incident_type" name="incident_type" required><option value="">Select type</option><?php foreach(['flood'=>'Flood','fire'=>'Fire','landslide'=>'Landslide','road_obstruction'=>'Road obstruction','other'=>'Other'] as $k=>$v): ?><option value="<?= h($k) ?>" <?= $values['incident_type']===$k?'selected':'' ?>><?= h($v) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="location">Incident location *</label><input id="location" name="location" maxlength="255" value="<?= h($values['location']) ?>" placeholder="Street, purok, or landmark" required></div>
<div class="field full"><label for="description">What happened? *</label><textarea id="description" name="description" required><?= h($values['description']) ?></textarea></div>
</div><button class="button" type="submit">Submit report</button></form></div></div></main><footer class="footer"><div class="wrap"><a href="index.php">← Back to homepage</a></div></footer></body></html>
