<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
          integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>

</body>
</html>
<?php
ini_set('display_errors', "1");
require_once "database.php";

global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_Id'];
    $mealName = $_POST['mealName'];
    $totalProteins = floatval($_POST['total_proteins']);
    $totalCalories = floatval($_POST['total_calories']);
    $totalFats = floatval($_POST['total_fats']);
    $totalCarbo = floatval($_POST['total_carbo']);
    $totalSugars = floatval($_POST['total_sugars']);

    $currentDate = date("Y-m-d");

    // Verifica dacă există deja o intrare pentru utilizatorul curent în aceeași zi
    $sqlCheck = "SELECT * FROM meals WHERE userId = ? AND mealName = ?";
    $stmtCheck = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmtCheck, $sqlCheck)) {
        mysqli_stmt_bind_param($stmtCheck, "ss", $userId, $mealName);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);

        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            // Dacă există deja o intrare, faceți un UPDATE
            $sqlUpdate = "UPDATE meals SET proteins = ?, calories = ?, fats = ?, carbo = ?, sugars = ?, mealName = ? WHERE userId = ? AND mealName = ?";
            $stmtUpdate = mysqli_stmt_init($conn);

            if (mysqli_stmt_prepare($stmtUpdate, $sqlUpdate)) {
                mysqli_stmt_bind_param($stmtUpdate, "ssssssss", $totalProteins, $totalCalories, $totalFats, $totalCarbo, $totalSugars, $mealName, $userId, $mealName);
                mysqli_stmt_execute($stmtUpdate);
                echo "<div class='alert alert-success'>Meal was updated</div>";
            } else {
                die("Something went wrong");
            }
        } else {
            // Dacă nu există, faceți un INSERT
            $sqlInsert = "INSERT INTO meals (userId, date, proteins, calories, fats, carbo, sugars, mealName) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = mysqli_stmt_init($conn);

            if (mysqli_stmt_prepare($stmtInsert, $sqlInsert)) {
                mysqli_stmt_bind_param($stmtInsert, "ssssssss", $userId, $currentDate, $totalProteins, $totalCalories, $totalFats, $totalCarbo, $totalSugars, $mealName);
                mysqli_stmt_execute($stmtInsert);
                echo "<div class='alert alert-success'>Meal was added</div>";
            } else {
                die("Something went wrong");
            }
        }
    } else {
        die("Something went wrong");
    }

    $sqlSelectMeals = "SELECT * FROM meals WHERE userId = ?";
    $stmtSelectMeals = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmtSelectMeals, $sqlSelectMeals)) {
        mysqli_stmt_bind_param($stmtSelectMeals, "s", $userId);
        mysqli_stmt_execute($stmtSelectMeals);
        $result = mysqli_stmt_get_result($stmtSelectMeals);

        if (mysqli_num_rows($result) > 0) {
            echo "<table class='table table-dark'>";
            echo "<thead><tr><th scope='col'>Date</th><th scope='col'>Meal Name</th><th scope='col'>Proteins</th><th scope='col'>Calories</th><th scope='col'>Fats</th><th scope='col'>Carbo</th><th scope='col'>Sugars</th></tr></thead><br>";
            echo "<tbody>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td scope='row'>" . $row['date'] . "</td>";
                echo "<td>" . $row['mealName'] . "</td>";
                echo "<td>" . $row['proteins'] . "</td>";
                echo "<td>" . $row['calories'] . "</td>";
                echo "<td>" . $row['fats'] . "</td>";
                echo "<td>" . $row['carbo'] . "</td>";
                echo "<td>" . $row['sugars'] . "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
            echo "<button class='btn btn-success btn-go-back d-flex'>Go back</button>";
        } else {
            echo "<p>No meals found.</p>";
        }
    } else {
        die("Something went wrong");
    }

    mysqli_stmt_close($stmtSelectMeals);
    mysqli_close($conn);
}
?>
<script>
    document.querySelector('.btn-go-back').addEventListener('click', function () {
        window.location.href = 'index.php';
    });
</script>

