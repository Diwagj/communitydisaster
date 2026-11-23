<?php










require_once __DIR__ . '/auth.php';

requireRoles($allowedRoles);

$records = [];

try {
    $records = db()->query($query)->fetchAll();
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $databaseError = true;
}

require __DIR__ . '/staff-layout-start.php';
?>

<section class="panel">
    <?php if (!empty($databaseError)): ?>
        <div class="notice error">
            The records could not be loaded. Please check the database
            connection and table structure.
        </div>
    <?php elseif (empty($records)): ?>
        <div class="empty">No records are available yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($tableColumns as $column): ?>
                            <th><?= h($column['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <?php foreach ($tableColumns as $column): ?>
                                <td>
                                    <?= h((string) ($record[$column['field']] ?? '')) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/staff-layout-end.php'; ?>
