<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize response variables
$statusMessage = "No RFID data received";
$rfid = "";

// Get RFID data from GET request
if (isset($_GET['rfid'])) {
    $rfid = trim($_GET['rfid']); // Get the RFID value from query parameter

    // Check if RFID already exists in the registrants table
    $sql = "SELECT id FROM registrants WHERE rfid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // RFID already registered, do not insert again
        $statusMessage = "RFID Already Registered - No new record created";
    } else {
        // RFID not found, insert new registrant with status = 0
        $insertSql = "INSERT INTO registrants (rfid, status) VALUES (?, 0)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("s", $rfid);

        if ($insertStmt->execute()) {
            // Record inserted successfully
            $statusMessage = "New registrant created with RFID: $rfid";
        } else {
            // Error inserting record
            $statusMessage = "Error inserting registrant: " . $insertStmt->error;
        }

        $insertStmt->close();
    }

    $stmt->close();
}

// Close the database connection
$conn->close();

// Return status message as JSON (for easier frontend consumption)
echo json_encode(["message" => $statusMessage]);
?>
