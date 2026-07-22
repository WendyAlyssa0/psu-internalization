<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program_id = (int)($_POST['program_id'] ?? 0);
    $requirement_name = trim($_POST['requirement_name'] ?? '');
    $is_required = isset($_POST['is_required']) ? 1 : 0;

    if ($program_id && $requirement_name !== '') {

        $stmt = $pdo->prepare("
            INSERT INTO program_requirements
            (program_id, requirement_name, is_required)
            VALUES (?, ?, ?)
        ");

        try {

            $stmt->execute([
                $program_id,
                $requirement_name,
                $is_required
            ]);

            echo "Saved successfully";

        } catch(PDOException $e) {

            die("Database Error: " . $e->getMessage());

        }

        header("Location: dashboard.php?page=program_requirements");
        exit;
    }
}

$programs = $pdo->query("
    SELECT id, program_name
    FROM programs
    ORDER BY program_name
")->fetchAll(PDO::FETCH_ASSOC);

$requirements = $pdo->query("
    SELECT
        pr.id,
        p.program_name,
        pr.requirement_name,
        pr.is_required,
        pr.created_at
    FROM program_requirements pr
    INNER JOIN programs p
        ON pr.program_id = p.id
    ORDER BY pr.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-card">

    <div class="page-header">
        <h2>Program Requirements</h2>
        <p>Assign requirements to specific programs.</p>
    </div>

    <div class="card">

        <form method="POST">

            <div class="form-group">
                <label>Program</label>

                <select name="program_id" required>
                    <option value="">Select Program</option>

                    <?php foreach($programs as $program): ?>
                        <option value="<?= $program['id'] ?>">
                            <?= htmlspecialchars($program['program_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Requirement Name</label>

                <input
                    type="text"
                    name="requirement_name"
                    required
                    placeholder="Example: Medical Certificate">
            </div>

            <div class="form-group">
                <label>
                    <input
                        type="checkbox"
                        name="is_required"
                        checked>
                    Required
                </label>
            </div>

            <button type="submit" class="btn-primary">
                Add Requirement
            </button>

        </form>

    </div>

    <div class="table-card">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Program</th>
                    <th>Requirement</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($requirements as $row): ?>

                <tr>

                    <td>#<?= $row['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($row['program_name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['requirement_name']) ?>
                    </td>

                    <td>
                        <?= $row['is_required']
                            ? '<span class="badge-success">Required</span>'
                            : '<span class="badge-secondary">Optional</span>' ?>
                    </td>

                    <td>
                        <?= date(
                            'M d, Y',
                            strtotime($row['created_at'])
                        ) ?>
                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>