<?php

require_once __DIR__ . '/../config/db.php';

$pdo = db();


/* DELETE */

if(isset($_GET['delete'])){

    $stmt = $pdo->prepare("
        DELETE FROM travel_information
        WHERE id=?
    ");

    $stmt->execute([
        $_GET['delete']
    ]);

    header("Location: dashboard.php?page=travel_information");
    exit();

}



/* FETCH */

$stmt = $pdo->query("
    SELECT *
    FROM travel_information
    ORDER BY id DESC
");

$travels = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="content">

<div class="card">


<div class="card-header">

<h3>
<i class="fa-solid fa-plane"></i>
Travel Information
</h3>

</div>



<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Applicant</th>
<th>Destination</th>
<th>Departure Date</th>
<th>Return Date</th>
<th>Action</th>

</tr>

</thead>


<tbody>


<?php if(!empty($travels)): ?>


<?php foreach($travels as $t): ?>


<tr>

<td>
<?= $t['id'] ?>
</td>


<td>
<?= htmlspecialchars($t['applicant_name'] ?? '') ?>
</td>


<td>
<?= htmlspecialchars($t['destination']) ?>
</td>


<td>
<?= $t['departure_date'] ?>
</td>


<td>
<?= $t['return_date'] ?>
</td>


<td>

<a 
href="dashboard.php?page=travel_information&delete=<?= $t['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete travel record?')">

<i class="fa fa-trash"></i>

</a>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td colspan="6" class="text-center">

No travel information found

</td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>

</div>