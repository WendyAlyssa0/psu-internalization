<?php
require_once __DIR__ . '/../config/db.php';

$pdo = db();

$stmt = $pdo->query("
    SELECT
        p.id,
        p.program_name,
        p.program_type,
        pr.institution_name AS partner_institution,
        pr.country,
        p.start_date,
        p.end_date,
        p.status
    FROM programs p
    LEFT JOIN partners pr
        ON p.partner_id = pr.id
    WHERE p.status IN ('Active','Upcoming')
    ORDER BY p.start_date ASC
");

$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
.programs-page{
    padding:24px;
}

.page-header{
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
}

.page-header-icon{
    width:60px;
    height:60px;
    border-radius:16px;
    background:#0E1C36;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}

.page-header h2{
    margin:0;
    color:#0E1C36;
}

.page-header p{
    margin:5px 0 0;
    color:#666;
}

.programs-container{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:20px;
}

.program-card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

.program-header{
    background:#0E1C36;
    color:#fff;
    padding:18px;
}

.program-header h3{
    margin:0;
    font-size:18px;
}

.program-body{
    padding:18px;
}

.program-body p{
    margin:10px 0;
    display:flex;
    gap:10px;
    align-items:center;
    color:#444;
}

.program-body i{
    color:#C9A227;
    width:18px;
}

.program-footer{
    padding:18px;
    border-top:1px solid #eee;
}

.btn-view{
    width:100%;
    border:none;
    padding:12px;
    border-radius:10px;
    cursor:pointer;
    background:#0E1C36;
    color:#fff;
    font-weight:600;
}

.btn-view:hover{
    opacity:.9;
}

.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

.modal-overlay.active{
    display:flex;
}

.modal{
    background:#fff;
    width:90%;
    max-width:800px;
    border-radius:18px;
    overflow:hidden;
}

.modal-header{
    background:#0E1C36;
    color:#fff;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.modal-header h3{
    margin:0;
}

.modal-close{
    border:none;
    background:none;
    color:#fff;
    font-size:20px;
    cursor:pointer;
}

.modal-body{
    padding:25px;
}

.modal-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
    margin-bottom:20px;
}

.modal-field{
    background:#f8fafc;
    padding:15px;
    border-radius:12px;
}

.modal-label{
    display:block;
    font-size:12px;
    color:#666;
    margin-bottom:5px;
    text-transform:uppercase;
}

.modal-description{
    background:#f8fafc;
    padding:15px;
    border-radius:12px;
    margin-top:15px;
}

.modal-footer{
    margin-top:25px;
    text-align:right;
}

.btn-apply{
    display:inline-block;
    padding:12px 24px;
    border-radius:10px;
    text-decoration:none;
    background:#C9A227;
    color:#fff;
    font-weight:600;
}
</style>

<div class="programs-page">

    <div class="page-header">
        <div class="page-header-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>

        <div>
            <h2>Available Programs</h2>
            <p>Browse international mobility opportunities offered by PSU.</p>
        </div>
    </div>

    <div class="programs-container">

        <?php foreach($programs as $program): ?>

        <div class="program-card">

            <div class="program-header">
                <h3><?= e($program['program_name']) ?></h3>
            </div>

            <div class="program-body">

                <p>
                    <i class="fa-solid fa-building"></i>
                    <?= e($program['partner_institution']) ?>
                </p>

                <p>
                    <i class="fa-solid fa-location-dot"></i>
                    <?= e($program['country']) ?>
                </p>

                    <p>
                        <i class="fa-solid fa-layer-group"></i>
                        <?= e($program['program_type']) ?>
                    </p>

                <p>
                    <i class="fa-solid fa-calendar"></i>
                    <?= date('M d, Y', strtotime($program['start_date'])) ?>
                </p>

            </div>

            <div class="program-footer">

                <?php
                $jsonProgram = htmlspecialchars(
                    json_encode($program, JSON_HEX_APOS | JSON_HEX_QUOT),
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>

                <button
                    class="btn-view"
                    onclick='viewProgram(<?= $jsonProgram ?>)'>
                    View Details
                </button>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- MODAL -->
<div class="modal-overlay" id="programModal">

    <div class="modal">

        <div class="modal-header">
            <h3 id="modalProgramName"></h3>

            <button
                class="modal-close"
                onclick="closeModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">

            <div class="modal-grid">

                <div class="modal-field">
                    <span class="modal-label">Partner Institution</span>
                    <span id="modalPartner"></span>
                </div>

                <div class="modal-field">
                    <span class="modal-label">Country</span>
                    <span id="modalCountry"></span>
                </div>

                <div class="modal-field">
                    <span class="modal-label">Program Type</span>
                    <span id="modalType"></span>
                </div>

                <div class="modal-field">
                    <span class="modal-label">Duration</span>
                    <span id="modalDuration"></span>
                </div>

            </div>

            <div class="modal-footer">

                <a
                    href="#"
                    id="applyLink"
                    class="btn-apply">
                    Apply Now
                </a>

            </div>

        </div>

    </div>

</div>

<script>
function viewProgram(program)
{
    document.getElementById('modalProgramName').textContent =
        program.program_name;

    document.getElementById('modalPartner').textContent =
        program.partner_institution;

    document.getElementById('modalCountry').textContent =
        program.country;

    document.getElementById('modalType').textContent =
        program.program_type;

    document.getElementById('modalDuration').textContent =
        formatDate(program.start_date) +
        ' - ' +
        formatDate(program.end_date);

    document.getElementById('modalDescription').textContent =
        'No description available.';

    document.getElementById('applyLink').href =
        '?page=apply_program&program_id=' + program.id;

    document.getElementById('programModal')
        .classList.add('active');
}

function closeModal()
{
    document.getElementById('programModal')
        .classList.remove('active');
}

function formatDate(date)
{
    if(!date) return 'N/A';

    return new Date(date).toLocaleDateString(
        'en-US',
        {
            year:'numeric',
            month:'short',
            day:'numeric'
        }
    );
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeModal();
    }
});
</script>