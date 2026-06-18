<?php

function renderTable($data)
{
    if (empty($data)) {
        echo "<p class='empty-msg'>No records found.</p>";
        return;
    }

    echo "<div class='table-card'><table>";

    echo "
        <thead>
            <tr>
                <th>ID</th>
                <th>Applicant</th>
                <th>Program</th>
                <th>Department</th>
                <th>Mobility</th>
                <th>Institution</th>
                <th>Country</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
    ";

    foreach ($data as $a) {

        $status = strtolower($a['status']);

        $class = match ($status) {
            'approved' => 'status-approved',
            'rejected' => 'status-rejected',
            'under evaluation' => 'status-evaluation',
            default => 'status-pending'
        };

        echo "
        <tr>
            <td>#".h($a['id'])."</td>
            <td>".h($a['applicant_name'])."</td>
            <td>".h($a['program'])."</td>
            <td>".h($a['department'])."</td>
            <td>".h($a['mobility_type'])."</td>
            <td>".h($a['institution'])."</td>
            <td>".h($a['country'])."</td>
            <td><span class='status-badge $class'>".ucfirst($status)."</span></td>
            <td>".(!empty($a['created_at']) ? date('M d, Y', strtotime($a['created_at'])) : 'N/A')."</td>
        </tr>";
    }

    echo "</tbody></table></div>";
}
?>