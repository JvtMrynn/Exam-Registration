<?php
    ini_set('display_errors', '0'); 
    error_reporting(E_ERROR | E_PARSE);

    require_once('../connection.php');

    // Get the logged-in user's USN from the session
    $user = $_SESSION['user'];

    // Fetch the user's data from the database
    $query = mysqli_query($con, "SELECT * from registration WHERE usn = '$user'");
    $row = mysqli_fetch_array($query);

    // Mapping applicant type to user-friendly display names
    $applicant_type_mapping = [
        'shs' => 'Senior High School Student/Graduate',
        'transferee' => 'Transferee',
        'old_curriculum' => 'Non-Senior High School Graduate',
        'als' => 'ALS Passer',
        'pwd'=> 'Person With Disability',
        'ip' => 'Indigenous People',
        'employee_dependent' => 'SLSU Employee Dependent',
        'sports_winner' => 'Sports/Culture/Arts Winner',
        'solo_parent' => 'Solo Parent',
    ];

    // Get the applicant type from the database and display the corresponding label
    $applicant_type = $row['applicant_type'];
    $display_applicant_type = isset($applicant_type_mapping[$applicant_type]) ? $applicant_type_mapping[$applicant_type] : 'Unknown';

    // Prepare the required and extra documents arrays
    $required_docs = [
        'id_picture' => 'ID Picture',
        'brgy_cert' => 'Brgy Certificate',
        'income_cert' => 'Income Certificate',
        'psa_cert' => 'PSA Certificate',
    ];

    $extra_docs = [
        'shs' => [
            'grade11_cert' => 'Grade 11 Records',
            'grade12_cert' => 'Grade 12 Certificate',
        ],
        'transferee' => [
            'tor' => 'Transcript of Records (TOR)'
        ],
        'old_curriculum' => [
            'form137' => 'Form 137',
        ],
        'als' => [
            'als_cert' => 'Certificate of Rating (Signed)',
            'eligibility_cert' => 'Certificate for Tertiary Level',
        ],
        'pwd' => [
            'pwd_id' => 'PWD ID',
        ],
        'ip' => [
            'ip_id' => 'Indigenous People ID',
        ],
        'employee_dependent' => [
            'employee_cert' => 'SLSU Certificate of Employment (Signed by HRMD)',
        ],
        'sports_winner' => [
            'award_cert' => 'Certificate of Winning (Signed by School Principal)',
        ],
        'solo_parent' => [
            'solo_parent_id' => 'Solo Parent ID',
        ],
    ];

    // Handle form submission for updating the profile
    if (isset($_POST['Update'])) {
        // Extract form values
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $dob = $_POST['dob'];
        $phone = $_POST['phone'];
        $applicant_type = $row['applicant_type'];  // Use the fixed applicant type
        
        // Initialize file paths array
        $file_paths = [];
        $upload_dir = "uploads/$user/"; // Directory to store uploaded files

        // Handle file uploads for required docs
        foreach ($required_docs as $field => $label) {
            // If a file is uploaded, update the file path
            if ($_FILES[$field]['name']) {
                $file_paths[$field] = $upload_dir . basename($_FILES[$field]['name']);
                move_uploaded_file($_FILES[$field]['tmp_name'], "../$file_paths[$field]");
            } else {
                // If no file is uploaded, retain the existing file path from the database
                if (!empty($row[$field])) {
                    // Retain existing file path if it exists in the database
                    $file_paths[$field] = $row[$field];
                } else {
                    // Otherwise, set it to null or empty string, depending on your database handling
                    $file_paths[$field] = null;  // or '' if you want empty strings
                }
            }
        }

        // Handle file uploads for extra docs based on applicant type
        if (isset($extra_docs[$applicant_type])) {
            foreach ($extra_docs[$applicant_type] as $field => $label) {
                // If a file is uploaded, update the file path
                if ($_FILES[$field]['name']) {
                    $file_paths[$field] = $upload_dir . basename($_FILES[$field]['name']);
                    move_uploaded_file($_FILES[$field]['tmp_name'], "../$file_paths[$field]");
                } else {
                    // If no file is uploaded, retain the existing file path from the database
                    if (!empty($row[$field])) {
                        // Retain existing file path if it exists in the database
                        $file_paths[$field] = $row[$field];
                    } else {
                        // Otherwise, set it to null or empty string
                        $file_paths[$field] = null;
                    }
                }
            }
        }

        // Ensure all other fields retain their values from the database if no new value is provided
        $file_paths['id_picture'] = !empty($file_paths['id_picture']) ? $file_paths['id_picture'] : $row['id_picture_path'];
        $file_paths['brgy_cert'] = !empty($file_paths['brgy_cert']) ? $file_paths['brgy_cert'] : $row['brgy_cert_path'];
        $file_paths['income_cert'] = !empty($file_paths['income_cert']) ? $file_paths['income_cert'] : $row['income_cert_path'];
        $file_paths['psa_cert'] = !empty($file_paths['psa_cert']) ? $file_paths['psa_cert'] : $row['psa_cert_path'];

        // Prepare the SQL query with the file paths
        $query = "UPDATE registration SET 
            fname = '$fname',
            lname = '$lname',
            dob = '$dob',
            phone = '$phone',
            id_picture_path = '{$file_paths['id_picture']}',
            brgy_cert_path = '{$file_paths['brgy_cert']}',
            income_cert_path = '{$file_paths['income_cert']}',
            psa_cert_path = '{$file_paths['psa_cert']}',
            applicant_type = '$applicant_type'";

        // Add extra documents fields based on applicant type
        foreach ($extra_docs[$applicant_type] ?? [] as $field => $label) {
            $query .= ", $field = '{$file_paths[$field]}'";
        }

        // Add additional documents that are always present
        $query .= ", grade11_cert = '{$file_paths['grade11_cert']}', 
                    grade12_cert = '{$file_paths['grade12_cert']}',
                    tor = '{$file_paths['tor']}',
                    form137 = '{$file_paths['form137']}',
                    als_cert = '{$file_paths['als_cert']}',
                    eligibility_cert = '{$file_paths['eligibility_cert']}',
                    pwd_id = '{$file_paths['pwd_id']}',
                    ip_id = '{$file_paths['ip_id']}',
                    employment_cert = '{$file_paths['employment_cert']}',
                    award_cert = '{$file_paths['award_cert']}',
                    solo_parent_id = '{$file_paths['solo_parent_id']}'
                WHERE USN = '$user'";

        // Execute the update query
        $run = mysqli_query($con, $query);
        if ($run) {
            $err = "<font color='green' align='center'>Profile Updated Successfully...!</font>";
        } else {
            $err = "<font color='red' align='center'>Error in Updating Profile.!</font>";
        }


    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" type="text/css" href="update.css">
</head>

<body>
    <div class="mcontainer">
        <form name="register" method="post" class="myform" action="" enctype="multipart/form-data">
            <h1 class="tit">Update Your Profile</h1>
            <?php echo @$err; ?>
            <hr>
            <table width="100%">
                <tr>
                    <td>
                        <label class="required">First Name (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="text" autocomplete="off" name="fname" placeholder="First Name" class="required" readonly value="<?php echo $row['fname']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label class="required">Last Name (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="text" name="lname" autocomplete="off" placeholder="Last Name" readonly value="<?php echo $row['lname']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>Birth Date (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="date" name="dob" autocomplete="off" readonly value="<?php echo $row['dob']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label class="required">USN (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="text" name="USN" autocomplete="off" readonly value="<?php echo $row['USN']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label class="required">Email (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="email" name="email" autocomplete="off" readonly value="<?php echo $row['email']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>Phone (Can be changed)</label>
                    </td>
                    <td class="td1">
                        <input type="phone" autocomplete="off" name="phone" id="phone" placeholder="9998887776" value="<?php echo $row['phone']; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <label>Applicant Type (Static)</label>
                    </td>
                    <td class="td1">
                        <input type="text" name="applicant_type" readonly value="<?php echo $display_applicant_type; ?>" />
                    </td>
                </tr>

                <!-- File uploads for required documents -->
                <?php foreach ($required_docs as $field => $label): ?>
                <tr>
                    <td><label><?php echo $label; ?></label></td>
                    <td class="td1">
                        <input type="file" name="<?php echo $field; ?>" accept=".pdf,.jpg,.jpeg,.png" />
                    </td>
                </tr>
                <?php endforeach; ?>

                <!-- Conditional file uploads for extra documents based on applicant type -->
                <?php if (isset($extra_docs[$row['applicant_type']])): ?>
                    <?php foreach ($extra_docs[$row['applicant_type']] as $field => $label): ?>
                    <tr>
                        <td><label><?php echo $label; ?></label></td>
                        <td class="td1">
                            <input type="file" name="<?php echo $field; ?>" accept=".pdf,.jpg,.jpeg,.png" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr>
                    <td><input type="submit" name="Update" class="login_btn" value="Update" /></td>
                    <td><input type="reset" onClick="window.location.href=window.location.href" class="reset_btn" value="Reset" /></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
