
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($activeTab === 'rapports'): ?>
            openRapportModal('<?php echo strpos($action, 'update') !== false ? 'edit' : 'add'; ?>', <?php echo json_encode($_POST); ?>);
        <?php else: ?>
            openDonneeModal('<?php echo strpos($action, 'update') !== false ? 'edit' : 'add'; ?>', <?php echo json_encode($_POST); ?>);
        <?php endif; ?>
    });
