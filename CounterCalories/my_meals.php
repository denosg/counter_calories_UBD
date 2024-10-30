<?php
global $conn;
session_start();
require_once "database.php";

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user.id"];

// Retrieve meals from the database for the logged-in user
$sql = "SELECT * FROM meals WHERE userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$meals = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $meals[] = $row;
    }
} else {
    error_log("No meals found for user ID: " . $userId);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
          integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>My Meals</title>
</head>
<body>
<container class='d-flex justify-content-center align-items-center flex-column'>
    <h1>Your Meals</h1>

    <div class="container mt-4">
        <?php if (count($meals) > 0): ?>
            <ul class="list-group">
                <?php foreach ($meals as $meal): ?>
                    <li class="list-group-item">
                        <h5>Meal ID: <?php echo $meal['id']; ?></h5>
                        <p><strong>Meal Name:</strong> <?php echo $meal['mealName']; ?></p>
                        <p><strong>Calories:</strong> <?php echo $meal['calories']; ?> kcal</p>
                        <p><strong>Proteins:</strong> <?php echo $meal['proteins']; ?> g</p>
                        <p><strong>Fats:</strong> <?php echo $meal['fats']; ?> g</p>
                        <p><strong>Carbohydrates:</strong> <?php echo $meal['carbo']; ?> g</p>
                        <p><strong>Sugars:</strong> <?php echo $meal['sugars']; ?> g</p>
                        <p><strong>Date:</strong> <?php echo $meal['date']; ?></p>
                        <button class="btn btn-danger delete-meal-btn" data-meal-id="<?php echo $meal['id']; ?>">
                            Delete
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No meals found.</p>
        <?php endif; ?>
    </div>
</container>
</body>
</html>

<script>
    document.querySelectorAll('.delete-meal-btn').forEach(button => {
        button.addEventListener('click', function () {
            const mealId = this.getAttribute('data-meal-id');
            if (confirm("Are you sure you want to delete this meal?")) {
                fetch('delete_meal.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `meal_id=${mealId}`
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes("successfully")) {
                            this.closest('.list-group-item').remove();
                        } else {
                            alert("Failed to delete meal. Please try again.");
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    });
</script>
