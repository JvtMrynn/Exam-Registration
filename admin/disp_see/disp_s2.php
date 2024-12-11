<?php
  require_once('../connection.php');
  $query1 = "SELECT * FROM registration WHERE approved = 1";
  $result = mysqli_query($con, $query1);
  $rr = mysqli_num_rows($result);
  if (!$rr) {
    echo "<h2 style='color:red;color:#ff0000;font-family:Acme;'>No Students is Approved Yet.</h2>";
  } else {

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

?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <title>Manage Users</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css" />

    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="admin.css">

    <script src="js/jquery.js"></script>
    <script lang="js/javascript" src="xlsx.full.min.js"></script>
    <script lang="js/javascript" src="FileSaver.min.js"></script>
    <script>
      function DeleteUser(id) {
        if (confirm("Are You Sure..? You Want To Delete this Subject...?")) {
          window.location.href = "delete_s1.php?id=" + id;
        }
      }
    </script>
    <style type="text/css">

        th, td {
        word-wrap: break-word;
        }
        
        table th {
            text-align: center;
            padding: 10px 20px 10px 20px;
        }
    </style>
  </head>

  <body>
    <h2 style="color:darkblue;font-family:'Acme';" class="page-header">Waiting for Approval</h2>

      <table border="1" while="100%">
        <tr style="background-color: #060e4d;color: white;">
          <th>ID</th>
          <th>Name</th>
          <th>Birth Date</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Applicant Type</th>
          <th>Required Documents</th>
          <th>Extra Documents</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_array($result)): ?>
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
      <?php
    }
      ?>
      </table>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>
    <script>
      $(document).ready(function() {
        $("#mytab1").DataTable();
      });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>

  </html>