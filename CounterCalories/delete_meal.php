<?php
require_once "database.php";
session_start();

global $conn;

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// Check if meal ID is provided
if (isset($_POST["meal_id"])) {
    $mealId = $_POST["meal_id"];
    $userId = $_SESSION["user.id"];  // Get logged-in user ID

    // Prepare and execute the delete statement
    $sql = "DELETE FROM meals WHERE id = ? AND userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $mealId, $userId);

    if ($stmt->execute()) {
        echo "Meal deleted successfully";
    } else {
        echo "Error deleting meal";
    }

    $stmt->close();
} else {
    echo "No meal ID provided";
}

$conn->close();
?>
