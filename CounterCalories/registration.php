<?php
global $conn;
session_start();
if (isset($_SESSION["user"])) {
   header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php
        if (isset($_POST["submit"])) {
           $username = $_POST["username"];
           $email = $_POST["email"];
           $password = $_POST["password"];
           $confirmPassword = $_POST["confirm_password"];
           $height = $_POST["height"];
           $weight = $_POST["weight"];
           $date = $_POST["date"];
           $gender = $_POST["gender"];

           $passwordHash = password_hash($password, PASSWORD_DEFAULT);

           $errors = array();
           
           if (empty($username) OR empty($email) OR empty($password) OR empty($confirmPassword) OR empty($height) OR empty($weight) OR empty($date)) {
            array_push($errors,"All fields are required");
           }
           if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "Email is not valid");
           }
           if (strlen($password)<8) {
            array_push($errors,"Password must be at least 8 charactes long");
           }
           if ($password!==$confirmPassword) {
            array_push($errors,"Password does not match");
           }
           if ($height < 50 || $height > 250) {
            array_push($errors, "Invalid height");
           }
           if ($weight < 10 || $weight > 500) {
            array_push($errors, "Invalid weight");
           }
           if (empty($_POST["gender"])) {
            array_push($errors[] = "Gender is required.");
        }
           require_once "database.php";
           $sql = "SELECT * FROM user WHERE email = '$email'";
           $result = mysqli_query($conn, $sql);
           $rowCount = mysqli_num_rows($result);
           if ($rowCount>0) {
            array_push($errors,"Email already exists!");
           }
           if (count($errors)>0) {
            foreach ($errors as  $error) {
                echo "<div class='alert alert-danger'>$error</div>";
            }
           }else{
            
            $sql = "INSERT INTO user (username, email, password, height, weight, date, gender) VALUES ( ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_stmt_init($conn);
            $prepareStmt = mysqli_stmt_prepare($stmt,$sql);
            if ($prepareStmt) {
                mysqli_stmt_bind_param($stmt,"sssssss",$username, $email, $passwordHash, $height, $weight, $date, $gender);
                mysqli_stmt_execute($stmt);
                echo "<div class='alert alert-success'>You are registered successfully.</div>";
            }else{
                die("Something went wrong");
            }
           }
        }
        ?>
        <form action="registration.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username:">
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email:">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password:">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password:">
            </div>
            <div class="form-group">
                <input type="number" class="form-control" name="height" placeholder="Height in cm:">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="weight" placeholder="Weight in kg:">
            </div>
            <select class="form-control mb-30" name="gender" id="gender">
                <option value="" disabled selected>Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
            <div class="form-group">
                <input type="date" class="form-control" name="date">
            </div>
            <div class="form-btn">
                <input type="submit" class="btn btn-primary" value="Register" name="submit">
            </div>
        </form>
        <div>
        <div><p>Already Registered? <a href="login.php">Login Here</a></p></div>
      </div>
    </div>
</body>
</html>