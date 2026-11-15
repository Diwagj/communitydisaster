<?php
require_once __DIR__ . '/includes/auth.php';
requireStaff();
$role=staff()['role'];
$modules=[
['incidents.php','Incident Reports','Review reports submitted by the public.',['admin','bdrrmc','official','responder']],
['response.php','Emergency Response','Coordinate response assignments and progress.',['admin','bdrrmc','responder']],
['evacuation.php','Evacuation Management','View centers, evacuees, and occupancy.',['admin','bdrrmc','official','responder']],
['households.php','Household Registry','Confidential household and resident records.',['admin']],
['inventory.php','Relief Inventory','Manage relief goods and stock movements.',['admin','bdrrmc']],
['distribution.php','Relief Distribution','Record and review relief distributions.',['admin','bdrrmc','official']],
['manage-announcements.php','Announcement Management','Publish public advisories.',['admin','bdrrmc','official']],
['reports.php','Disaster Reports','Review system summaries and reports.',['admin','bdrrmc','official']],
['users.php','User Accounts','Create and manage staff accounts.',['admin']]
];
pageStart('Staff Dashboard');
echo '<div class="info-banner" style="margin-bottom:18px">Signed in as <strong>'.h(staff()['full_name']).'</strong> · '.h(roleLabel($role)).'. Your permissions determine which modules you can open.</div>';
echo '<div class="module-grid">';
foreach($modules as [$url,$title,$desc,$allowed]){
 echo '<article class="module-card"><h3>'.h($title).'</h3><p>'.h($desc).'</p>';
 if(in_array($role,$allowed,true)) echo '<a class="button" href="'.h($url).'">Open module</a>';
 else echo '<span class="locked">🔒 Restricted for your role</span>';
 echo '</article>';
}
echo '</div>';
pageEnd();
