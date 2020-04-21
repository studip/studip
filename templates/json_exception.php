<? if (Studip\ENV === 'development'): ?>
<?= json_encode([
    'status'  => (int) $status,
    'message' => $exception->getMessage(),
    'file'    => $exception->getFile(),
    'line'    => $exception->getLine(),
    'trace'   => explode("\n", $exception->getTraceAsString()),
]) ?>
<? else: ?>
<?= json_encode([
    'status'  => (int) $status,
    'message' => $exception->getMessage(),
    ]) ?>
<? endif; ?>
