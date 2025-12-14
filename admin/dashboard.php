<?php
require_once "auth.php"; 

require_once "../classes/student.php";
$studentObj = new Student();

$page_title = "Dashboard Overview";

// role check
$assignedCollege = null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admissions Officer') {
    $assignedCollege = $_SESSION['college_assigned'];
    $page_title .= " (" . htmlspecialchars($assignedCollege) . ")";
}

$stats = $studentObj->getDashboardStatistics($assignedCollege);

$barChartTitle = "Applicants per College";
$barLabels = [];
$barData = [];

if ($assignedCollege) {
    $barChartTitle = "Applicants per Program";
    if (isset($stats['by_program']) && is_array($stats['by_program'])) {
        foreach ($stats['by_program'] as $row) {
            $barLabels[] = html_entity_decode($row['academic_program']); 
            $barData[] = $row['count'];
        }
    }
} else {
    if (isset($stats['by_college']) && is_array($stats['by_college'])) {
        foreach ($stats['by_college'] as $row) {
            $barLabels[] = html_entity_decode($row['college']); 
            $barData[] = $row['count'];
        }
    }
}

$statusLabels = [];
$statusData = [];
if (isset($stats['by_enrollment_status']) && is_array($stats['by_enrollment_status'])) {
    foreach ($stats['by_enrollment_status'] as $status => $count) {
        $statusLabels[] = $status;
        $statusData[] = $count;
    }
}


include "template_header.php";
?>

<style>
    
    .kpi-container {
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 1.5rem; 
        max-width: 1000px; 
        margin-left: 0; 
        margin-right: auto; 
    }

    .charts-row {
        display: flex;
        flex-wrap: wrap; 
        gap: 2rem;
        margin-bottom: 2.5rem;
        align-items: stretch; 
    }


    .bar-chart-column {
        flex: 2; 
        min-width: 300px; 
        display: flex;
    }
    

    .pie-chart-column {
        flex: 1;
        min-width: 300px;
        display: flex;
    }

    .chart-container {
        width: 100%;
        height: 100%;
      
        margin-bottom: 0; 
    }
    
    .kpi-card.enrolled { border-left: 5px solid #28a745; }
    .kpi-card.pending { border-left: 5px solid #ffc107; }
</style>


<?php
$alert_message = ''; $alert_type = '';
if (isset($_GET['message'])) {
    $alert_message = htmlspecialchars($_GET['message']); $alert_type = 'success';
} elseif (isset($_GET['error'])) {
    $alert_message = htmlspecialchars($_GET['error']); $alert_type = 'danger';
}
?>
<?php if (!empty($alert_message)): ?>
    <div class="alert alert-<?php echo $alert_type; ?>" id="alert-box">
        <span><i class="fas fa-<?php echo ($alert_type == 'success' ? 'check-circle' : 'exclamation-triangle'); ?>"></i> <?php echo $alert_message; ?></span>
        <button class="close-btn" onclick="document.getElementById('alert-box').style.display='none'">&times;</button>
    </div>
<?php endif; ?>


<div class="kpi-container">
    <div class="kpi-card">
        <h3>Total Applicants</h3>
        <div class="number"><?php echo $stats['total_applicants']; ?></div>
    </div>
    
    <div class="kpi-card enrolled">
        <h3>Enrolled Students</h3>
        <div class="number" style="color: #28a745;"><?php echo $stats['total_enrolled']; ?></div>
    </div>

    <div class="kpi-card pending">
        <h3>Pending Applicants</h3>
        <div class="number" style="color: #d39e00;"><?php echo $stats['total_pending']; ?></div>
    </div>

    <div class="kpi-card">
        <h3>Scholarship Applicants</h3>
        <div class="number"><?php echo $stats['total_scholarships'] ?? 0; ?></div>
    </div>
</div>

<div class="charts-row">

    <div class="bar-chart-column">
        <div class="chart-container">
            <h2><?php echo $barChartTitle; ?></h2>
            <canvas id="collegeChart"></canvas>
        </div>
    </div>

    <div class="pie-chart-column">
        <div class="chart-container">
            <h2>Student Classification</h2>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // bar Chart (Applicants per College)
        const barCtx = document.getElementById('collegeChart'); 
        if (barCtx) { 
            const barLabels = <?php echo json_encode($barLabels); ?>;
            const barData = <?php echo json_encode($barData); ?>;

            new Chart(barCtx, {
                type: 'bar', 
                data: {
                    labels: barLabels, 
                    datasets: [{
                        label: '# of Applicants',
                        data: barData, 
                        backgroundColor: 'rgba(164, 4, 4, 0.7)', 
                        borderColor: 'rgba(164, 4, 4, 1)',
                        borderWidth: 1,
                        borderRadius: 5 
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } },
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Pie, enrollment Status
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const statusLabels = <?php echo json_encode($statusLabels); ?>;
            const statusData = <?php echo json_encode($statusData); ?>;

            new Chart(statusCtx, {
                type: 'pie', 
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: '# of Students',
                        data: statusData,
                        backgroundColor: [
                            'rgba(164, 4, 4, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: '#fff', 
                        borderWidth: 2      
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top', 
                        },
                        title: {
                            display: false 
                        }
                    }
                }
            });
        }
    });
</script>

<?php

include "template_footer.php";
?>