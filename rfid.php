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

    // Check if the RFID exists in the registrants table
    $sql = "SELECT status FROM registrants WHERE rfid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // RFID found, get the current status
        $stmt->bind_result($status);
        $stmt->fetch();

        // Toggle status between 0 and 1
        $newStatus = ($status == 0) ? 1 : 0;

        // Update status in the registrants table
        $updateSql = "UPDATE registrants SET status = ? WHERE rfid = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("is", $newStatus, $rfid);
        if ($updateStmt->execute()) {
            // Insert a new entry in the entries table
            $insertSql = "INSERT INTO entries (rfid, status) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("si", $rfid, $newStatus);
            if ($insertStmt->execute()) {
                $statusMessage = "RFID tapped successfully. Status updated to: $newStatus";
            } else {
                $statusMessage = "Error inserting entry: " . $insertStmt->error;
            }
            $insertStmt->close();
        } else {
            $statusMessage = "Error updating registrant status: " . $updateStmt->error;
        }
        $updateStmt->close();
    } else {
        // RFID not found in the registrants table
        $statusMessage = "RFID not found in the registrants table";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();

// Return status message as JSON (for easier frontend consumption)
echo json_encode(["message" => $statusMessage]);
?>
