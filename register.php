<?php
ini_set('display_errors', '0'); 
error_reporting(E_ERROR | E_PARSE);

require_once('connection.php');
session_start();

if (isset($_POST['Register'])) {
    // Initialize error array
    $errors = [];
    
    // Extract POST data
    $fname = $_POST['fname'] ?? '';
    $lname = $_POST['lname'] ?? '';
    $USN = $_POST['USN'] ?? '';
    $psswd = $_POST['psswd'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $mail = $_POST['mail'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $applicant_type = $_POST['applicant_type'] ?? '';
    $dob = $_POST['dob'] ?? '';

    // Basic validation
    if (empty($fname)) $errors[] = "First name is required";
    if (empty($lname)) $errors[] = "Last name is required";
    if (empty($USN)) $errors[] = "Username is required";
    if (empty($psswd)) $errors[] = "Password is required";

    // Password validation
    if (strlen($psswd) < 8) $errors[] = "Password must be at least 8 characters long";
    if (!preg_match("/[A-Z]/", $psswd)) $errors[] = "Password must contain at least one uppercase letter";
    if (!preg_match("/[a-z]/", $psswd)) $errors[] = "Password must contain at least one lowercase letter";
    if (!preg_match("/[0-9]/", $psswd)) $errors[] = "Password must contain at least one number";
    if ($psswd !== $confirm_password) $errors[] = "Passwords do not match";

    // Email/Phone validation
    $has_valid_contact = !empty($mail) && filter_var($mail, FILTER_VALIDATE_EMAIL);
    $has_valid_contact = $has_valid_contact || (!empty($phone) && preg_match('/^[0-9]{10}$/', $phone));
    if (!$has_valid_contact) $errors[] = "Please provide either a valid email address or phone number";

    // Check if user already exists
    $sql = mysqli_query($con, "SELECT * FROM registration WHERE usn = '$USN'");
    if (mysqli_num_rows($sql) > 0) {
        $errors[] = "<div class='alert alert-warning'>Username Already Exists!</div>";
    }

    // Required Documents Validation
    $required_docs = [
        'id_picture' => '2x2 ID Picture',
        'brgy_cert' => 'Barangay Residency Certificate',
        'income_cert' => "Parent's/Guardian's Statement of Income",
        'psa_cert' => 'PSA/NSO Certificate'
    ];

    // Create user directory if it doesn't exist
    $upload_dir = "uploads/" . $USN . "/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Validate and upload required documents
    $uploaded_files = [];
    foreach ($required_docs as $field => $label) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) {
            $errors[] = "$label is required";
        } else {
            // Validate file
            $file = $_FILES[$field];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file_extension, $allowed_types)) {
                $errors[] = "$label must be in PDF, JPG, JPEG, or PNG format";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "$label must be less than 5MB";
            } else {
                // Generate unique filename
                $new_filename = $field . '_' . time() . '.' . $file_extension;
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    $uploaded_files[$field] = $new_filename;
                } else {
                    $errors[] = "Failed to upload $label";
                }
            }
        }
    }

    // Additional Requirements based on applicant type
    if (!empty($applicant_type)) {
        $extra_docs = [
            'shs' => ['grade11_cert' => 'Grade 11 records', 'grade12_cert' => 'Grade 12 certificate'],
            'transferee' => ['tor' => 'Transcript of Records'],
            'old_curriculum' => ['form137' => 'Form 137'],
            'als' => ['als_cert' => 'ALS Certificate', 'eligibility_cert' => 'Certificate of Eligibility'],
            'pwd' => ['pwd_id' => 'PWD ID'],
            'ip' => ['ip_id' => 'Indigenous People ID'],
            'employee_dependent' => ['employment_cert' => 'SLSU Employment Certificate'],
            'sports_winner' => ['award_cert' => 'Certificate of winning'],
            'solo_parent' => ['solo_parent_id' => 'Solo Parent ID']
        ];
        
        if (isset($extra_docs[$applicant_type])) {
            foreach ($extra_docs[$applicant_type] as $doc_field => $doc_label) {
                if (!isset($_FILES[$doc_field]) || $_FILES[$doc_field]['error'] !== 0) {
                    $errors[] = "$doc_label is required";
                } else {
                    $file = $_FILES[$doc_field];
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($file_extension, $allowed_types) || $file['size'] > $max_size) {
                        $errors[] = "$doc_label must be a valid file and less than 5MB";
                    } else {
                        $new_filename = $doc_field . '_' . time() . '.' . $file_extension;
                        $target_file = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $target_file)) {
                            $uploaded_files[$doc_field] = $new_filename;
                        } else {
                            $errors[] = "Failed to upload $doc_label";
                        }
                    }
                }
            }
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        $pass = md5($psswd);

        // Prepare file paths for database
        $file_paths = [];
        foreach ($required_docs as $field => $label) {
            $file_paths[$field] = isset($uploaded_files[$field]) ? $upload_dir . $uploaded_files[$field] : '';
        }
        
        foreach ($extra_docs[$applicant_type] ?? [] as $field => $label) {
            $file_paths[$field] = isset($uploaded_files[$field]) ? $upload_dir . $uploaded_files[$field] : '';
        }

        $query = "INSERT INTO registration (
            fname, lname, dob, USN, email, phone,
            id_picture_path, brgy_cert_path, income_cert_path, psa_cert_path,
            applicant_type, password, grade11_cert, grade12_cert, tor, form137,
            als_cert, eligibility_cert, pwd_id, ip_id, employment_cert, award_cert, solo_parent_id
        ) VALUES (
            '$fname', '$lname', '$dob', '$USN', '$mail', '$phone',
            '{$file_paths['id_picture']}', '{$file_paths['brgy_cert']}', '{$file_paths['income_cert']}', '{$file_paths['psa_cert']}',
            '$applicant_type', '$pass', '{$file_paths['grade11_cert']}', '{$file_paths['grade12_cert']}', '{$file_paths['tor']}', '{$file_paths['form137']}',
            '{$file_paths['als_cert']}', '{$file_paths['eligibility_cert']}', '{$file_paths['pwd_id']}', '{$file_paths['ip_id']}', '{$file_paths['employment_cert']}', '{$file_paths['award_cert']}', '{$file_paths['solo_parent_id']}'
        )";
        

        if (mysqli_query($con, $query)) {
            $success = "<div class='alert alert-success'>Registration successful! You can now login using your Username and password.</div>";
        } else {
            $errors[] = "Database error occurred: " . mysqli_error($con);
        }
    }
}
?>

<!-- register.php -->
<!-- REGISTRATION FORM -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Md Yaseen Ahmed">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!------ Include the above in your HEAD tag ---------->
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">

    <!-- Font special for pages-->
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Vendor CSS-->
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/datepicker/daterangepicker.css" rel="stylesheet" media="all">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
    
    <!--Bootsrap 4 CDN-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <!--Fontawesome CDN-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

    <!--Custom styles-->
    <link rel="stylesheet" type="text/css" href="css/reg_style.css">

    <!--Custom Favicon.-->
    <link rel="icon" type="image/png" sizes="64x64" href="css/images/slsu-logo.png">
    <style type="text/css">
        .back-to-top {
            position: fixed;
            bottom: 25px;
            right: 25px;
            display: none;
            outline: none;
        }

        .mh3:hover {
            border: 2px solid black;
            padding: 5px;
            border-radius: 5px;
            color: white;
            background-color: black;
        }

        .mnav ul li a:hover {
            color: whitesmoke;
            padding: 2px;
            border: 5px solid black;
            border-radius: 5px;
            background-color: black;
        }
    </style>

    <script type="text/javascript">
        window.onload = function() {
            document.getElementById("phone").onchange = passwdlen;
            document.getElementById("pass1").onchange = passwdlen2;
        }

        function passwdlen() {
            var num = document.getElementById("phone").value;
            if (num.length < 10)
                document.getElementById("phone").setCustomValidity("phone length shuld be = 10");
            else
                document.getElementById("phone").setCustomValidity('');
            //empty string means no validation error
        }

        function passwdlen2() {
            var pass = document.getElementById("pass1").value;
            if (pass.length < 8)
                document.getElementById("pass1").setCustomValidity("passwd length shuld be > 8");
            else
                document.getElementById("pass1").setCustomValidity('');
            //empty string means no validation error
        }
    </script>
    <title>Registration</title>
</head>

<body>
    <div id="progress"></div>
    <!-- Navigation -->
    <nav class="navbar mnav navbar-expand-lg navbar-dark static-top" style="background-color: #060e4d;padding:20px;border-bottom: 2px solid black;box-shadow: 3px 3px 5px black;">
        <div class="container" style="font-family:'PT Serif';font-size:22px;padding-right:0px;">
            <a style="margin-left:0%;padding-left:0px" class="navbar-brand" href="https://southernleytestateu.edu.ph">
                <h3 class="mh3">SLSU</h3>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#"><i class="fa fa-user-plus" aria-hidden="true"></i> Registration
                            <span class="sr-only">(current)</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fa fa-key" aria-hidden="true"></i> Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php"><i class="fa fa-lock" aria-hidden="true"></i> Admin</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="help.php"><i class="fa fa-question" aria-hidden="true"></i> Help</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><i class="fa fa-info-circle" aria-hidden="true"></i> About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!--Form-->
    <div class="mcontainer">
        <form name="register" method="post" class="myform" action="" enctype="multipart/form-data">
            <h1 class="tit">SLSU Registration</h1>
            
            <?php 
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<div class='error-message'>$error</div>";
                }
            }
            if (isset($success)) {
                echo $success;
            }
            ?>

            <!-- Basic Information Section -->
            <div class="section">
                <h3>Basic Information</h3>
                <table width="100%">
                    <tr>    
                        <td><label class="required-label">Username</label></td>
                        <td><input type="text" name="USN" required value="<?php echo @$USN; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Password</label></td>
                        <td>
                            <input type="password" name="psswd" id="pass1" required 
                                minlength="8" 
                                placeholder="Minimum 8 characters"
                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Confirm Password</label></td>
                        <td>
                            <input type="password" name="confirm_password" id="pass2" required 
                                minlength="8" 
                                placeholder="Confirm your password"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td><label class="required-label">First Name</label></td>
                        <td><input type="text" name="fname" required value="<?php echo @$fname; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Last Name</label></td>
                        <td><input type="text" name="lname" required value="<?php echo @$lname; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Birth Date</label></td>
                        <td><input type="date" name="dob" required value="<?php echo @$dob; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Contact Information</label></td>
                        <td>
                            <input type="email" name="mail" placeholder="Email Address" value="<?php echo @$mail; ?>" />
                            <span>OR</span>
                            <input type="text" name="phone" placeholder="Mobile Number" value="<?php echo @$phone; ?>" />
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Required Documents Section -->
            <div class="document-section">
                <h3>Required Documents</h3>
                <table width="100%">
                    <tr>
                        <td><label class="required-label">2x2 ID Picture (White background with nametag)</label></td>
                        <td><input type="file" name="id_picture" accept="image/*" required /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Barangay Residency Certificate</label></td>
                        <td><input type="file" name="brgy_cert" accept=".pdf,.jpg,.jpeg,.png" required /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">Parent's/Guardian's Statement of Income or DSWD Number &nbsp &nbsp</label></td>
                        <td><input type="file" name="income_cert" accept=".pdf,.jpg,.jpeg,.png" required /></td>
                    </tr>
                    <tr>
                        <td><label class="required-label">PSA/NSO Certificate</label></td>
                        <td><input type="file" name="psa_cert" accept=".pdf,.jpg,.jpeg,.png" required /></td>
                    </tr>
                </table>
            </div>

            <!-- Applicant Type Section -->
            <div class="document-section">
                <h3>Applicant Type</h3>
                <select name="applicant_type" id="applicant_type" class="form-control" required>
                    <option value="shs" selected>Senior High School Student/Graduate</option>
                    <option value="transferee">Transferee</option>
                    <option value="old_curriculum">Non-Senior High School Graduate</option>
                    <option value="als">ALS Passer</option>
                    <option value="pwd">Person with Disability</option>
                    <option value="ip">Indigenous People</option>
                    <option value="employee_dependent">SLSU Employee Dependent</option>
                    <option value="sports_winner">Sports/Culture/Arts Winner</option>
                    <option value="solo_parent">Solo Parent</option>
                </select>

                <!-- Conditional Document Sections -->
                <div id="conditional_docs" class="mt-3">
                    <!-- SHS Documents -->
                    <div class="conditional-section" id="shs_docs">
                        <h4>Senior High School Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Grade 11 Records</label></td>
                                <td><input type="file" name="grade11_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                            <tr>
                                <td><label>Grade 12 Certificate</label></td>
                                <td><input type="file" name="grade12_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Transferee Documents -->
                    <div class="conditional-section" id="transferee_docs" style="display: none;">
                        <h4>Transferee Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Transcript of Records</label></td>
                                <td><input type="file" name="tor" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Old Curriculum Documents -->
                    <div class="conditional-section" id="old_curriculum_docs" style="display: none;">
                        <h4>Non-Senior High School Graduate Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Form 137</label></td>
                                <td><input type="file" name="form137" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- ALS Documents -->
                    <div class="conditional-section" id="als_docs" style="display: none;">
                        <h4>ALS Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Certificate of Rating (Signed)</label></td>
                                <td><input type="file" name="als_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                            <tr>
                                <td><label>Certificate of Eligibility for Tertiary Level</label></td>
                                <td><input type="file" name="eligibility_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- PWD Documents -->
                    <div class="conditional-section" id="pwd_docs" style="display: none;">
                        <h4>PWD Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>PWD ID</label></td>
                                <td><input type="file" name="pwd_id" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Indigenous People Documents -->
                    <div class="conditional-section" id="ip_docs" style="display: none;">
                        <h4>Indigenous People Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Indigenous People ID</label></td>
                                <td><input type="file" name="ip_id" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Employee Dependent Documents -->
                    <div class="conditional-section" id="employee_dependent_docs" style="display: none;">
                        <h4>SLSU Employee Dependent Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>SLSU Certificate of Employment (Signed by HRMD)</label></td>
                                <td><input type="file" name="employment_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Sports/Culture/Arts Winner Documents -->
                    <div class="conditional-section" id="sports_winner_docs" style="display: none;">
                        <h4>Sports/Culture/Arts Winner Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Certificate of Winning (Signed by School Principal)</label></td>
                                <td><input type="file" name="award_cert" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Solo Parent Documents -->
                    <div class="conditional-section" id="solo_parent_docs" style="display: none;">
                        <h4>Solo Parent Documents</h4>
                        <table width="100%">
                            <tr>
                                <td width="50%"><label>Solo Parent ID</label></td>
                                <td><input type="file" name="solo_parent_id" accept=".pdf,.jpg,.jpeg,.png" /></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="text-center mt-4">
                <input type="submit" name="Register" class="login_btn" value="Submit Registration" />
                <input type="reset" class="reset_btn" value="Reset Form" />
            </div>
        </form>
    </div>

    <!-- JavaScript for handling conditional sections -->
    <script>
        document.getElementById('applicant_type').addEventListener('change', function() {
            // Hide all conditional sections
            document.querySelectorAll('.conditional-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show relevant section based on selection
            const selectedType = this.value;
            const section = document.getElementById(selectedType + '_docs');
            if (section) {
                section.style.display = 'block';
            }
        });
    </script>

    <!-- Add this JavaScript code before the closing </body> tag -->
    <script>
            // Password validation
            document.querySelector("form").addEventListener("submit", function(event) {
                var pass1 = document.getElementById("pass1").value;
                var pass2 = document.getElementById("pass2").value;
                
                if (pass1 != pass2) {
                    alert("Passwords do not match!");
                    event.preventDefault();
                    return false;
                }
                
                // Password strength validation
                var passwordRegex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
                if (!passwordRegex.test(pass1)) {
                    alert("Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long!");
                    event.preventDefault();
                    return false;
                }
                
                return true;
            });

            // Show/hide password feature
            function togglePassword(inputId) {
                var input = document.getElementById(inputId);
                input.type = input.type === "password" ? "text" : "password";
            }
            </script>

    <?php require_once('footer.php'); ?>
</body>

</html>