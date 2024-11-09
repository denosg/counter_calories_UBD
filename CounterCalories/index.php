<?php
global $conn;
ini_set('display_errors', 1);
session_start();

error_log("Session started.");

if (!isset($_SESSION["user"])) {
    error_log("User not logged in, redirecting to login.");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user.id"];
error_log("User ID retrieved from session: " . $userId);

require_once "database.php";

if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
} else {
    error_log("Database connection established.");
}
$sql = "SELECT gender, weight, height, date FROM user WHERE id = $userId ";
error_log("Executing SQL query: " . $sql);

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    error_log("User data retrieved: " . json_encode($row));

    $gender = $row['gender'];
    $weight = $row['weight'];
    $height = $row['height'];
    $date = $row['date'];

    $birthDate = new DateTime($date);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
    error_log("Age calculated: " . $age);

    $calories = calculateCalories($gender, $weight, $height, $age);
    error_log("Calories calculated: " . $calories);
} else {
    error_log("No data found for user ID: " . $userId);
    $calories = 0;
}

function calculateCalories($sex, $weight, $height, $age)
{
    if ($sex == 'male') {
        $calories = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
    } elseif ($sex == 'female') {
        $calories = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
    } else {
        $calories = 0;
    }
    error_log("Calories calculation inside function - sex: $sex, weight: $weight, height: $height, age: $age, calories: $calories");
    $GLOBALS['calories'] = $calories;
    return $calories;
}

function getMealsCalories($conn, $userId)
{
    $currentDate = date("Y-m-d");
    $todayTotalCalories = 0;

    // Query to get today's meals for the given user ID
    $sql = "SELECT SUM(calories) as total_calories FROM meals WHERE userId = ? AND date = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $userId, $currentDate);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $totalCalories);
        mysqli_stmt_fetch($stmt);
        $todayTotalCalories = $totalCalories ?: 0;
        mysqli_stmt_close($stmt);
    }

    return $todayTotalCalories;
}

$dailyTarget = 2000;

$todayTotalCalories = getMealsCalories($conn, $userId);
$progressPercentage = ($todayTotalCalories / $dailyTarget) * 100;
if ($progressPercentage > 100) {
    $progressPercentage = 100;
}
$progressPercentageDisplay = round($progressPercentage, 2);


// Ensure percentage doesn't exceed 100%
if ($progressPercentage > 100) {
    $progressPercentage = 100;
}

// Round to two decimal places for display purposes
$progressPercentageDisplay = round($progressPercentage, 2);

mysqli_close($conn);
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>User Dashboard</title>
</head>
<body>
<container class='d-flex justify-content-center align-items-center flex-column'>
    <div class="container-home d-flex justify-content-center align-items-center flex-column">
        <h1>Welcome To Counter Calories</h1><br>

        <?php if ($calories > 0): ?>
            <h2>Your calorie needs are: <?php echo round($calories); ?> calories</h2>
        <?php else: ?>
            <p>Error retrieving user data.</p>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-warningg">Logout</a>
    </div>
    <div class="progress-container">
        <div class="progress-bar" role="progressbar" style="width: <?php echo $progressPercentage; ?>%;"
             aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
            <?php echo $progressPercentageDisplay; ?>%
        </div>
    </div>
    <div class="progress-bar-labels d-flex justify-content-between">
        <span>0</span>
        <span><?php echo round($todayTotalCalories); ?> / <?php echo round($calories); ?>  calories</span>
    </div>
    <h1>Your Meals</h1>
    <iframe src="my_meals.php" style="width: 100%; height: 600px; border: none; display: block; margin: auto;"></iframe>

    <ul class='meal-list'>
        <li>
            <div class='meal mt-40 d-flex justify-content-center align-items-center flex-colum'>
                <div class='btn-search'>
                    <input type="text" class="food-search" placeholder="Search for food"><br>
                    <button class="food-search-button">Search</button>
                </div>
                <table class='nutritional-values'>
                    <tr class='nutritional-values-names'>
                        <td class='product-name-header'>Product</td>
                        <td class='product-grams-header'>Grams</td> <!-- Added grams column -->
                        <td class='product-calories-header'>Calories</td>
                        <td class='product-proteins-header'>Proteins</td>
                        <td class='product-fats-header'>Fats</td>
                        <td class='product-carbo-header'>Carbo</td>
                        <td class='product-sugars-header'>Sugars</td>
                    </tr>
                    <tr class='total-nutritional-values d-none'>
                        <td>Total</td>
                        <td class='total-grams'></td>
                        <td class='total-calories'></td>
                        <td class='total-proteins'></td>
                        <td class='total-fats'></td>
                        <td class='total-carbo'></td>
                        <td class='total-sugars'></td>
                    </tr>
                </table>
            </div>
        </li>
    </ul>
    <div class='d-flex justify-content-center align-items-center btns'>
        <button type='submit' name='btn-save' class='btn btn-successs save-meal'>Save</button>
    </div>
</container>
<form id="saveMealForm" action="save_meal.php" method="post" style="display: none;">
    <input type='hidden' class='user_id' value='<?php echo $userId ?>' name='user_Id'>
    <input type="hidden" id="mealName" name="mealName">
    <input type="hidden" id="totalCaloriesInput" name="total_calories">
    <input type="hidden" id="totalProteinsInput" name="total_proteins">
    <input type="hidden" id="totalFatsInput" name="total_fats">
    <input type="hidden" id="totalCarboInput" name="total_carbo">
    <input type="hidden" id="totalSugarsInput" name="total_sugars">
</form>
</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchFoodInput = document.querySelector('.food-search');
        const searchButton = document.querySelector('.food-search-button');
        const saveMealButton = document.querySelector('.save-meal');

        function performSearch() {
            $.ajax({
                method: 'POST',
                url: 'https://trackapi.nutritionix.com/v2/natural/nutrients',
                headers: {
                    'x-app-id': '16ab14ca',
                    'x-app-key': '5bc7a8aead73acd8440f3ffa80f10e7a'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    'query': searchFoodInput.value
                }),
                success: function (result) {
                    console.log(result);
                    const foodName = result.foods[0].food_name;
                    addProduct(
                        foodName,
                        result.foods[0].serving_weight_grams,
                        result.foods[0].nf_calories,
                        result.foods[0].nf_protein,
                        result.foods[0].nf_total_fat,
                        result.foods[0].nf_total_carbohydrate,
                        result.foods[0].nf_sugars ?? 10
                    );

                    // Set meal name to the searched food item
                    document.getElementById('mealName').value = foodName;
                },
                error: function ajaxError(jqXHR) {
                    console.error('Error: ', jqXHR.responseText);
                }
            });

        }

        searchFoodInput.addEventListener("keydown", function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        searchButton.addEventListener("click", function (e) {
            e.preventDefault();
            performSearch();
        });

        saveMealButton.addEventListener('click', function (e) {
            e.preventDefault();
            saveMeal();
        });

    });

    function addProduct(name, grams, calories, proteins, fats, carbo, sugars) {

        var newProductRow = document.createElement('tr');
        newProductRow.className = 'product';

        var nameCellProduct = document.createElement('td');
        nameCellProduct.className = 'product-name';
        nameCellProduct.textContent = name;
        newProductRow.appendChild(nameCellProduct);

        var gramsCellProduct = document.createElement('td');
        gramsCellProduct.className = 'product-grams';
        gramsCellProduct.textContent = grams; // Added grams data here
        newProductRow.appendChild(gramsCellProduct);

        var caloriesCellProduct = document.createElement('td');
        caloriesCellProduct.className = 'product-calories';
        caloriesCellProduct.textContent = calories;
        newProductRow.appendChild(caloriesCellProduct);

        var proteinsCellProduct = document.createElement('td');
        proteinsCellProduct.className = 'product-proteins';
        proteinsCellProduct.textContent = proteins;
        newProductRow.appendChild(proteinsCellProduct);

        var fatsCellProduct = document.createElement('td');
        fatsCellProduct.className = 'product-fats';
        fatsCellProduct.textContent = fats;
        newProductRow.appendChild(fatsCellProduct);

        var carboCellProduct = document.createElement('td');
        carboCellProduct.className = 'product-carbo';
        carboCellProduct.textContent = carbo;
        newProductRow.appendChild(carboCellProduct);

        var sugarsCellProduct = document.createElement('td');
        sugarsCellProduct.className = 'product-sugars';
        sugarsCellProduct.textContent = sugars;
        newProductRow.appendChild(sugarsCellProduct);

        var nutritionalValuesTable = document.querySelector('.nutritional-values');
        var totalRow = document.querySelector('.total-nutritional-values');

        if (totalRow) {
            totalRow.insertAdjacentHTML('beforebegin', newProductRow.outerHTML);
        } else {
            nutritionalValuesTable.appendChild(newProductRow);
        }

        updateTotals();
    }


    function updateTotals() {
        const rows = document.querySelectorAll('.product');
        const totalRow = document.querySelector('.total-nutritional-values');

        totalRow.classList.remove('d-none');

        if (totalRow) {
            let totalGrams = 0,
                totalCalories = 0,
                totalProteins = 0,
                totalFats = 0,
                totalCarbo = 0,
                totalSugars = 0;

            rows.forEach(function (row) {
                const grams = parseFloat(row.querySelector('.product-grams').textContent) || 0;
                const calories = parseFloat(row.querySelector('.product-calories').textContent) || 0;
                const proteins = parseFloat(row.querySelector('.product-proteins').textContent) || 0;
                const fats = parseFloat(row.querySelector('.product-fats').textContent) || 0;
                const carbo = parseFloat(row.querySelector('.product-carbo').textContent) || 0;
                const sugars = parseFloat(row.querySelector('.product-sugars').textContent) || 0;

                totalGrams += grams;
                totalCalories += calories;
                totalProteins += proteins;
                totalFats += fats;
                totalCarbo += carbo;
                totalSugars += sugars;
            });

            const gramsCell = totalRow.querySelector('.total-grams');
            const totalCaloriesCell = totalRow.querySelector('.total-calories');
            const totalProteinsCell = totalRow.querySelector('.total-proteins');
            const totalFatsCell = totalRow.querySelector('.total-fats');
            const totalCarboCell = totalRow.querySelector('.total-carbo');
            const totalSugarsCell = totalRow.querySelector('.total-sugars');

            if (gramsCell) gramsCell.textContent = totalGrams.toFixed(2);
            if (totalCaloriesCell) totalCaloriesCell.textContent = totalCalories.toFixed(2);
            if (totalProteinsCell) totalProteinsCell.textContent = totalProteins.toFixed(2);
            if (totalFatsCell) totalFatsCell.textContent = totalFats.toFixed(2);
            if (totalCarboCell) totalCarboCell.textContent = totalCarbo.toFixed(2);
            if (totalSugarsCell) totalSugarsCell.textContent = totalSugars.toFixed(2);
        }
        updateProgressBar();
    }

    function updateProgressBar() {
        const progressBar = document.querySelector('.progress-bar');
        const totalCaloriesCell = document.querySelector('.total-calories');
        const consumedCalories = parseFloat(totalCaloriesCell.textContent) || 0;
        const progressPercentage = (consumedCalories / <?php echo $calories; ?>) * 100;

        progressBar.value = progressPercentage;
        progressBar.innerHTML = progressPercentage.toFixed(2) + '%';
    }

    function saveMeal() {
        const totalCalories = parseFloat(document.querySelector('.total-calories').textContent) || 0;
        const totalProteins = parseFloat(document.querySelector('.total-proteins').textContent) || 0;
        const totalFats = parseFloat(document.querySelector('.total-fats').textContent) || 0;
        const totalCarbo = parseFloat(document.querySelector('.total-carbo').textContent) || 0;
        const totalSugars = parseFloat(document.querySelector('.total-sugars').textContent) || 0;
        const mealName = document.querySelector('.food-search').value.trim(); // Use the search input value

        if (!mealName) {
            alert("Please search for and select a food item first.");
            return; // Stop if meal name is empty
        }

        document.getElementById('mealName').value = mealName;
        document.getElementById('totalCaloriesInput').value = totalCalories;
        document.getElementById('totalProteinsInput').value = totalProteins;
        document.getElementById('totalFatsInput').value = totalFats;
        document.getElementById('totalCarboInput').value = totalCarbo;
        document.getElementById('totalSugarsInput').value = totalSugars;

        // Submit the form after setting the values
        document.getElementById('saveMealForm').submit();
    }

</script>

<style>
    .progress-container {
        width: 30%;
        margin: 20px 0;
        background-color: #d3d3d3; /* Light grey background for the progress container */
        border-radius: 5px;
    }

    .progress-bar {
        color: #fff;
        height: 30px;
        background-color: #4caf50; /* Green color for the filled portion */
        text-align: center;
        line-height: 30px;
        border-radius: 5px;
    }

    .progress-bar-labels {
        font-weight: bold;
        margin-top: 5px;
    }
</style>