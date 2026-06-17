import sys
import re

file_path = 'c:/xampp/htdocs/digambar-samaj/registration.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add the helper function and group logic at the top, after fetching custom fields
fetch_block = """// Fetch custom dynamic fields to display and process
$stmtCustom = $pdo->query("SELECT * FROM registration_fields WHERE is_custom = 1 AND is_visible = 1 ORDER BY sort_order ASC, id ASC");
$customFields = $stmtCustom->fetchAll();"""

new_fetch_block = """// Fetch custom dynamic fields to display and process
$stmtCustom = $pdo->query("SELECT * FROM registration_fields WHERE is_custom = 1 AND is_visible = 1 ORDER BY sort_order ASC, id ASC");
$customFields = $stmtCustom->fetchAll();

$customFieldsByGroup = [];
foreach ($customFields as $field) {
    $group = $field['field_group'] ?: 'Additional Information';
    if ($group === 'Custom Fields') $group = 'Additional Information';
    $customFieldsByGroup[$group][] = $field;
}

function renderCustomFieldHTML($field) {
    $req = $field['is_required'] ? '*' : '';
    $reqAttr = $field['is_required'] ? 'required' : '';
    $label = htmlspecialchars($field['field_label']);
    $key = htmlspecialchars($field['field_key']);
    $type = htmlspecialchars($field['field_type']);
    
    $html = '<div><label class="block text-gray-700 font-medium mb-2">' . $label . ' ' . $req . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea name="' . $key . '" ' . $reqAttr . ' rows="2" class="w-full border rounded-lg px-4 py-2"></textarea>';
    } elseif ($type === 'dropdown') {
        $html .= '<select name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2"><option value="">Select ' . $label . '</option>';
        $options = explode(',', $field['field_options']);
        foreach ($options as $opt) {
            $opt = trim($opt);
            if ($opt) $html .= '<option value="' . htmlspecialchars($opt) . '">' . htmlspecialchars($opt) . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'file') {
        $html .= '<input type="file" name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2">';
    } else {
        $html .= '<input type="' . $type . '" name="' . $key . '" ' . $reqAttr . ' class="w-full border rounded-lg px-4 py-2">';
    }
    $html .= '</div>';
    return $html;
}
"""
content = content.replace(fetch_block, new_fetch_block)

# 2. Inject custom fields into Section 1
s1_end = "                        <div><label class=\"block text-gray-700 font-medium mb-2\">Mobile Number</label>"
s1_end_chunk = """                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                            <input type="tel" name="mobile" value="<?= htmlspecialchars($current_user['mobile']) ?>" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                    </div>
                </div>"""
s1_new_chunk = """                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Mobile Number</label>
                            <input type="tel" name="mobile" value="<?= htmlspecialchars($current_user['mobile']) ?>" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Section 1: Basic Information'])) {
                            foreach ($customFieldsByGroup['Section 1: Basic Information'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>"""
content = content.replace(s1_end_chunk, s1_new_chunk)


# 3. Inject into Section 2
s2_end_chunk = """                        <div><label class="block text-gray-700 font-medium mb-2">Company/Firm Name (Optional)</label><input type="text" name="company_name" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Designation (Optional)</label><input type="text" name="designation" class="w-full border rounded-lg px-4 py-2"></div>
                    </div>
                </div>"""
s2_new_chunk = """                        <div><label class="block text-gray-700 font-medium mb-2">Company/Firm Name (Optional)</label><input type="text" name="company_name" class="w-full border rounded-lg px-4 py-2"></div>
                        <div><label class="block text-gray-700 font-medium mb-2">Designation (Optional)</label><input type="text" name="designation" class="w-full border rounded-lg px-4 py-2"></div>
                        <?php 
                        if (!empty($customFieldsByGroup['Section 2: Personal Details'])) {
                            foreach ($customFieldsByGroup['Section 2: Personal Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>"""
content = content.replace(s2_end_chunk, s2_new_chunk)


# 4. Inject into Family Details
s3_end_chunk = """                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Unmarried Count (Optional)</label>
                            <select name="sisters_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                    </div>
                </div>"""
s3_new_chunk = """                        <div><label class="block text-gray-700 font-medium mb-2">Sisters Unmarried Count (Optional)</label>
                            <select name="sisters_unmarried" class="w-full border rounded-lg px-4 py-2"><option>0</option><?php for($i=1;$i<=5;$i++) echo "<option>$i</option>"; ?></select>
                        </div>
                        <?php 
                        if (!empty($customFieldsByGroup['Family Details'])) {
                            foreach ($customFieldsByGroup['Family Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>"""
content = content.replace(s3_end_chunk, s3_new_chunk)


# 5. Inject into Section 4 Mandir Details
s4_end_chunk = """                    <!-- Reference Persons (Hidden initially, dynamic slide down) -->"""
s4_new_chunk = """                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <?php 
                        if (!empty($customFieldsByGroup['Section 4: Mandir Verification Details'])) {
                            foreach ($customFieldsByGroup['Section 4: Mandir Verification Details'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                    <!-- Reference Persons (Hidden initially, dynamic slide down) -->"""
content = content.replace(s4_end_chunk, s4_new_chunk)

# 6. Inject into Photos
s5_end_chunk = """                        <?php if (isset($coreFieldsSettings['profile_photo_drive_url']) && $coreFieldsSettings['profile_photo_drive_url']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Profile Photo Drive URL <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? '*' : '' ?></label>
                            <input type="url" name="profile_photo_drive_url" <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>"""
s5_new_chunk = """                        <?php if (isset($coreFieldsSettings['profile_photo_drive_url']) && $coreFieldsSettings['profile_photo_drive_url']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Profile Photo Drive URL <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? '*' : '' ?></label>
                            <input type="url" name="profile_photo_drive_url" <?= $coreFieldsSettings['profile_photo_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>
                        <?php 
                        if (!empty($customFieldsByGroup['Photos'])) {
                            foreach ($customFieldsByGroup['Photos'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>"""
content = content.replace(s5_end_chunk, s5_new_chunk)

# 7. Inject into Documents & Payment
s6_end_chunk = """                        <?php if (isset($coreFieldsSettings['payment_proof_drive_url']) && $coreFieldsSettings['payment_proof_drive_url']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Payment Proof Drive URL <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? '*' : '' ?></label>
                            <input type="url" name="payment_proof_drive_url" <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>"""
s6_new_chunk = """                        <?php if (isset($coreFieldsSettings['payment_proof_drive_url']) && $coreFieldsSettings['payment_proof_drive_url']['is_visible']): ?>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Payment Proof Drive URL <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? '*' : '' ?></label>
                            <input type="url" name="payment_proof_drive_url" <?= $coreFieldsSettings['payment_proof_drive_url']['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <?php endif; ?>
                        <?php 
                        if (!empty($customFieldsByGroup['Documents & Payment'])) {
                            foreach ($customFieldsByGroup['Documents & Payment'] as $f) echo renderCustomFieldHTML($f);
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>"""
content = content.replace(s6_end_chunk, s6_new_chunk)

# 8. Replace Additional Information
s7_old = """                <!-- Custom Fields Section -->
                <?php if (count($customFields) > 0): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($customFields as $field): ?>
                            <div>
                                <label class="block text-gray-700 font-medium mb-2"><?= htmlspecialchars($field['field_label']) ?> <?= $field['is_required'] ? '*' : '' ?></label>
                                <?php if ($field['field_type'] === 'textarea'): ?>
                                    <textarea name="<?= htmlspecialchars($field['field_key']) ?>" <?= $field['is_required'] ? 'required' : '' ?> rows="2" class="w-full border rounded-lg px-4 py-2"></textarea>
                                <?php elseif ($field['field_type'] === 'dropdown'): ?>
                                    <select name="<?= htmlspecialchars($field['field_key']) ?>" <?= $field['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                                        <option value="">Select <?= htmlspecialchars($field['field_label']) ?></option>
                                        <?php 
                                        $options = explode(',', $field['field_options']);
                                        foreach ($options as $opt): 
                                            $opt = trim($opt);
                                        ?>
                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($field['field_type'] === 'file'): ?>
                                    <input type="file" name="<?= htmlspecialchars($field['field_key']) ?>" <?= $field['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                                <?php else: ?>
                                    <input type="<?= htmlspecialchars($field['field_type']) ?>" name="<?= htmlspecialchars($field['field_key']) ?>" <?= $field['is_required'] ? 'required' : '' ?> class="w-full border rounded-lg px-4 py-2">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>"""

s7_new = """                <!-- Additional Information Section -->
                <?php if (!empty($customFieldsByGroup['Additional Information'])): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-primary mb-4">Additional Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($customFieldsByGroup['Additional Information'] as $f) echo renderCustomFieldHTML($f); ?>
                    </div>
                </div>
                <?php endif; ?>"""

content = content.replace(s7_old, s7_new)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Done")
