<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<div class="content">

    <div class="notification-card">

        <div class="notification-header">
            <div>
                <h3>
                    <i class="ti ti-message"></i>
                    Messages
                </h3>

                <p>
                    Applicant and admin messages
                </p>
            </div>
        </div>


        <div class="notification-empty">
            No messages yet.
        </div>


    </div>

</div>