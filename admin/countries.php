<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

/* ADD COUNTRY */
if (isset($_POST['add_country'])) {

    $country_name = trim($_POST['country_name']);
    $country_code = strtoupper(trim($_POST['country_code']));
    $status = $_POST['status'];

    $stmt = $pdo->prepare("
        INSERT INTO countries
        (country_name,country_code,status)
        VALUES (?,?,?)
    ");

    $stmt->execute([
        $country_name,
        $country_code,
        $status
    ]);

    header("Location: dashboard.php?page=countries");
    exit;
}

/* UPDATE COUNTRY */
if (isset($_POST['update_country'])) {

    $id = $_POST['id'];

    $stmt = $pdo->prepare("
        UPDATE countries
        SET
            country_name=?,
            country_code=?,
            status=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['country_name'],
        strtoupper($_POST['country_code']),
        $_POST['status'],
        $id
    ]);

    header("Location: dashboard.php?page=countries");
    exit;
}

/* DELETE */
if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("
        DELETE FROM countries
        WHERE id=?
    ");

    $stmt->execute([$_GET['delete']]);

    header("Location: dashboard.php?page=countries");
    exit;
}

/* SEARCH */
$search = $_GET['search'] ?? '';

$stmt = $pdo->prepare("
    SELECT *
    FROM countries
    WHERE country_name LIKE ?
       OR country_code LIKE ?
    ORDER BY country_name ASC
");

$stmt->execute([
    "%$search%",
    "%$search%"
]);

$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="../asset/css/countries.css"

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h3>
            <i class="fa-solid fa-earth-asia"></i>
            Country Management
        </h3>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addCountryModal">

            <i class="fa-solid fa-plus"></i>
            Add Country

        </button>

    </div>

    <br>

    <form method="GET">

        <input type="hidden"
               name="page"
               value="countries">

        <div class="row">

            <div class="col-md-4">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search country..."
                    value="<?= htmlspecialchars($search) ?>">

            </div>

            <div class="col-md-2">

                <button class="btn btn-primary">

                    Search

                </button>

            </div>

        </div>

    </form>

    <br>

    <table class="table table-bordered table-hover">

        <thead>

        <tr>

            <th>ID</th>
            <th>Country Code</th>
            <th>Country Name</th>
            <th>Status</th>
            <th width="120">Actions</th>

        </tr>

        </thead>

        <tbody>

        <?php if(count($countries)): ?>

            <?php foreach($countries as $country): ?>

                <tr>

                    <td><?= $country['id'] ?></td>

                    <td><?= htmlspecialchars($country['country_code']) ?></td>

                    <td><?= htmlspecialchars($country['country_name']) ?></td>

                    <td>

                        <?php if($country['status']=='Active'): ?>

                            <span class="badge bg-success">

                                Active

                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">

                                Inactive

                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <button
                            class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#edit<?= $country['id'] ?>">

                            <i class="fa-solid fa-pen"></i>

                        </button>

                        <a href="dashboard.php?page=countries&delete=<?= $country['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete country?')">

                            <i class="fa-solid fa-trash"></i>

                        </a>

                    </td>

                </tr>

                <!-- EDIT MODAL -->

                <div class="modal fade"
                     id="edit<?= $country['id'] ?>">

                    <div class="modal-dialog">

                        <div class="modal-content">

                            <form method="POST">

                                <div class="modal-header">

                                    <h5>Edit Country</h5>

                                </div>

                                <div class="modal-body">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= $country['id'] ?>">

                                    <div class="mb-3">

                                        <label>
                                            Country Name
                                        </label>

                                        <input
                                            type="text"
                                            name="country_name"
                                            class="form-control"
                                            value="<?= htmlspecialchars($country['country_name']) ?>"
                                            required>

                                    </div>

                                    <div class="mb-3">

                                        <label>
                                            Country Code
                                        </label>

                                        <input
                                            type="text"
                                            name="country_code"
                                            class="form-control"
                                            value="<?= htmlspecialchars($country['country_code']) ?>"
                                            required>

                                    </div>

                                    <div class="mb-3">

                                        <label>Status</label>

                                        <select
                                            name="status"
                                            class="form-control">

                                            <option
                                                value="Active"
                                                <?= $country['status']=='Active' ? 'selected' : '' ?>>
                                                Active
                                            </option>

                                            <option
                                                value="Inactive"
                                                <?= $country['status']=='Inactive' ? 'selected' : '' ?>>
                                                Inactive
                                            </option>

                                        </select>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button
                                        type="submit"
                                        name="update_country"
                                        class="btn btn-primary">

                                        Save Changes

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="5" class="text-center">

                    No countries found

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<!-- ADD COUNTRY MODAL -->

<div class="modal fade"
     id="addCountryModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5>Add Country</h5>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>Country Name</label>

                        <input
                            type="text"
                            name="country_name"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>Country Code</label>

                        <input
                            type="text"
                            name="country_code"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label>Status</label>

                        <select
                            name="status"
                            class="form-control">

                            <option value="Active">
                                Active
                            </option>

                            <option value="Inactive">
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="submit"
                        name="add_country"
                        class="btn btn-success">

                        Save Country

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>