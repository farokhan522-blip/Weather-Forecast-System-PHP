<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        isset($_SESSION['user_id']) &&
        isset($data['latitude'], $data['longitude'], $data['city'], $data['country'], $data['forecast'])
    ) {
        $user_id = $_SESSION['user_id'];
        $lat = $data['latitude'];
        $lon = $data['longitude'];
        $city = $data['city'];
        $country = $data['country'];
        $desc = $data['forecast'];

        $conn = connect();
        echo "✅ Connected. ";

        // Step 1: Check or insert location
        $stmt = $conn->prepare("SELECT location_id FROM location WHERE latitude = ? AND longitude = ?");
        $stmt->bind_param("dd", $lat, $lon);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $location_id = $row['location_id'];
            echo "📍 Location exists. ID: $location_id. ";
        } else {
            $insertLoc = $conn->prepare("INSERT INTO location (city, country, latitude, longitude) VALUES (?, ?, ?, ?)");
            $insertLoc->bind_param("ssdd", $city, $country, $lat, $lon);
            if ($insertLoc->execute()) {
                $location_id = $insertLoc->insert_id;
                echo "🆕 Location added. ID: $location_id. ";
            } else {
                echo "❌ Location insert failed: " . $insertLoc->error;
                exit;
            }
            $insertLoc->close();
        }
        $stmt->close();

        // Step 2: Check if forecast already exists for user
        $check = $conn->prepare("SELECT forecast_id FROM forecast WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            // Forecast exists → update
            $update = $conn->prepare("UPDATE forecast SET location_id = ?, description = ?, last_checked = NOW() WHERE user_id = ?");
            $update->bind_param("isi", $location_id, $desc, $user_id);
            if ($update->execute()) {
                echo "🔁 Forecast updated.";
            } else {
                echo "❌ Forecast update failed: " . $update->error;
            }
            $update->close();
        } else {
            // Insert new forecast
            $today = date("Y-m-d");
            $insert = $conn->prepare("INSERT INTO forecast (user_id, location_id, forecast_date, description) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiss", $user_id, $location_id, $today, $desc);
            if ($insert->execute()) {
                echo "🌤️ Forecast inserted.";
            } else {
                echo "❌ Forecast insert failed: " . $insert->error;
            }
            $insert->close();
        }

        $check->close();
        $conn->close();
    } else {
        echo "❌ Missing input or session.";
    }
} else {
    echo "❌ Invalid request method.";
}