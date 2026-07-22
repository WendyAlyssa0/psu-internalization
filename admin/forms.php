<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();


/* DELETE FORM */
if(isset($_GET['delete'])){

    $stmt = $pdo->prepare("
        DELETE FROM forms
        WHERE id=?
    ");

    $stmt->execute([
        $_GET['delete']
    ]);

    header("Location: dashboard.php?page=forms");
    exit();
}


/* FETCH */
$stmt = $pdo->query("
    SELECT *
    FROM forms
    ORDER BY id DESC
");

$forms = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="content">

<div class="card">


<div class="card-header">

<h3>
<i class="fa-solid fa-file-lines"></i>
Application Forms
</h3>

</div>


<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Form Name</th>
<th>Description</th>
<th>Action</th>

</tr>

</thead>


<tbody>


<?php if(!empty($forms)): ?>


<?php foreach($forms as $f): ?>


<tr>

<td>
<?= $f['id'] ?>
</td>


<td>
<?= htmlspecialchars($f['form_name']) ?>
</td>


<td>
<?= htmlspecialchars($f['description']) ?>
</td>


<td>

<a href="dashboard.php?page=forms&delete=<?= $f['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete form?')">

<i class="fa fa-trash"></i>

</a>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td colspan="4" class="text-center">
No forms found
</td>

</tr>


<?php endif; ?>


</tbody>

</table>


</div>

</div>