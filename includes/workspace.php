<?php
require_once __DIR__ . '/auth.php';

$module = (string) ($moduleKey ?? '');

$configs = [
    'households' => [
        'title' => 'Household and Resident Registry',
        'permission' => 'households',
        'table' => 'households',
        'id' => 'household_id',
        'fields' => [
            'household_head' => ['Household head', 'text', 100],
            'member_count' => ['Number of members', 'number', 3],
            'address' => ['Address', 'text', 255],
            'location_description' => ['Location description', 'optional_text', 255],
            'vulnerability_class' => ['Vulnerability classification', 'select', ['none', 'senior_citizen', 'pwd', 'pregnant', 'infant', 'low_income', 'multiple']],
            'emergency_contact_name' => ['Emergency contact name', 'optional_text', 100],
            'emergency_contact_no' => ['Emergency contact number', 'optional_text', 20],
        ],
    ],
    'centers' => [
        'title' => 'Evacuation Center Management',
        'permission' => 'evacuation',
        'table' => 'evacuation_centers',
        'id' => 'center_id',
        'fields' => [
            'center_name' => ['Center name', 'text', 100],
            'address' => ['Address', 'text', 255],
            'capacity' => ['Capacity', 'number', 6],
            'status' => ['Status', 'select', ['open', 'full', 'closed']],
            'contact_no' => ['Contact number', 'optional_text', 20],
        ],
    ],
    'items' => [
        'title' => 'Relief Goods Inventory',
        'permission' => 'inventory',
        'table' => 'relief_items',
        'id' => 'item_id',
        'fields' => [
            'item_name' => ['Item name', 'text', 100],
            'category' => ['Category', 'select', ['food_pack', 'drinking_water', 'medicine', 'blanket', 'other']],
            'unit' => ['Unit (e.g. packs, bottles, boxes)', 'text', 20],
        ],
    ],
    'announcements' => [
        'title' => 'Emergency Announcement Management',
        'permission' => 'announcements',
        'table' => 'announcements',
        'id' => 'announcement_id',
        'fields' => [
            'title' => ['Title', 'text', 150],
            'message' => ['Announcement details', 'textarea', 5000],
            'category' => ['Category', 'select', ['advisory', 'evacuation_instruction', 'emergency', 'preparedness']],
        ],
    ],
    'evacuees' => [
        'title' => 'Evacuee Registration',
        'permission' => 'evacuation',
        'table' => 'evacuees',
        'id' => 'evacuee_id',
        'fields' => [
            'center_id' => ['Evacuation center', 'center', 0],
            'household_id' => ['Linked household (optional)', 'household_select', 11],
            'incident_id' => ['Related incident (optional)', 'incident_select', 11],
            'evacuee_name' => ['Evacuee full name', 'text', 100],
            'age' => ['Age', 'number_optional', 3],
            'sex' => ['Sex', 'select_optional', ['M', 'F']],
        ],
    ],
];

if ($module === 'reports') {
    $config = ['title' => 'Disaster Reports & Analytics', 'permission' => 'reports', 'table' => 'incidents', 'id' => 'incident_id'];
} elseif ($module === 'incidents') {
    $config = ['title' => 'Disaster Incident Reporting', 'permission' => 'incidents', 'table' => 'incidents', 'id' => 'incident_id'];
} elseif ($module === 'response') {
    $config = ['title' => 'Emergency Response Management', 'permission' => 'response', 'table' => 'incidents', 'id' => 'incident_id'];
} elseif ($module === 'distribution') {
    $config = ['title' => 'Relief Distribution Management', 'permission' => 'distribution', 'table' => 'relief_distributions', 'id' => 'distribution_id'];
} else {
    $config = $configs[$module] ?? null;
}

if (!$config) {
    http_response_code(404);
    exit('Unknown module.');
}

requirePermission($config['permission'] . '.view');

$pdo = db();
$notice = '';
$error = '';
$editRecord = null;
$search = trim((string) ($_GET['q'] ?? ''));

function fieldValue(string $name): string
{
    return trim((string) ($_POST[$name] ?? ''));
}

function renderField(string $name, array $field, array $record = []): void
{
    [$label, $type, $limit] = $field;
    $value = (string) ($record[$name] ?? '');

    echo '<div class="field"><label for="' . h($name) . '">' . h($label) . '</label>';

    if ($type === 'textarea') {
        echo '<textarea id="' . h($name) . '" name="' . h($name) . '" rows="4" required>' . h($value) . '</textarea>';
    } elseif ($type === 'select' || $type === 'select_optional') {
        $req = $type === 'select' ? 'required' : '';
        echo '<select id="' . h($name) . '" name="' . h($name) . '" ' . $req . '><option value="">Select</option>';
        foreach ($limit as $option) {
            $sel = $value === $option ? 'selected' : '';
            echo '<option value="' . h($option) . '" ' . $sel . '>' . h(ucwords(str_replace('_', ' ', $option))) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'center') {
        $rows = db()->query("SELECT center_id, center_name, capacity FROM evacuation_centers WHERE status = 'open' ORDER BY center_name")->fetchAll();
        echo '<select id="center_id" name="center_id" required><option value="">Select open center</option>';
        foreach ($rows as $r) {
            $sel = $value === (string) $r['center_id'] ? 'selected' : '';
            echo '<option value="' . (int) $r['center_id'] . '" ' . $sel . '>' . h($r['center_name']) . ' (Cap: ' . (int) $r['capacity'] . ')</option>';
        }
        echo '</select>';
    } elseif ($type === 'household_select') {
        $rows = db()->query('SELECT household_id, household_head, address FROM households ORDER BY household_head')->fetchAll();
        echo '<select id="' . h($name) . '" name="' . h($name) . '"><option value="">None / Unregistered</option>';
        foreach ($rows as $r) {
            $sel = $value === (string) $r['household_id'] ? 'selected' : '';
            echo '<option value="' . (int) $r['household_id'] . '" ' . $sel . '>' . h($r['household_head'] . ' — ' . $r['address']) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'incident_select') {
        $rows = db()->query("SELECT incident_id, incident_type, location FROM incidents WHERE status != 'false_report' ORDER BY incident_id DESC")->fetchAll();
        echo '<select id="' . h($name) . '" name="' . h($name) . '"><option value="">None / General</option>';
        foreach ($rows as $r) {
            $sel = $value === (string) $r['incident_id'] ? 'selected' : '';
            $ref = formatRef('INC', (int) $r['incident_id']);
            echo '<option value="' . (int) $r['incident_id'] . '" ' . $sel . '>' . h($ref . ' — ' . ucfirst($r['incident_type']) . ' (' . $r['location'] . ')') . '</option>';
        }
        echo '</select>';
    } else {
        $inputType = str_contains($type, 'number') ? 'number' : 'text';
        $extra = '';
        if ($type === 'number') {
            $extra = 'min="1" required';
        } elseif ($type === 'number_optional') {
            $extra = 'min="0"';
        } elseif ($type === 'text') {
            $extra = 'required';
        }
        echo '<input id="' . h($name) . '" name="' . h($name) . '" type="' . $inputType . '" maxlength="' . (int) $limit . '" value="' . h($value) . '" ' . $extra . '>';
    }

    echo '</div>';
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string) ($_POST['action'] ?? '');

    try {

        if ($module === 'incidents') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'verify' || $action === 'reject') {
                requirePermission('incidents.verify');
                $newStatus = $action === 'verify' ? 'verified' : 'false_report';

                $stmt = $pdo->prepare(
                    'UPDATE incidents
                     SET status = :status, verified_by = :uid, verified_at = NOW()
                     WHERE incident_id = :id AND status = "reported"'
                );
                $stmt->execute([
                    'status' => $newStatus,
                    'uid' => (int) currentStaff()['user_id'],
                    'id' => $id,
                ]);

                if (!$stmt->rowCount()) {
                    throw new RuntimeException('Only newly reported incidents can be verified or rejected.');
                }

                auditLog($action, 'incident', $id);
                $notice = 'Incident ' . formatRef('INC', $id) . ' marked as ' . $newStatus . '.';

            } elseif ($action === 'link_household') {
                requirePermission('incidents.affected_households');
                $householdId = (int) ($_POST['household_id'] ?? 0);

                if ($householdId < 1) {
                    throw new RuntimeException('Select a valid household to link.');
                }

                $link = $pdo->prepare(
                    'INSERT INTO incident_households (incident_id, household_id, recorded_by)
                     VALUES (:incident, :household, :uid)'
                );

                try {
                    $link->execute([
                        'incident' => $id,
                        'household' => $householdId,
                        'uid' => (int) currentStaff()['user_id'],
                    ]);
                } catch (PDOException $duplicate) {
                    if ((int) ($duplicate->errorInfo[1] ?? 0) === 1062) {
                        throw new RuntimeException('That household is already linked to this incident.');
                    }
                    throw $duplicate;
                }

                auditLog('link_affected_household', 'incident', $id, 'Household ID ' . $householdId);
                $notice = 'Affected household linked to ' . formatRef('INC', $id) . '.';

            } elseif ($action === 'unlink_household') {
                requirePermission('incidents.affected_households');
                $householdId = (int) ($_POST['household_id'] ?? 0);

                $unlink = $pdo->prepare(
                    'DELETE FROM incident_households WHERE incident_id = :incident AND household_id = :household'
                );
                $unlink->execute(['incident' => $id, 'household' => $householdId]);
                auditLog('unlink_affected_household', 'incident', $id, 'Household ID ' . $householdId);
                $notice = 'Household unlinked from ' . formatRef('INC', $id) . '.';

            } elseif ($action === 'edit') {
                requirePermission('incidents.edit');
                $allowedTypes = ['flood', 'fire', 'landslide', 'road_obstruction', 'other'];
                $type = (string) ($_POST['incident_type'] ?? '');
                $location = trim((string) ($_POST['location'] ?? ''));
                $description = trim((string) ($_POST['description'] ?? ''));

                if (!in_array($type, $allowedTypes, true) || $location === '' || $description === '') {
                    throw new RuntimeException('Provide a valid incident type, location, and description.');
                }

                $stmt = $pdo->prepare(
                    'UPDATE incidents
                     SET incident_type = :type, description = :description, location = :location
                     WHERE incident_id = :id'
                );
                $stmt->execute([
                    'type' => $type,
                    'description' => $description,
                    'location' => $location,
                    'id' => $id,
                ]);

                auditLog('edit', 'incident', $id);
                $notice = 'Incident ' . formatRef('INC', $id) . ' updated.';
            }


        } elseif ($module === 'response') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($action === 'assign') {
                requirePermission('response.assign');
                $team = trim((string) ($_POST['response_team'] ?? ''));
                if ($team === '') {
                    throw new RuntimeException('Enter the response team name.');
                }

                $check = $pdo->prepare('SELECT status FROM incidents WHERE incident_id = :id');
                $check->execute(['id' => $id]);
                $currentStatus = $check->fetchColumn();

                if (!in_array($currentStatus, ['verified', 'responding'], true)) {
                    throw new RuntimeException('Incident must be verified before assigning a response team.');
                }

                $assignedTo = ($_POST['assigned_to'] ?? '') !== '' ? (int) $_POST['assigned_to'] : null;

                $stmt = $pdo->prepare(
                    'UPDATE incidents
                     SET response_team = :team, assigned_to = :assigned, status = "responding"
                     WHERE incident_id = :id'
                );
                $stmt->execute([
                    'team' => $team,
                    'assigned' => $assignedTo,
                    'id' => $id,
                ]);

                auditLog('assign_response', 'incident', $id, 'Team: ' . $team);
                $notice = 'Response team assigned to ' . formatRef('INC', $id) . '.';

            } elseif ($action === 'update') {
                requirePermission('response.update');
                $status = (string) ($_POST['status'] ?? '');
                $actionTaken = trim((string) ($_POST['action_taken'] ?? ''));

                if (!in_array($status, ['responding', 'resolved'], true)) {
                    throw new RuntimeException('Invalid response status.');
                }
                if ($actionTaken === '') {
                    throw new RuntimeException('Describe the action taken before updating status.');
                }

                $check = $pdo->prepare('SELECT status FROM incidents WHERE incident_id = :id');
                $check->execute(['id' => $id]);
                $currentStatus = $check->fetchColumn();

                if (!in_array($currentStatus, ['verified', 'responding'], true)) {
                    throw new RuntimeException('Only verified or responding incidents can be updated.');
                }

                $stmt = $pdo->prepare(
                    'UPDATE incidents
                     SET action_taken = :action, status = :status
                     WHERE incident_id = :id'
                );
                $stmt->execute([
                    'action' => $actionTaken,
                    'status' => $status,
                    'id' => $id,
                ]);

                auditLog('update_response', 'incident', $id, 'Status: ' . $status);
                $notice = 'Response progress updated for ' . formatRef('INC', $id) . '.';
            }


        } elseif ($module === 'items' && $action === 'stock') {
            requirePermission('inventory.stock');
            $itemId = (int) ($_POST['item_id'] ?? 0);
            $qty = (int) ($_POST['quantity'] ?? 0);
            $type = (string) ($_POST['movement_type'] ?? '');
            $remarks = trim((string) ($_POST['remarks'] ?? ''));

            if ($qty < 1 || !in_array($type, ['in', 'out'], true)) {
                throw new RuntimeException('Enter a valid stock movement quantity and type.');
            }

            $pdo->beginTransaction();

            $lock = $pdo->prepare('SELECT stock_qty FROM relief_items WHERE item_id = :id FOR UPDATE');
            $lock->execute(['id' => $itemId]);
            $stock = $lock->fetchColumn();

            if ($stock === false) {
                throw new RuntimeException('Relief item not found.');
            }

            $newStock = (int) $stock + ($type === 'in' ? $qty : -$qty);
            if ($newStock < 0) {
                throw new RuntimeException('Insufficient stock for stock-out.');
            }

            $pdo->prepare('UPDATE relief_items SET stock_qty = :qty WHERE item_id = :id')
                ->execute(['qty' => $newStock, 'id' => $itemId]);

            $pdo->prepare(
                'INSERT INTO stock_movements (item_id, movement_type, quantity, remarks, recorded_by)
                 VALUES (:item, :type, :qty, :remarks, :uid)'
            )->execute([
                'item' => $itemId,
                'type' => $type,
                'qty' => $qty,
                'remarks' => $remarks !== '' ? $remarks : null,
                'uid' => (int) currentStaff()['user_id'],
            ]);

            $pdo->commit();
            auditLog('stock_' . $type, 'relief_item', $itemId, 'Quantity ' . $qty);
            $notice = 'Stock movement recorded. Updated balance: ' . $newStock . '.';


        } elseif ($module === 'distribution' && $action === 'release') {
            requirePermission('distribution.create');
            $householdId = (int) ($_POST['household_id'] ?? 0);
            $itemId = (int) ($_POST['item_id'] ?? 0);
            $qty = (int) ($_POST['quantity'] ?? 0);
            $receivedBy = trim((string) ($_POST['received_by'] ?? ''));
            $incidentId = ($_POST['incident_id'] ?? '') !== '' ? (int) $_POST['incident_id'] : null;

            if ($householdId < 1 || $itemId < 1 || $qty < 1 || $receivedBy === '') {
                throw new RuntimeException('Select a household, item, valid quantity, and receiving representative.');
            }

            $pdo->beginTransaction();

            $lock = $pdo->prepare('SELECT stock_qty FROM relief_items WHERE item_id = :id FOR UPDATE');
            $lock->execute(['id' => $itemId]);
            $stock = $lock->fetchColumn();

            if ($stock === false || (int) $stock < $qty) {
                throw new RuntimeException('The selected item has insufficient stock.');
            }

            $distStmt = $pdo->prepare(
                'INSERT INTO relief_distributions (household_id, incident_id, item_id, quantity, received_by, distributed_by)
                 VALUES (:household, :incident, :item, :qty, :received, :uid)'
            );
            $distStmt->execute([
                'household' => $householdId,
                'incident' => $incidentId,
                'item' => $itemId,
                'qty' => $qty,
                'received' => $receivedBy,
                'uid' => (int) currentStaff()['user_id'],
            ]);

            $distributionId = (int) $pdo->lastInsertId();
            $distRef = formatRef('DIST', $distributionId);

            $pdo->prepare('UPDATE relief_items SET stock_qty = stock_qty - :qty WHERE item_id = :item')
                ->execute(['qty' => $qty, 'item' => $itemId]);

            $pdo->prepare(
                'INSERT INTO stock_movements (item_id, movement_type, quantity, remarks, recorded_by)
                 VALUES (:item, "out", :qty, :remarks, :uid)'
            )->execute([
                'item' => $itemId,
                'qty' => $qty,
                'remarks' => 'Relief release ' . $distRef,
                'uid' => (int) currentStaff()['user_id'],
            ]);

            $pdo->commit();
            auditLog('release_relief', 'distribution', $distributionId, $distRef);
            $notice = 'Relief distribution recorded (' . $distRef . '). Stock balance automatically updated.';


        } elseif ($module === 'announcements' && $action === 'toggle') {
            requirePermission('announcements.edit');
            $id = (int) ($_POST['id'] ?? 0);
            $active = (int) ($_POST['active'] ?? 0) === 1 ? 1 : 0;

            $pdo->prepare('UPDATE announcements SET is_active = :active WHERE announcement_id = :id')
                ->execute(['active' => $active, 'id' => $id]);

            auditLog($active ? 'publish' : 'unpublish', 'announcement', $id);
            $notice = $active ? 'Announcement published.' : 'Announcement unpublished.';


        } elseif ($module === 'evacuees' && $action === 'checkout') {
            requirePermission('evacuation.evacuate');
            $id = (int) ($_POST['id'] ?? 0);

            $stmt = $pdo->prepare('UPDATE evacuees SET time_out = NOW() WHERE evacuee_id = :id AND time_out IS NULL');
            $stmt->execute(['id' => $id]);

            $q = $pdo->prepare('SELECT center_id FROM evacuees WHERE evacuee_id = :id');
            $q->execute(['id' => $id]);
            $centerId = $q->fetchColumn();

            if ($centerId) {
                $pdo->prepare('UPDATE evacuation_centers SET status = "open" WHERE center_id = :id AND status = "full"')
                    ->execute(['id' => (int) $centerId]);
            }

            auditLog('evacuee_checkout', 'evacuee', $id);
            $notice = 'Evacuee checked out. Center availability updated.';

        } elseif ($module === 'evacuees' && $action === 'create') {
            requirePermission('evacuation.evacuate');
            $centerId = (int) ($_POST['center_id'] ?? 0);
            $name = trim((string) ($_POST['evacuee_name'] ?? ''));
            $age = ($_POST['age'] ?? '') !== '' ? (int) $_POST['age'] : null;
            $sex = ($_POST['sex'] ?? '') !== '' ? (string) $_POST['sex'] : null;
            $householdId = ($_POST['household_id'] ?? '') !== '' ? (int) $_POST['household_id'] : null;
            $incidentId = ($_POST['incident_id'] ?? '') !== '' ? (int) $_POST['incident_id'] : null;

            if ($name === '' || strlen($name) > 100) {
                throw new RuntimeException('Enter a valid evacuee name.');
            }
            if ($sex !== null && !in_array($sex, ['M', 'F'], true)) {
                throw new RuntimeException('Select a valid sex value.');
            }

            $pdo->beginTransaction();

            $c = $pdo->prepare('SELECT capacity FROM evacuation_centers WHERE center_id = :id AND status = "open" FOR UPDATE');
            $c->execute(['id' => $centerId]);
            $capacity = $c->fetchColumn();

            if ($capacity === false) {
                throw new RuntimeException('Select an open evacuation center.');
            }

            $count = $pdo->prepare('SELECT COUNT(*) FROM evacuees WHERE center_id = :id AND time_out IS NULL');
            $count->execute(['id' => $centerId]);
            $occupants = (int) $count->fetchColumn();

            if ($occupants >= (int) $capacity) {
                throw new RuntimeException('This evacuation center has reached its maximum capacity.');
            }

            $ins = $pdo->prepare(
                'INSERT INTO evacuees (center_id, household_id, incident_id, evacuee_name, age, sex)
                 VALUES (:center, :household, :incident, :name, :age, :sex)'
            );
            $ins->execute([
                'center' => $centerId,
                'household' => $householdId,
                'incident' => $incidentId,
                'name' => $name,
                'age' => $age,
                'sex' => $sex,
            ]);

            $evacueeId = (int) $pdo->lastInsertId();
            $newCount = $occupants + 1;


            $pdo->prepare('UPDATE evacuation_centers SET status = "full" WHERE center_id = :id AND :occupants >= capacity')
                ->execute(['id' => $centerId, 'occupants' => $newCount]);

            $pdo->commit();
            auditLog('evacuee_checkin', 'evacuee', $evacueeId);
            $notice = 'Evacuee registered successfully.';


        } elseif (isset($config['fields']) && in_array($action, ['create', 'update'], true)) {
            $isUpdate = ($action === 'update');
            $permKey = ($module === 'evacuees')
                ? 'evacuation.evacuate'
                : $config['permission'] . ($isUpdate ? '.edit' : '.create');

            requirePermission($permKey);

            $values = [];
            foreach ($config['fields'] as $name => $field) {
                $value = fieldValue($name);
                if (in_array($field[1], ['number', 'number_optional', 'center', 'household_select', 'incident_select'], true)) {
                    $value = ($value === '') ? null : (int) $value;
                }
                if ($field[1] === 'text' && $value === '') {
                    throw new RuntimeException($field[0] . ' is required.');
                }
                if (in_array($field[1], ['select', 'select_optional'], true) && $value !== '' && !in_array($value, $field[2], true)) {
                    throw new RuntimeException('Invalid selection for ' . $field[0] . '.');
                }
                $values[$name] = ($value === '') ? null : $value;
            }

            if ($module === 'announcements' && !$isUpdate) {
                $values['posted_by'] = (int) currentStaff()['user_id'];
                $values['is_active'] = 1;
            }
            if ($module === 'centers' && (int) $values['capacity'] < 1) {
                throw new RuntimeException('Capacity must be at least 1.');
            }
            if ($module === 'households' && (int) $values['member_count'] < 1) {
                throw new RuntimeException('Member count must be at least 1.');
            }

            if ($isUpdate) {
                $id = (int) ($_POST['id'] ?? 0);
                $sets = [];
                foreach (array_keys($values) as $col) {
                    $sets[] = '`' . $col . '` = :' . $col;
                }
                $values['record_id'] = $id;
                $sql = 'UPDATE `' . $config['table'] . '` SET ' . implode(', ', $sets) . ' WHERE `' . $config['id'] . '` = :record_id';
                $pdo->prepare($sql)->execute($values);
                auditLog('edit', $module, $id);
                $notice = 'Record updated successfully.';
            } else {
                $cols = array_keys($values);
                $sql = 'INSERT INTO `' . $config['table'] . '` (`' . implode('`, `', $cols) . '`) VALUES (:' . implode(', :', $cols) . ')';
                $pdo->prepare($sql)->execute($values);
                auditLog('create', $module, (int) $pdo->lastInsertId());
                $notice = 'Record saved successfully.';
            }
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        $error = ($e instanceof RuntimeException)
            ? $e->getMessage()
            : 'The operation could not be completed. Please check your inputs and try again.';
    }
}


if (isset($config['fields']) && isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    requirePermission($config['permission'] . '.edit');
    $q = $pdo->prepare('SELECT * FROM `' . $config['table'] . '` WHERE `' . $config['id'] . '` = :id');
    $q->execute(['id' => $id]);
    $editRecord = $q->fetch() ?: null;
}

$pageTitle = $config['title'];
require __DIR__ . '/staff-layout-start.php';
?>

<?php if ($notice !== ''): ?><div class="notice success"><?= h($notice) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>

<?php if ($module === 'centers'): ?>
    <p><a class="button" href="workspace.php?module=evacuees">Manage evacuee check-in / check-out</a></p>
<?php endif; ?>




<?php if (isset($config['fields'])): ?>
    <section class="panel">
        <h2><?= $editRecord ? 'Edit record' : 'Add new record' ?></h2>
        <?php $canAddOrEdit = hasPermission($module === 'evacuees' ? 'evacuation.evacuate' : $config['permission'] . '.create') || $editRecord; ?>
        <?php if ($canAddOrEdit): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="<?= $editRecord ? 'update' : 'create' ?>">
                <?php if ($editRecord): ?>
                    <input type="hidden" name="id" value="<?= (int) $editRecord[$config['id']] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <?php foreach ($config['fields'] as $name => $field): ?>
                        <?php renderField($name, $field, $editRecord ?? []); ?>
                    <?php endforeach; ?>
                </div>

                <button class="button" type="submit">Save record</button>
                <?php if ($editRecord): ?>
                    <a class="button button-light" href="workspace.php?module=<?= h($module) ?>">Cancel</a>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p class="muted">Your account has view-only access in this module.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>




<?php if ($module === 'incidents'): ?>
    <?php
    $statusFilter = trim((string) ($_GET['status'] ?? ''));
    $where = [];
    $params = [];

    if ($statusFilter !== '' && in_array($statusFilter, ['reported', 'verified', 'responding', 'resolved', 'false_report'], true)) {
        $where[] = 'i.status = :status';
        $params['status'] = $statusFilter;
    } else {
        $statusFilter = '';
    }

    if ($search !== '') {
        $where[] = '(i.location LIKE :s1 OR i.description LIKE :s2 OR i.reporter_name LIKE :s3)';
        $params['s1'] = '%' . $search . '%';
        $params['s2'] = '%' . $search . '%';
        $params['s3'] = '%' . $search . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $stmt = $pdo->prepare(
        "SELECT i.*, u.full_name AS verifier_name
         FROM incidents i
         LEFT JOIN users u ON u.user_id = i.verified_by
         {$whereSql}
         ORDER BY i.reported_at DESC"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $households = $pdo->query('SELECT household_id, household_head, address FROM households ORDER BY household_head')->fetchAll();
    ?>

    <section class="panel">
        <h2>Search & Filter Incidents</h2>
        <form method="get" action="incidents.php">
            <div class="form-grid">
                <div class="field">
                    <label for="q">Search location, details, or reporter</label>
                    <input id="q" name="q" value="<?= h($search) ?>" placeholder="Search incidents...">
                </div>
                <div class="field">
                    <label for="status">Filter by status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        <?php foreach (['reported', 'verified', 'responding', 'resolved', 'false_report'] as $st): ?>
                            <option value="<?= h($st) ?>" <?= $statusFilter === $st ? 'selected' : '' ?>>
                                <?= h(ucwords(str_replace('_', ' ', $st))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button class="button" type="submit">Search</button>
            <?php if ($search !== '' || $statusFilter !== ''): ?>
                <a class="button button-light" href="incidents.php">Reset</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Incident Verification & Affected Household Linking</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ref & Type</th>
                        <th>Details & Reporter</th>
                        <th>Location & Evidence</th>
                        <th>Status & Audit</th>
                        <th>Affected Households</th>
                        <th>Review Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $incId = (int) $r['incident_id'];
                        $linkedStmt = $pdo->prepare(
                            'SELECT h.household_id, h.household_head
                             FROM incident_households ih
                             JOIN households h ON h.household_id = ih.household_id
                             WHERE ih.incident_id = :id
                             ORDER BY h.household_head'
                        );
                        $linkedStmt->execute(['id' => $incId]);
                        $linkedHouseholds = $linkedStmt->fetchAll();
                        ?>
                        <tr>
                            <td>
                                <strong><?= h(formatRef('INC', $incId)) ?></strong><br>
                                <?= h(ucwords(str_replace('_', ' ', $r['incident_type']))) ?><br>
                                <small class="small"><?= h($r['reported_at']) ?></small>
                            </td>
                            <td>
                                <?= nl2br(h($r['description'])) ?><br>
                                <small class="small">
                                    By: <?= h($r['reporter_name'] ?? 'Public visitor') ?>
                                    <?= !empty($r['reporter_contact']) ? ' (' . h($r['reporter_contact']) . ')' : '' ?>
                                </small>
                            </td>
                            <td>
                                <?= h($r['location']) ?>
                                <?php if (!empty($r['image_path'])): ?>
                                    <br><a href="<?= h($r['image_path']) ?>" target="_blank" rel="noopener">View evidence image</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status"><?= h(ucwords(str_replace('_', ' ', $r['status']))) ?></span>
                                <?php if (!empty($r['verifier_name'])): ?>
                                    <br><small class="small">Reviewed by <?= h($r['verifier_name']) ?> at <?= h($r['verified_at']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($linkedHouseholds)): ?>
                                    <ul style="margin: 0 0 8px; padding-left: 16px;">
                                        <?php foreach ($linkedHouseholds as $lh): ?>
                                            <li>
                                                <?= h($lh['household_head']) ?>
                                                <?php if (hasPermission('incidents.affected_households')): ?>
                                                    <form method="post" class="inline-form">
                                                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                                        <input type="hidden" name="action" value="unlink_household">
                                                        <input type="hidden" name="id" value="<?= $incId ?>">
                                                        <input type="hidden" name="household_id" value="<?= (int) $lh['household_id'] ?>">
                                                        <button type="submit" style="border:0;background:none;color:#9b3b3b;cursor:pointer;">[×]</button>
                                                    </form>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <small class="small">No households linked yet.</small><br>
                                <?php endif; ?>

                                <?php if (hasPermission('incidents.affected_households')): ?>
                                    <form method="post" style="margin-top: 6px;">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="link_household">
                                        <input type="hidden" name="id" value="<?= $incId ?>">
                                        <select name="household_id" required>
                                            <option value="">Link household...</option>
                                            <?php foreach ($households as $hh): ?>
                                                <option value="<?= (int) $hh['household_id'] ?>">
                                                    <?= h($hh['household_head'] . ' (' . $hh['address'] . ')') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="button" type="submit" style="margin-top: 4px; padding: 6px 10px;">Link</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'reported' && hasPermission('incidents.verify')): ?>
                                    <?php foreach (['verify' => 'Verify', 'reject' => 'Reject'] as $act => $label): ?>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                            <input type="hidden" name="id" value="<?= $incId ?>">
                                            <input type="hidden" name="action" value="<?= h($act) ?>">
                                            <button class="button <?= $act === 'reject' ? 'button-danger' : '' ?>" type="submit" style="padding: 6px 10px; margin-bottom: 6px;">
                                                <?= h($label) ?>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (hasPermission('incidents.edit')): ?>
                                    <form method="post" style="margin-top: 6px;">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $incId ?>">
                                        <select name="incident_type" style="margin-bottom: 4px;">
                                            <?php foreach (['flood', 'fire', 'landslide', 'road_obstruction', 'other'] as $t): ?>
                                                <option value="<?= h($t) ?>" <?= $r['incident_type'] === $t ? 'selected' : '' ?>>
                                                    <?= h(ucwords(str_replace('_', ' ', $t))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input name="location" value="<?= h($r['location']) ?>" required style="margin-bottom: 4px;">
                                        <textarea name="description" rows="2" required><?= h($r['description']) ?></textarea>
                                        <button class="button button-light" type="submit" style="margin-top: 4px; padding: 6px 10px;">Save edit</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>




<?php elseif ($module === 'response'): ?>
    <?php
    $rows = $pdo->query(
        'SELECT i.*, u.full_name AS assigned_name
         FROM incidents i
         LEFT JOIN users u ON u.user_id = i.assigned_to
         WHERE i.status IN ("verified", "responding")
         ORDER BY i.updated_at DESC'
    )->fetchAll();

    $staff = $pdo->query('SELECT user_id, full_name FROM users WHERE is_active = 1 ORDER BY full_name')->fetchAll();
    ?>

    <section class="panel">
        <h2>Assign Response Teams & Update Field Progress</h2>
        <?php foreach ($rows as $r): ?>
            <?php $incId = (int) $r['incident_id']; ?>
            <article class="card" style="margin-bottom: 14px;">
                <h3><?= h(formatRef('INC', $incId)) ?> — <?= h(ucwords(str_replace('_', ' ', $r['incident_type']))) ?></h3>
                <p>
                    Location: <strong><?= h($r['location']) ?></strong> ·
                    Status: <span class="status"><?= h(ucfirst($r['status'])) ?></span> ·
                    Assigned Lead: <strong><?= h($r['assigned_name'] ?? 'Unassigned') ?></strong>
                </p>
                <p style="margin: 8px 0;"><?= nl2br(h($r['description'])) ?></p>

                <?php if (hasPermission('response.assign')): ?>
                    <form method="post" style="margin-top: 10px;">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="action" value="assign">
                        <input type="hidden" name="id" value="<?= $incId ?>">
                        <div class="form-grid">
                            <div class="field">
                                <label>Response team</label>
                                <input name="response_team" value="<?= h($r['response_team'] ?? '') ?>" placeholder="e.g. BDRRMC Rescue Team Alpha" required>
                            </div>
                            <div class="field">
                                <label>Assign staff lead</label>
                                <select name="assigned_to">
                                    <option value="">Not assigned</option>
                                    <?php foreach ($staff as $s): ?>
                                        <option value="<?= (int) $s['user_id'] ?>" <?= (int) $r['assigned_to'] === (int) $s['user_id'] ? 'selected' : '' ?>>
                                            <?= h($s['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button class="button" type="submit">Save assignment</button>
                    </form>
                <?php endif; ?>

                <?php if (hasPermission('response.update')): ?>
                    <form method="post" style="margin-top: 14px; border-top: 1px solid #edf1f5; padding-top: 10px;">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $incId ?>">
                        <div class="field">
                            <label>Action taken / field progress remarks *</label>
                            <textarea name="action_taken" rows="3" required><?= h($r['action_taken'] ?? '') ?></textarea>
                        </div>
                        <div class="field">
                            <label>Response status</label>
                            <select name="status">
                                <option value="responding" <?= $r['status'] === 'responding' ? 'selected' : '' ?>>Responding</option>
                                <option value="resolved">Resolved (Complete)</option>
                            </select>
                        </div>
                        <button class="button" type="submit">Update response</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>

        <?php if (empty($rows)): ?>
            <p class="empty">No verified incidents are currently awaiting response.</p>
        <?php endif; ?>
    </section>




<?php elseif ($module === 'items'): ?>
    <?php
    $items = $pdo->query('SELECT * FROM relief_items ORDER BY item_name')->fetchAll();
    ?>

    <section class="panel">
        <h2>Current Stock & Low-Stock Monitoring</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Available Balance</th>
                        <th>Stock Status Alert</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $r): ?>
                        <?php $qty = (int) $r['stock_qty']; ?>
                        <tr>
                            <td><?= h($r['item_name']) ?></td>
                            <td><?= h(ucwords(str_replace('_', ' ', $r['category']))) ?></td>
                            <td><?= h($r['unit']) ?></td>
                            <td><strong><?= $qty ?></strong></td>
                            <td>
                                <?php if ($qty === 0): ?>
                                    <span class="status" style="background:#fff0f0;color:#963737;">OUT OF STOCK</span>
                                <?php elseif ($qty <= 10): ?>
                                    <span class="status" style="background:#fff8e6;color:#946200;">LOW STOCK ALERT</span>
                                <?php else: ?>
                                    <span class="status status-open">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (hasPermission('inventory.stock')): ?>
            <h2 style="margin-top: 22px;">Record Stock-In / Stock-Out</h2>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="stock">
                <div class="form-grid">
                    <div class="field">
                        <label>Item</label>
                        <select name="item_id" required>
                            <?php foreach ($items as $r): ?>
                                <option value="<?= (int) $r['item_id'] ?>">
                                    <?= h($r['item_name'] . ' (' . $r['stock_qty'] . ' ' . $r['unit'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Movement type</label>
                        <select name="movement_type">
                            <option value="in">Stock-in (Add)</option>
                            <option value="out">Stock-out (Deduct)</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Quantity</label>
                        <input type="number" min="1" name="quantity" required>
                    </div>
                    <div class="field">
                        <label>Remarks / source</label>
                        <input name="remarks" maxlength="255" placeholder="e.g. DSWD delivery or spoilage">
                    </div>
                </div>
                <button class="button" type="submit">Record movement</button>
            </form>
        <?php endif; ?>

        <h2 style="margin-top: 22px;">Stock Movement History</h2>
        <?php
        $movements = $pdo->query(
            'SELECT m.movement_date, i.item_name, m.movement_type, m.quantity, m.remarks, u.full_name
             FROM stock_movements m
             JOIN relief_items i ON i.item_id = m.item_id
             JOIN users u ON u.user_id = m.recorded_by
             ORDER BY m.movement_date DESC
             LIMIT 100'
        )->fetchAll();
        ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Movement</th>
                        <th>Quantity</th>
                        <th>Remarks</th>
                        <th>Recorded by</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements as $m): ?>
                        <tr>
                            <td><?= h($m['movement_date']) ?></td>
                            <td><?= h($m['item_name']) ?></td>
                            <td><?= strtoupper(h($m['movement_type'])) ?></td>
                            <td><?= (int) $m['quantity'] ?></td>
                            <td><?= h($m['remarks'] ?? '') ?></td>
                            <td><?= h($m['full_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>




<?php elseif ($module === 'distribution'): ?>
    <?php
    $households = $pdo->query('SELECT household_id, household_head, address FROM households ORDER BY household_head')->fetchAll();
    $items = $pdo->query('SELECT item_id, item_name, unit, stock_qty FROM relief_items WHERE stock_qty > 0 ORDER BY item_name')->fetchAll();
    $incidentsList = $pdo->query("SELECT incident_id, incident_type, location FROM incidents WHERE status != 'false_report' ORDER BY incident_id DESC")->fetchAll();
    $history = $pdo->query(
        'SELECT d.distribution_id, d.distribution_date, h.household_head, i.item_name, d.quantity, i.unit, d.received_by, d.incident_id
         FROM relief_distributions d
         JOIN households h ON h.household_id = d.household_id
         JOIN relief_items i ON i.item_id = d.item_id
         ORDER BY d.distribution_date DESC'
    )->fetchAll();
    ?>

    <section class="panel">
        <h2>Release Relief Goods</h2>
        <?php if (hasPermission('distribution.create')): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="release">
                <div class="form-grid">
                    <div class="field">
                        <label>Affected household *</label>
                        <select name="household_id" required>
                            <option value="">Select household</option>
                            <?php foreach ($households as $hrow): ?>
                                <option value="<?= (int) $hrow['household_id'] ?>">
                                    <?= h($hrow['household_head'] . ' — ' . $hrow['address']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Relief item *</label>
                        <select name="item_id" required>
                            <option value="">Select available item</option>
                            <?php foreach ($items as $it): ?>
                                <option value="<?= (int) $it['item_id'] ?>">
                                    <?= h($it['item_name'] . ' (' . $it['stock_qty'] . ' ' . $it['unit'] . ' available)') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" min="1" required>
                    </div>
                    <div class="field">
                        <label>Receiving representative *</label>
                        <input name="received_by" maxlength="100" required>
                    </div>
                    <div class="field full">
                        <label>Related incident (optional)</label>
                        <select name="incident_id">
                            <option value="">None / General relief</option>
                            <?php foreach ($incidentsList as $inc): ?>
                                <option value="<?= (int) $inc['incident_id'] ?>">
                                    <?= h(formatRef('INC', (int) $inc['incident_id']) . ' — ' . ucfirst($inc['incident_type']) . ' (' . $inc['location'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button class="button" type="submit">Record distribution</button>
            </form>
        <?php else: ?>
            <p class="muted">No distribution release permission granted.</p>
        <?php endif; ?>

        <h2 style="margin-top: 22px;">Distribution History</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Date</th>
                        <th>Household</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Received by</th>
                        <th>Incident</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $d): ?>
                        <tr>
                            <td><strong><?= h(formatRef('DIST', (int) $d['distribution_id'])) ?></strong></td>
                            <td><?= h($d['distribution_date']) ?></td>
                            <td><?= h($d['household_head']) ?></td>
                            <td><?= h($d['item_name']) ?></td>
                            <td><?= (int) $d['quantity'] ?> <?= h($d['unit']) ?></td>
                            <td><?= h($d['received_by']) ?></td>
                            <td><?= !empty($d['incident_id']) ? h(formatRef('INC', (int) $d['incident_id'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>




<?php elseif ($module === 'evacuees'): ?>
    <?php
    $params = [];
    $whereSql = '';
    if ($search !== '') {
        $whereSql = 'WHERE e.evacuee_name LIKE :s1 OR c.center_name LIKE :s2';
        $params['s1'] = '%' . $search . '%';
        $params['s2'] = '%' . $search . '%';
    }

    $stmt = $pdo->prepare(
        "SELECT e.*, c.center_name, h.household_head
         FROM evacuees e
         JOIN evacuation_centers c ON c.center_id = e.center_id
         LEFT JOIN households h ON h.household_id = e.household_id
         {$whereSql}
         ORDER BY e.time_in DESC"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    ?>

    <section class="panel">
        <h2>Evacuation Log & Check-Out</h2>
        <form method="get" action="workspace.php" style="margin-bottom: 14px;">
            <input type="hidden" name="module" value="evacuees">
            <div class="form-grid">
                <div class="field">
                    <label for="q">Search evacuee or center</label>
                    <input id="q" name="q" value="<?= h($search) ?>" placeholder="Search evacuee name or center...">
                </div>
            </div>
            <button class="button" type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="button button-light" href="workspace.php?module=evacuees">Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Evacuee Name</th>
                        <th>Center</th>
                        <th>Linked Household</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= h($r['evacuee_name']) ?></td>
                            <td><?= h($r['center_name']) ?></td>
                            <td><?= h($r['household_head'] ?? '—') ?></td>
                            <td><?= h($r['time_in']) ?></td>
                            <td><?= h($r['time_out'] ?? 'Currently staying') ?></td>
                            <td>
                                <?php if (!$r['time_out'] && hasPermission('evacuation.evacuate')): ?>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="checkout">
                                        <input type="hidden" name="id" value="<?= (int) $r['evacuee_id'] ?>">
                                        <button class="button button-light" type="submit" style="padding: 6px 10px;">Check out</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>




<?php if (isset($config['fields']) && $module !== 'evacuees'): ?>
    <?php
    $params = [];
    if ($module === 'centers') {
        $whereSql = '';
        if ($search !== '') {
            $whereSql = 'WHERE c.center_name LIKE :s1 OR c.address LIKE :s2';
            $params['s1'] = '%' . $search . '%';
            $params['s2'] = '%' . $search . '%';
        }
        $stmt = $pdo->prepare(
            "SELECT c.*,
                (SELECT COUNT(*) FROM evacuees e WHERE e.center_id = c.center_id AND e.time_out IS NULL) AS current_occupants
             FROM evacuation_centers c
             {$whereSql}
             ORDER BY c.center_name"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } else {
        $searchCols = [
            'households' => ['household_head', 'address', 'vulnerability_class'],
            'items' => ['item_name', 'category'],
            'announcements' => ['title', 'message', 'category'],
        ];
        $whereSql = '';
        if ($search !== '' && isset($searchCols[$module])) {
            $likes = [];
            foreach ($searchCols[$module] as $idx => $col) {
                $key = 's' . $idx;
                $likes[] = "`{$col}` LIKE :{$key}";
                $params[$key] = '%' . $search . '%';
            }
            $whereSql = 'WHERE ' . implode(' OR ', $likes);
        }
        $stmt = $pdo->prepare(
            'SELECT * FROM `' . $config['table'] . '` ' . $whereSql . ' ORDER BY `' . $config['id'] . '` DESC'
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    }
    ?>

    <section class="panel">
        <h2>Saved Records</h2>

        <form method="get" style="margin-bottom: 14px;">
            <div class="form-grid">
                <div class="field">
                    <label for="q">Live search records</label>
                    <input id="q" name="q" value="<?= h($search) ?>" placeholder="Type keyword to search...">
                </div>
            </div>
            <button class="button" type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="button button-light" href="workspace.php?module=<?= h($module) ?>">Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach (array_keys($config['fields']) as $f): ?>
                            <th><?= h($config['fields'][$f][0]) ?></th>
                        <?php endforeach; ?>
                        <?php if ($module === 'centers'): ?>
                            <th>Current Occupancy</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <?php foreach (array_keys($config['fields']) as $f): ?>
                                <td><?= h((string) ($r[$f] ?? '')) ?></td>
                            <?php endforeach; ?>
                            <?php if ($module === 'centers'): ?>
                                <td><strong><?= (int) $r['current_occupants'] ?> / <?= (int) $r['capacity'] ?></strong></td>
                            <?php endif; ?>
                            <td>
                                <?php if (hasPermission($config['permission'] . '.edit')): ?>
                                    <a href="workspace.php?module=<?= h($module) ?>&edit=<?= (int) $r[$config['id']] ?>">Edit</a>
                                <?php endif; ?>
                                <?php if ($module === 'announcements' && hasPermission('announcements.edit')): ?>
                                    <form method="post" class="inline-form" style="margin-left: 8px;">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $r[$config['id']] ?>">
                                        <button class="button button-light" type="submit" name="active" value="<?= (int) $r['is_active'] ? 0 : 1 ?>" style="padding: 4px 8px;">
                                            <?= (int) $r['is_active'] ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>




<?php if ($module === 'reports'): ?>
    <?php
    $dateFrom = trim((string) ($_GET['from'] ?? ''));
    $dateTo = trim((string) ($_GET['to'] ?? ''));

    $incConds = [];
    $incParams = [];
    if ($dateFrom !== '') {
        $incConds[] = 'DATE(reported_at) >= :inc_from';
        $incParams['inc_from'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $incConds[] = 'DATE(reported_at) <= :inc_to';
        $incParams['inc_to'] = $dateTo;
    }
    $incWhere = !empty($incConds) ? 'WHERE ' . implode(' AND ', $incConds) : '';

    $distConds = [];
    $distParams = [];
    if ($dateFrom !== '') {
        $distConds[] = 'DATE(d.distribution_date) >= :dist_from';
        $distParams['dist_from'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $distConds[] = 'DATE(d.distribution_date) <= :dist_to';
        $distParams['dist_to'] = $dateTo;
    }
    $distWhere = !empty($distConds) ? 'WHERE ' . implode(' AND ', $distConds) : '';

    $countsStmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM incidents {$incWhere} GROUP BY status");
    $countsStmt->execute($incParams);
    $counts = $countsStmt->fetchAll();

    $totalIncidents = 0;
    $resolvedIncidents = 0;
    foreach ($counts as $cRow) {
        $totalIncidents += (int) $cRow['total'];
        if ($cRow['status'] === 'resolved') {
            $resolvedIncidents = (int) $cRow['total'];
        }
    }

    $affectedStmt = $pdo->prepare(
        "SELECT i.incident_id, i.incident_type, i.status, COUNT(ih.household_id) AS affected_households
         FROM incidents i
         LEFT JOIN incident_households ih ON ih.incident_id = i.incident_id
         {$incWhere}
         GROUP BY i.incident_id, i.incident_type, i.status
         ORDER BY i.incident_id DESC"
    );
    $affectedStmt->execute($incParams);
    $affected = $affectedStmt->fetchAll();

    $occupancy = $pdo->query(
        'SELECT c.center_name, c.status, c.capacity, COUNT(e.evacuee_id) AS occupants
         FROM evacuation_centers c
         LEFT JOIN evacuees e ON e.center_id = c.center_id AND e.time_out IS NULL
         GROUP BY c.center_id, c.center_name, c.status, c.capacity'
    )->fetchAll();

    $totalEvacuees = 0;
    foreach ($occupancy as $oRow) {
        $totalEvacuees += (int) $oRow['occupants'];
    }

    $stock = $pdo->query('SELECT item_name, unit, stock_qty FROM relief_items ORDER BY item_name')->fetchAll();
    $lowStockCount = 0;
    foreach ($stock as $sRow) {
        if ((int) $sRow['stock_qty'] <= 10) {
            $lowStockCount++;
        }
    }

    $distStmt = $pdo->prepare(
        "SELECT d.distribution_id, h.household_head, i.item_name, d.quantity, i.unit, d.received_by, d.distribution_date
         FROM relief_distributions d
         JOIN households h ON h.household_id = d.household_id
         JOIN relief_items i ON i.item_id = d.item_id
         {$distWhere}
         ORDER BY d.distribution_date DESC"
    );
    $distStmt->execute($distParams);
    $dist = $distStmt->fetchAll();
    ?>

    <section class="panel">
        <h2>Filter Reports by Date Range</h2>
        <form method="get" action="reports.php">
            <div class="form-grid">
                <div class="field">
                    <label for="from">From date</label>
                    <input id="from" name="from" type="date" value="<?= h($dateFrom) ?>">
                </div>
                <div class="field">
                    <label for="to">To date</label>
                    <input id="to" name="to" type="date" value="<?= h($dateTo) ?>">
                </div>
            </div>
            <button class="button" type="submit">Apply date filter</button>
            <?php if ($dateFrom !== '' || $dateTo !== ''): ?>
                <a class="button button-light" href="reports.php">Reset</a>
            <?php endif; ?>
        </form>
    </section>

    <div class="module-grid" style="margin-bottom: 18px;">
        <div class="module-card">
            <h3>Total Incidents Recorded</h3>
            <p style="font-size: 20px; font-weight: bold; color: #17314e;"><?= $totalIncidents ?> (<?= $resolvedIncidents ?> Resolved)</p>
        </div>
        <div class="module-card">
            <h3>Active Evacuees & Low Stock</h3>
            <p style="font-size: 20px; font-weight: bold; color: #17314e;"><?= $totalEvacuees ?> Evacuees · <?= $lowStockCount ?> Low-Stock Items</p>
        </div>
    </div>

    <section class="panel">
        <h2>1. Incident Status Summary</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($counts as $r): ?>
                        <tr>
                            <td><?= h(ucwords(str_replace('_', ' ', $r['status']))) ?></td>
                            <td><?= (int) $r['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top: 20px;">2. Affected Households by Incident</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Type</th><th>Status</th><th>Affected Households</th></tr></thead>
                <tbody>
                    <?php foreach ($affected as $r): ?>
                        <tr>
                            <td><?= h(formatRef('INC', (int) $r['incident_id'])) ?></td>
                            <td><?= h(ucwords(str_replace('_', ' ', $r['incident_type']))) ?></td>
                            <td><?= h(ucwords(str_replace('_', ' ', $r['status']))) ?></td>
                            <td><?= (int) $r['affected_households'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top: 20px;">3. Evacuation Center Occupancy</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Center</th><th>Status</th><th>Capacity</th><th>Current Occupants</th><th>Available Slots</th></tr></thead>
                <tbody>
                    <?php foreach ($occupancy as $r): ?>
                        <tr>
                            <td><?= h($r['center_name']) ?></td>
                            <td><?= h(ucfirst($r['status'])) ?></td>
                            <td><?= (int) $r['capacity'] ?></td>
                            <td><?= (int) $r['occupants'] ?></td>
                            <td><?= max(0, (int) $r['capacity'] - (int) $r['occupants']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top: 20px;">4. Relief Inventory Balances</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Balance</th><th>Alert Status</th></tr></thead>
                <tbody>
                    <?php foreach ($stock as $r): ?>
                        <?php $q = (int) $r['stock_qty']; ?>
                        <tr>
                            <td><?= h($r['item_name']) ?></td>
                            <td><?= $q ?> <?= h($r['unit']) ?></td>
                            <td><?= $q === 0 ? 'OUT OF STOCK' : ($q <= 10 ? 'LOW STOCK' : 'OK') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="margin-top: 20px;">5. Relief Distribution History</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref #</th><th>Household</th><th>Item</th><th>Quantity</th><th>Received By</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($dist as $r): ?>
                        <tr>
                            <td><?= h(formatRef('DIST', (int) $r['distribution_id'])) ?></td>
                            <td><?= h($r['household_head']) ?></td>
                            <td><?= h($r['item_name']) ?></td>
                            <td><?= (int) $r['quantity'] ?> <?= h($r['unit']) ?></td>
                            <td><?= h($r['received_by']) ?></td>
                            <td><?= h($r['distribution_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="muted" style="margin-top: 14px;">To print or export this report as PDF, press <strong>Ctrl + P</strong>.</p>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/staff-layout-end.php'; ?>