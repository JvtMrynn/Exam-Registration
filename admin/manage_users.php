<?php
ini_set('display_errors', '0');
error_reporting(E_ERROR | E_PARSE);
require_once('../connection.php');

// Applicant Type Mapping
$applicant_type_mapping = [
    'shs' => 'Senior High School Student/Graduate',
    'transferee' => 'Transferee',
    'old_curriculum' => 'Non-Senior High School Graduate',
    'als' => 'ALS Passer',
    'pwd' => 'Person With Disability',
    'ip' => 'Indigenous People',
    'employee_dependent' => 'SLSU Employee Dependent',
    'sports_winner' => 'Sports/Culture/Arts Winner',
    'solo_parent' => 'Solo Parent',
];

// Required and Extra Documents
$required_docs = [
    'id_picture_path' => '2x2 ID Picture',
    'brgy_cert_path' => 'Barangay Certificate',
    'income_cert_path' => 'Income Certificate',
    'psa_cert_path' => 'PSA Certificate',
];

$extra_docs = [
    'shs' => [
        'grade11_cert' => 'Grade 11 Records',
        'grade12_cert' => 'Grade 12 Certificate',
    ],
    'transferee' => [
        'tor' => 'Transcript of Records (TOR)',
    ],
    'old_curriculum' => [
        'form137' => 'Form 137',
    ],
    'als' => [
        'als_cert' => 'Certificate of Rating',
        'eligibility_cert' => 'Eligibility Certificate for Tertiary Level',
    ],
    'pwd' => [
        'pwd_id' => 'PWD ID',
    ],
    'ip' => [
        'ip_id' => 'Indigenous People ID',
    ],
    'employee_dependent' => [
        'employment_cert' => 'SLSU Certificate of Employment',
    ],
    'sports_winner' => [
        'award_cert' => 'Certificate of Winning',
    ],
    'solo_parent' => [
        'solo_parent_id' => 'Solo Parent ID',
    ],
];

// // Handle Approve Request with Prepared Statement
// if (isset($_POST['approve_user'])) {
//     $usn = mysqli_real_escape_string($con, $_POST['usn']); // Sanitize input
//     $stmt = $con->prepare("UPDATE registration SET approved = 1 WHERE USN = ?");
//     $stmt->bind_param("s", $usn);

//     if ($stmt->execute()) {
//         $success_message = "User approved successfully.";
//     } else {
//         $error_message = "Error approving user. Please try again.";
//     }
//     $stmt->close();
// }

// Handle Comments Update
if (isset($_POST['update_comments'])) {
    $usn = mysqli_real_escape_string($con, $_POST['usn']);
    $comments = mysqli_real_escape_string($con, $_POST['comments']);
    
    $stmt = $con->prepare("UPDATE registration SET comments = ? WHERE USN = ?");
    $stmt->bind_param("ss", $comments, $usn);

    if ($stmt->execute()) {
        $success_message = "Comments updated successfully.";
    } else {
        $error_message = "Error updating comments. Please try again.";
    }
    $stmt->close();
}

// Handle Toggle Approval Request with Prepared Statement
if (isset($_POST['toggle_approval'])) {
    $usn = mysqli_real_escape_string($con, $_POST['usn']); // Sanitize input

    // First, check the current approval status of the user
    $stmt = $con->prepare("SELECT approved FROM registration WHERE USN = ?");
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    // Toggle the approval status
    if ($current_status == 1) {
        // If the user is approved (1), set it to 0 (disapprove)
        $new_status = 0;
        $status_message = "User disapproved successfully.";
    } else {
        // If the user is not approved (0), set it to 1 (approve)
        $new_status = 1;    
        $status_message = "User approved successfully.";
    }

    // Update the user's approval status
    $stmt = $con->prepare("UPDATE registration SET approved = ? WHERE USN = ?");
    $stmt->bind_param("is", $new_status, $usn);

    if ($stmt->execute()) {
        // Success: display the status message
        echo "<script type='text/javascript'>
                alert('$status_message');
                window.location.href = window.location.href;
            </script>";
    } else {
        // Error: display an error message
        $error_message = "Error updating approval status. Please try again.";
        echo "<script type='text/javascript'>
                alert('$error_message');
                window.location.href = window.location.href;
            </script>";
    }

    $stmt->close();
}

// Fetch All Users
$users = mysqli_query($con, "SELECT * FROM registration");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Manage Users</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        th {
            text-align: center;
            padding: 10px 30px 10px 30px;
        }
    </style>
</head>
<body>
    <h1 style="font-family: 'Acme', sans-serif; color: #060e4d; font-size: 36px; text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); text-align: center;">
        List of Students Registered
    </h1>

    <!-- Success or Error Message -->
    <?php if (isset($success_message)): ?>
        <div style="color: green; text-align: center;"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div style="color: red; text-align: center;"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Birth Date</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Applicant Type</th>
                <th>Required Documents</th>
                <th>Extra Documents</th>
                <th>Status</th>
                <th>Comments</th>
                <th>Date Created / Recent Modified</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_array($users)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                    <td><?php echo htmlspecialchars($row['dob']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo $applicant_type_mapping[$row['applicant_type']] ?? 'Unknown'; ?></td>
                    <td>
                        <?php foreach ($required_docs as $field => $label): ?>
                            <div>
                                <strong><?php echo htmlspecialchars($label); ?>:</strong>
                                <?php if (!empty($row[$field])): ?>
                                    <a href="../<?php echo htmlspecialchars($row[$field]); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    <span style="color: red;">Not Uploaded</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php if (isset($extra_docs[$row['applicant_type']])): ?>
                            <?php foreach ($extra_docs[$row['applicant_type']] as $field => $label): ?>
                                <?php if (!empty($row[$field])): ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($label); ?>:</strong> 
                                        <a href="../<?php echo htmlspecialchars($row[$field]); ?>" target="_blank">View</a>
                                    </div>
                                <?php else: ?>
                                    <span style="color: red;">Not Uploaded</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <em>None</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Display approval status -->
                        <?php echo ($row['approved'] == 1) ? "<span style='color: green;'>Approved</span>" : "<span style='color: red;'>Pending</span>"; ?>
                    </td>
                    <td>
                        <!-- Display Comments Section -->
                        <form method="post">
                            <input type="hidden" name="usn" value="<?php echo htmlspecialchars($row['USN']); ?>" />
                            <textarea name="comments" rows="3" cols="40"><?php echo htmlspecialchars($row['comments']); ?></textarea>
                            <br>
                            <button type="submit" name="update_comments">Save Comments</button>
                        </form>
                    </td>
                    <td>
                        <!-- Combine 'Date Created' and 'Recent Modified' -->
                        <strong>Created:</strong> <?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?><br>
                        <strong>Modified:</strong> <?php echo date('Y-m-d H:i:s', strtotime($row['updated_at'])); ?>
                    </td>
                    <td>
                    <!-- Toggle Approval Button -->
                    <?php if ($row['approved'] == 0): ?>
                        <!-- Approve Button -->
                        <form method="post">
                            <input type="hidden" name="usn" value="<?php echo htmlspecialchars($row['USN']); ?>" />
                            <button type="submit" name="toggle_approval" onclick="return confirm('Are you sure you want to approve this user?');">
                                Approve
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Disapprove Button -->
                        <form method="post">
                            <input type="hidden" name="usn" value="<?php echo htmlspecialchars($row['USN']); ?>" />
                            <button type="submit" name="toggle_approval" onclick="return confirm('Are you sure you want to disapprove this user?');">
                                Disapprove
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
