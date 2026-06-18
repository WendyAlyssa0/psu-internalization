<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="../asset/css/download_form.css">

<div class="content-card">

    <div class="page-header">
        <div>
            <h2>Download Forms</h2>
            <p>Download required application and mobility forms.</p>
        </div>
    </div>

    <div class="form-list">

        <div class="form-item">
            <div>
                <h3>Application Form</h3>
                <p>Main mobility application form (PDF)</p>
            </div>

            <a href="../asset/forms/application_form.pdf" download class="btn">
                <i class="fa-solid fa-download"></i>
                Download
            </a>
        </div>

        <div class="form-item">
            <div>
                <h3>Recommendation Letter</h3>
                <p>Required faculty recommendation template</p>
            </div>

            <a href="../asset/forms/recommendation_letter.pdf" download class="btn">
                <i class="fa-solid fa-download"></i>
                Download
            </a>
        </div>

        <div class="form-item">
            <div>
                <h3>Medical Certificate</h3>
                <p>Health clearance form</p>
            </div>

            <a href="../asset/forms/medical_certificate.pdf" download class="btn">
                <i class="fa-solid fa-download"></i>
                Download
            </a>
        </div>

    </div>

</div>