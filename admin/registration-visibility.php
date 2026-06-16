<?php
require_once '../includes/db.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$current_page = 'registration-visibility.php';

// Handle Add Custom Field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_custom_field') {
    $label = trim($_POST['field_label']);
    $type = trim($_POST['field_type']);
    $options = trim($_POST['field_options'] ?? '');
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    
    if (!empty($label)) {
        // Create a field key from the label
        $key = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $label));
        $key = 'custom_' . trim($key, '_');
        
        $stmt = $pdo->prepare("INSERT INTO registration_fields (field_group, field_key, field_label, field_type, field_options, is_custom, is_visible, is_required) VALUES ('Custom Fields', ?, ?, ?, ?, 1, 1, ?)");
        try {
            $stmt->execute([$key, $label, $type, $options, $is_required]);
        } catch (PDOException $e) {
            // Key might already exist
        }
    }
    header("Location: registration-visibility.php");
    exit;
}

// Handle Delete Custom Field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_custom_field') {
    $field_id = (int)$_POST['field_id'];
    $stmt = $pdo->prepare("DELETE FROM registration_fields WHERE id = ? AND is_custom = 1");
    $stmt->execute([$field_id]);
    header("Location: registration-visibility.php");
    exit;
}

// Handle Save Visibility Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_visibility') {
    // Reset all to hidden and not required first (excluding core fields)
    $pdo->query("UPDATE registration_fields SET is_visible = 0, is_required = 0 WHERE is_core = 0");
    
    if (!empty($_POST['visible_fields']) && is_array($_POST['visible_fields'])) {
        foreach ($_POST['visible_fields'] as $id) {
            $stmt = $pdo->prepare("UPDATE registration_fields SET is_visible = 1 WHERE id = ?");
            $stmt->execute([(int)$id]);
        }
    }
    
    if (!empty($_POST['required_fields']) && is_array($_POST['required_fields'])) {
        foreach ($_POST['required_fields'] as $id) {
            $stmt = $pdo->prepare("UPDATE registration_fields SET is_required = 1 WHERE id = ?");
            $stmt->execute([(int)$id]);
        }
    }
    header("Location: registration-visibility.php?saved=1");
    exit;
}

// Fetch fields
$stmt = $pdo->query("SELECT * FROM registration_fields ORDER BY is_custom ASC, sort_order ASC, id ASC");
$fields = $stmt->fetchAll();

// Group fields
$grouped_fields = [];
$custom_fields = [];
foreach ($fields as $f) {
    if ($f['is_custom']) {
        $custom_fields[] = $f;
    } else {
        $grouped_fields[$f['field_group']][] = $f;
    }
}

include 'includes/header.php'; 
include 'includes/sidebar.php'; 
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Registration Form Setup</h3>
        <p class="text-gray-500 text-sm">Manage which fields are shown on the public signup page.</p>
    </div>
    <button type="submit" form="visibilityForm" class="bg-primary text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-opacity-90 transition shadow-sm flex items-center">
        <i class="fas fa-save mr-2"></i> Save Changes
    </button>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
    <p class="text-sm text-green-700 font-medium">Settings saved successfully.</p>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- Standard Fields List -->
    <div class="lg:col-span-2 space-y-6">
        <form id="visibilityForm" method="POST" action="registration-visibility.php">
            <input type="hidden" name="action" value="save_visibility">
            
            <?php foreach ($grouped_fields as $group_name => $group_fields): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h4 class="font-bold text-gray-800"><i class="fas <?= $group_name === 'Basic Details' ? 'fa-user' : 'fa-list' ?> text-gray-400 mr-2"></i> <?= htmlspecialchars($group_name) ?></h4>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-gray-100">
                        <?php foreach ($group_fields as $field): ?>
                        <li class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 px-6 hover:bg-gray-50 transition">
                            <div class="mb-2 sm:mb-0">
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($field['field_label']) ?></p>
                                <p class="text-xs text-gray-500">Type: <?= htmlspecialchars($field['field_type']) ?></p>
                            </div>
                            <div class="flex items-center space-x-6">
                                <?php if ($field['is_core']): ?>
                                    <div class="flex flex-col items-end">
                                        <label class="relative inline-flex items-center cursor-pointer mb-1">
                                            <input type="checkbox" class="sr-only peer" checked disabled>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary opacity-50 cursor-not-allowed"></div>
                                        </label>
                                        <span class="text-xs text-gray-400 font-medium">Core (Required)</span>
                                    </div>
                                <?php else: ?>
                                    <div class="flex flex-col items-center">
                                        <label class="text-xs text-gray-500 mb-1">Visible</label>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="visible_fields[]" value="<?= $field['id'] ?>" class="sr-only peer" <?= $field['is_visible'] ? 'checked' : '' ?>>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        </label>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <label class="text-xs text-gray-500 mb-1">Required</label>
                                        <input type="checkbox" name="required_fields[]" value="<?= $field['id'] ?>" class="rounded border-gray-300 text-primary focus:ring-primary w-5 h-5" <?= $field['is_required'] ? 'checked' : '' ?>>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- Custom Fields Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-primary/5 px-6 py-4 border-b border-primary/10 flex justify-between items-center">
                <h4 class="font-bold text-primary"><i class="fas fa-plus-circle mr-2"></i> Add Custom Field</h4>
            </div>
            <div class="p-6">
                <form class="space-y-4" method="POST" action="registration-visibility.php">
                    <input type="hidden" name="action" value="add_custom_field">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Field Label *</label>
                        <input type="text" name="field_label" required placeholder="e.g. Dietary Preference" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Field Type *</label>
                        <select name="field_type" id="fieldTypeSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none bg-white text-sm">
                            <option value="text">Short Text</option>
                            <option value="textarea">Long Text</option>
                            <option value="number">Number</option>
                            <option value="dropdown">Dropdown Options</option>
                            <option value="radio">Radio Buttons</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="date">Date Picker</option>
                            <option value="time">Time Picker</option>
                            <option value="file">File Upload</option>
                            <option value="url">URL/Link</option>
                        </select>
                    </div>
                    <div id="optionsGroup" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Options (Comma separated)</label>
                        <input type="text" name="field_options" placeholder="Vegetarian, Vegan, Jain" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_required" id="is_required" value="1" class="rounded border-gray-300 text-primary focus:ring-primary w-4 h-4">
                        <label for="is_required" class="ml-2 text-sm text-gray-700">Make this field Required</label>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg font-bold shadow-md hover:bg-opacity-90 transition mt-2">
                        Create Field
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h4 class="font-bold text-gray-800">Existing Custom Fields</h4>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($custom_fields as $cf): ?>
                    <li class="flex items-center justify-between p-4 px-6">
                        <div>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($cf['field_label']) ?></span>
                            <span class="block text-xs text-gray-400 mt-1"><?= ucfirst($cf['field_type']) ?> <?= $cf['is_required'] ? '(Required)' : '' ?></span>
                        </div>
                        <form method="POST" action="registration-visibility.php" class="m-0" onsubmit="return confirm('Are you sure you want to delete this custom field?');">
                            <input type="hidden" name="action" value="delete_custom_field">
                            <input type="hidden" name="field_id" value="<?= $cf['id'] ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($custom_fields)): ?>
                    <li class="p-4 px-6 text-gray-500 text-center italic">No custom fields created yet.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('fieldTypeSelect').addEventListener('change', function(e) {
        const val = e.target.value;
        const optsGroup = document.getElementById('optionsGroup');
        if(val === 'dropdown' || val === 'radio') {
            optsGroup.classList.remove('hidden');
        } else {
            optsGroup.classList.add('hidden');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
