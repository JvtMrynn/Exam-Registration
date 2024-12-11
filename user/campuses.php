<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once('../connection.php');

    // Process form submission
    if (isset($_POST['Submit'])) {
        // Get selected campus ID
        $selectedCampusId = $_POST['campus'] ?? '';  // Default to empty if nothing is selected

        // Check if user has already selected a campus
        $checkCampusQuery = "SELECT * FROM campus_registration WHERE usn = ?";
        $checkStmt = mysqli_prepare($con, $checkCampusQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $user);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);

        // If a campus is already selected, deny further submission
        if (mysqli_num_rows($checkResult) > 0) {
            $exec1 = "<div class='alert alert-danger'>You have already selected a campus. You cannot select again.</div>";
        } else {
            // Proceed if no campus has been selected yet
            if ((int)$selectedCampusId != 0) {
                // Retrieve campus details using the ID from the database
                $query = "SELECT campus_name, address FROM campuses WHERE id = ?";
                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "i", $selectedCampusId); // Binding as integer for the id
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($row = mysqli_fetch_assoc($result)) {
                    $campusName = $row['campus_name'];  // Corrected column name
                    $campusAddress = $row['address'];   // Corrected column name

                    // Check if campus address is not null or empty
                    if (empty($campusAddress)) {
                        $exec1 = "<div class='alert alert-danger'>Campus address is missing!</div>";
                    } else {
                        // Insert the selected campus details into the database
                        $query1 = "INSERT INTO campus_registration (usn, campus_name, campus_address) VALUES (?, ?, ?)";
                        $stmt1 = mysqli_prepare($con, $query1);
                        mysqli_stmt_bind_param($stmt1, "sss", $user, $campusName, $campusAddress);

                        if (mysqli_stmt_execute($stmt1)) {
                            $exec1 = "<div class='alert alert-success'>Your Campus Selection Has Been Recorded!</div>";
                        } else {
                            $exec1 = "<div class='alert alert-danger'>Error in Recording Campus: " . mysqli_error($con) . "</div>";
                        }
                        mysqli_stmt_close($stmt1);
                    }
                } else {
                    $exec1 = "<div class='alert alert-warning'>Selected campus not found!</div>";
                }

                mysqli_stmt_close($stmt);
            } else {
                $exec1 = "<div class='alert alert-warning'>Please select a campus.</div>";
            }
        }
        mysqli_stmt_close($checkStmt);
    }

    // Fetch available campuses
    $q = mysqli_query($con, "SELECT * FROM campuses");
    $rr = mysqli_num_rows($q);
    if (!$rr) {
        echo "<h2 class='text-danger font-weight-bold'>No Campuses Listed/Added Yet.</h2>";
    } else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU | Campuses</title>
    <style>
        .forms {
            width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
        }

        .myh1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #060e4d;
            font-family: 'PT Serif', serif;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background-color: #060e4d;
            color: white;
            padding: 1rem;
            font-family: 'Bitter', serif;
            font-size: 14px;
        }

        table td {
            padding: 0.75rem;
            border-bottom: 1px solid #ddd;
            font-family: 'Bitter', serif;
            font-size: 14px;
        }

        .btn {
            padding: 0.5rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin: 1rem;
            transition: background-color 0.3s;
        }

        .login_btn {
            background-color: #060e4d;
            color: white;
        }

        .login_btn:hover {
            background-color: #0a1875;
        }

        .reset_btn {
            background-color: #dc3545;
            color: white;
        }

        .reset_btn:hover {
            background-color: #c82333;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            text-align: center;  /* This centers the text inside the alert */
            margin-left: auto;   /* This centers the alert box */
            margin-right: auto;  /* This centers the alert box */
            width: 80%;  /* You can adjust the width of the alert as needed */
        }


        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .geekmark {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1 class="myh1">SLSU Campuses</h1>
    
    <?php if (isset($exec1)) echo $exec1; ?>
    
    <div class="forms">
        <form method="POST">
            <table>
                <tr>
                    <th>Select</th>
                    <th>Campus Name</th>
                    <th>Campus Address</th>
                </tr>
                <?php
                while ($row = mysqli_fetch_array($q)) {
                    echo "<tr>";
                    // The radio button value is set to the campus ID
                    echo "<td><input type='radio' name='campus' value='" . htmlspecialchars($row['id']) . "' /></td>";
                    echo "<td>" . htmlspecialchars($row['campus_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "</tr>";
                }
                ?>
                <tr>
                    <td colspan="3" style="text-align: center">
                        <input class="btn login_btn" type="submit" name="Submit" value="Submit">
                        <input class="btn reset_btn" type="reset" value="Reset">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
<?php
}
?>
