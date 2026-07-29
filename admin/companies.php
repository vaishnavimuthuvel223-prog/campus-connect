<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: login.php'); exit; }
require_once '../config/db.php';

$success = '';
$error = '';

// Fix: Ensure companies table has a working AUTO_INCREMENT PRIMARY KEY on company_id.
// This repairs the classic "Duplicate entry '0' for key PRIMARY" bug, which happens
// when company_id lost its AUTO_INCREMENT property (so new rows try to insert as 0).
try {
    // 1) If there are leftover rows sharing the same company_id (e.g. several rows
    //    stuck at 0), keep only one of each so a PRIMARY KEY can be applied safely.
    $conn->exec("
        DELETE c1 FROM companies c1
        INNER JOIN companies c2
        ON c1.company_id = c2.company_id AND c1.company_name < c2.company_name
    ");

    // 2) Make sure company_id is a proper AUTO_INCREMENT PRIMARY KEY, continuing
    //    from the current highest id (never restarting below existing data).
    $maxId = (int)$conn->query("SELECT COALESCE(MAX(company_id), 0) FROM companies")->fetchColumn();
    $nextId = $maxId + 1;
    $conn->exec("ALTER TABLE companies MODIFY COLUMN company_id INT(11) NOT NULL AUTO_INCREMENT");
    $conn->exec("ALTER TABLE companies AUTO_INCREMENT = $nextId");
} catch (Exception $e) {
    // If ALTER fails because PRIMARY KEY isn't set at all yet, add it then retry once.
    try {
        $conn->exec("ALTER TABLE companies ADD PRIMARY KEY (company_id)");
        $conn->exec("ALTER TABLE companies MODIFY COLUMN company_id INT(11) NOT NULL AUTO_INCREMENT");
    } catch (Exception $e2) {
        // Already set correctly, or a non-critical race — safe to continue.
    }
}

// Add skill_category column to companies table if missing
try {
    $conn->query("SELECT skill_category FROM companies LIMIT 1");
} catch (Exception $e) {
    try {
        $conn->exec("ALTER TABLE companies ADD COLUMN skill_category VARCHAR(32) DEFAULT NULL AFTER description");
    } catch (Exception $e2) {
        // Column might already exist
    }
}

// Handle add company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['company_name']);
    $package = trim($_POST['package']);
    $description = trim($_POST['description']);
    $skill_category = $_POST['skill_category'] ?? '';

    $allowed_skills = ['all','elite category','general','service now','salesforce','SAP','Cybersecurity'];
    if (!in_array($skill_category, $allowed_skills)) {
        $error = 'Invalid skill category.';
    } elseif (empty($name) || empty($package) || empty($skill_category)) {
        $error = 'Company name, package, and skill category are required.';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO companies (company_name, package, description, skill_category) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $package, $description, $skill_category]);
            $new_id = $conn->lastInsertId();
            $success = 'Company added successfully! (ID: ' . $new_id . ')';
        } catch (Exception $e) {
            $error = 'Failed to add company: ' . $e->getMessage();
        }
    }
}

// Handle edit company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_company'])) {
    $cid = (int)$_POST['company_id'];
    $name = trim($_POST['company_name']);
    $package = trim($_POST['package']);
    $description = trim($_POST['description']);
    $skill_category = $_POST['skill_category'] ?? '';

    $allowed_skills = ['all','elite category','general','service now','salesforce','SAP','Cybersecurity'];
    if (!in_array($skill_category, $allowed_skills)) {
        $error = 'Invalid skill category.';
    } elseif (empty($name) || empty($package) || empty($skill_category)) {
        $error = 'Company name, package, and skill category are required.';
    } else {
        $stmt = $conn->prepare("UPDATE companies SET company_name = ?, package = ?, description = ?, skill_category = ? WHERE company_id = ?");
        $stmt->execute([$name, $package, $description, $skill_category, $cid]);
        $success = 'Company updated successfully!';
    }
}

// Handle delete single company
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_company'])) {
    $cid = (int)$_POST['company_id'];
    try {
        $conn->beginTransaction();
        $driveIds = $conn->prepare("SELECT drive_id FROM drives WHERE company_id = ?");
        $driveIds->execute([$cid]);
        $driveIdList = $driveIds->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($driveIdList)) {
            $placeholders = implode(',', array_fill(0, count($driveIdList), '?'));
            $conn->prepare("DELETE FROM applications WHERE drive_id IN ($placeholders)")->execute($driveIdList);
            $conn->prepare("DELETE FROM drive_departments WHERE drive_id IN ($placeholders)")->execute($driveIdList);
        }
        $conn->prepare("DELETE FROM drives WHERE company_id = ?")->execute([$cid]);
        $conn->prepare("DELETE FROM company_reviews WHERE company_id = ?")->execute([$cid]);
        $conn->prepare("DELETE FROM companies WHERE company_id = ?")->execute([$cid]);

        $conn->commit();
        $success = 'Company deleted successfully.';
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Failed to delete company: ' . $e->getMessage();
    }
}

// Handle delete ALL companies (full reset, so ids start fresh from 1 again)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_companies'])) {
    try {
        $conn->beginTransaction();
        $conn->exec("DELETE FROM applications");
        $conn->exec("DELETE FROM drive_departments");
        $conn->exec("DELETE FROM drives");
        $conn->exec("DELETE FROM company_reviews");
        $conn->exec("DELETE FROM companies");
        $conn->exec("ALTER TABLE companies AUTO_INCREMENT = 1");
        $conn->commit();
        $success = 'All companies (and their drives/applications) have been removed. You can start adding fresh companies now.';
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Failed to clear companies: ' . $e->getMessage();
    }
}

$companies = $conn->query("
    SELECT c.*, (SELECT COUNT(*) FROM drives WHERE company_id = c.company_id) AS drive_count
    FROM companies c ORDER BY c.company_name
")->fetchAll();

$pageTitle = 'Manage Companies';
$showNav = true;
$currentPage = 'companies';
include '../includes/header.php';
?>

<style>
    body {
        background: url('/campuss/uploads/clg.jpg') no-repeat center center fixed;
        background-size: cover;
        min-height: 100vh;
        margin: 0;
        padding: 0;
    }
    .page-wrapper {
        background: rgba(255,255,255,0.80);
        min-height: 100vh;
        padding: 2rem 1rem;
        backdrop-filter: blur(16px);
        box-shadow: 0 8px 32px rgba(99,102,241,0.10);
        border-radius: 24px 24px 0 0;
        margin: 0 auto;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .alert {
        animation: slideUp 0.6s ease-out;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: none;
    }
    .alert-success {
        background: rgba(34, 197, 94, 0.10);
        border-left: 4px solid #22c55e;
        color: #166534;
    }
    .alert-danger {
        background: rgba(255, 193, 7, 0.10);
        border-left: 4px solid #f59e42;
        color: #b45309;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 2rem;
        animation: slideUp 0.6s ease-out 0.1s both;
    }
    .page-header h2 {
        color: #3b3b4f;
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.01em;
        text-shadow: 0 2px 8px rgba(99,102,241,0.08);
    }
    .glass-card {
        background: rgba(255,59,59,0.13);
        border: 2px solid #dc2626;
        border-radius: 20px;
        padding: 2.2rem 2rem;
        margin-bottom: 1.7rem;
        animation: slideUp 0.6s ease-out;
        transition: all 0.3s ease;
        box-shadow: 0 10px 36px rgba(220,38,38,0.13), 0 2px 8px rgba(0,0,0,0.04);
        backdrop-filter: blur(14px);
    }
    .glass-card:hover {
        background: #f8fafc;
        border-color: #6366f1;
        box-shadow: 0 8px 32px rgba(99,102,241,0.13);
    }
    .glass-card-header {
        color: #dc2626;
        font-size: 1.45rem;
        font-weight: 1000;
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-shadow: 0 2px 12px rgba(220,38,38,0.10);
    }
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: flex-end;
        margin-bottom: 1.5rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .form-group label {
        color: #dc2626;
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        color: #1f2937;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.10);
    }
    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #a5b4fc;
    }
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .btn {
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn:hover {
        transform: translateY(-4px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
    }

    .btn-warning:hover {
        box-shadow: 0 6px 20px rgba(249, 115, 22, 0.5);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
    }

    .table-wrapper {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(8px);
        border: 1px solid #e0e7ef;
        border-radius: 12px;
        overflow: hidden;
        animation: slideUp 0.6s ease-out 0.2s both;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #22223b;
        background: transparent;
    }

    table thead {
        background: #f3f4f6;
        border-bottom: 2px solid #e0e7ef;
    }

    table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: #22223b;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    table tbody tr {
        border-bottom: 1px solid #e0e7ef;
        transition: all 0.3s ease;
    }

    table tbody tr:hover {
        background: #f8fafc;
    }

    table tbody td {
        padding: 1rem;
        font-size: 0.95rem;
        color: #22223b;
    }

    .badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(6, 182, 212, 0.2);
        color: #a5f3fc;
    }

    .action-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-box {
        background: linear-gradient(135deg, rgba(30, 30, 60, 0.95) 0%, rgba(20, 20, 50, 0.95) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 12px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .modal-box h3 {
        color: #f87171;
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        justify-content: space-between;
    }

    .modal-close {
        background: none;
        border: none;
        color: #f87171;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .modal-close:hover {
        transform: rotate(90deg);
        color: #fca5a5;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .action-btns {
            flex-direction: column;
        }

        .action-btns .btn {
            width: 100%;
            justify-content: center;
        }

        table {
            font-size: 0.75rem;
        }

        table thead th,
        table tbody td {
            padding: 0.75rem 0.5rem;
        }

        .modal-box {
            width: 95%;
            padding: 1.5rem;
        }
    }
</style>

<div class="main-content-wrapper">
<div class="page-wrapper">
    <div class="container">
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="page-header">
            <h2>Companies</h2>
        </div>

        <!-- Add Company Form -->
        <div class="glass-card">
            <div class="glass-card-header">Add New Company</div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" placeholder="Enter company name" required>
                    </div>
                    <div class="form-group">
                        <label>Package</label>
                        <input type="text" name="package" placeholder="e.g. 6 LPA" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief company description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Skill Category</label>
                    <select name="skill_category" class="form-control" required>
                        <option value="">-- Select Skill Category --</option>
                        <option value="all">🌐 All Categories (Open to Everyone)</option>
                        <option value="elite category">Elite Category</option>
                        <option value="general">General</option>
                        <option value="service now">Service Now</option>
                        <option value="salesforce">Salesforce</option>
                        <option value="SAP">SAP</option>
                        <option value="Cybersecurity">Cybersecurity</option>
                    </select>
                </div>
                <button type="submit" name="add_company" class="btn btn-primary">Add Company</button>
            </form>
        </div>

        <!-- Companies Table -->
        <div class="table-wrapper">
            <div style="padding:1rem; border-bottom:2px solid rgba(239,68,68,0.3); background:rgba(239,68,68,0.1); display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                <h3 style="color:#f87171; margin:0; font-size:1.125rem; text-transform:uppercase; letter-spacing:0.05em;">All Companies (<?= count($companies) ?>)</h3>
                <?php if (count($companies) > 0): ?>
                <form method="POST" onsubmit="return confirm('This will permanently delete ALL companies along with their drives and applications. Continue?');">
                    <button type="submit" name="delete_all_companies" class="btn btn-danger btn-sm">Remove All Companies</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Package</th>
                            <th>Skill Category</th>
                            <th>Drives</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($companies) === 0): ?>
                            <tr><td colspan="6" style="text-align:center; padding:2rem; color:#d0d5ff;">No companies found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($companies as $c): ?>
                            <tr>
                                <td><?= $c['company_id'] ?></td>
                                <td><strong><?= htmlspecialchars($c['company_name']) ?></strong></td>
                                <td><?= htmlspecialchars($c['package']) ?></td>
                                <td><?= htmlspecialchars($c['skill_category'] ?? '-') ?></td>
                                <td><span class="badge"><?= $c['drive_count'] ?></span></td>
                                <td class="action-btns">
                                    <button class="btn btn-warning btn-sm" onclick="editCompany(<?= htmlspecialchars(json_encode($c)) ?>)">Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($c['company_name'])) ?> and its drives/applications?');">
                                        <input type="hidden" name="company_id" value="<?= $c['company_id'] ?>">
                                        <button type="submit" name="delete_company" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Company Modal -->
<div class="modal-overlay" id="editCompanyModal">
    <div class="modal-box">
        <h3>Edit Company <button class="modal-close" onclick="closeModal('editCompanyModal')">&times;</button></h3>
        <form method="POST">
            <input type="hidden" name="company_id" id="edit_company_id">
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label>Company Name</label>
                <input type="text" name="company_name" id="edit_company_name" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label>Package</label>
                <input type="text" name="package" id="edit_company_package" required>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label>Description</label>
                <textarea name="description" id="edit_company_desc" rows="3"></textarea>
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label>Skill Category</label>
                <select name="skill_category" id="edit_company_skill_category" class="form-control" required>
                    <option value="">-- Select Skill Category --</option>
                    <option value="all">🌐 All Categories (Open to Everyone)</option>
                    <option value="elite category">Elite Category</option>
                    <option value="general">General</option>
                    <option value="service now">Service Now</option>
                    <option value="salesforce">Salesforce</option>
                    <option value="SAP">SAP</option>
                    <option value="Cybersecurity">Cybersecurity</option>
                </select>
            </div>
            <div style="display:flex; gap:1rem;">
                <button type="submit" name="edit_company" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-danger" onclick="closeModal('editCompanyModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCompany(c) {
    document.getElementById('edit_company_id').value = c.company_id;
    document.getElementById('edit_company_name').value = c.company_name;
    document.getElementById('edit_company_package').value = c.package;
    document.getElementById('edit_company_desc').value = c.description || '';
    document.getElementById('edit_company_skill_category').value = c.skill_category || '';
    document.getElementById('editCompanyModal').classList.add('active');
}
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.getElementById('editCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('editCompanyModal');
});
</script>

<?php include '../includes/footer.php'; ?>
