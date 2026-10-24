
<?php
$file = "users.txt";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $action = $_POST["action"];

    $users = file_exists($file)
        ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        : [];

    if ($action == "register") {
        $exists = false;

        foreach ($users as $user) {
            $data = explode(":", $user);
            if ($data[0] == $username) {
                $exists = true;
                break;
            }
        }

        if ($username == "" || $password == "") {
            $message = "Please fill all fields.";
        } elseif (str_contains($username, ":") || str_contains($username, "\n")) {
            $message = "Invalid username.";
        } elseif ($exists) {
            $message = "Username already exists.";
        } else {
            $salt = bin2hex(random_bytes(16));
            $hash = hash("sha256", $password . $salt);

            file_put_contents(
                $file,
                "$username:$salt:$hash\n",
                FILE_APPEND | LOCK_EX
            );

            $message = "Registration successful!";
        }
    }

    if ($action == "login") {
        $message = "Invalid username or password.";

        foreach ($users as $user) {
            $data = explode(":", $user);

            if (count($data) == 3 && $data[0] == $username) {
                $hash = hash("sha256", $password . $data[1]);

                if (hash_equals($data[2], $hash)) {
                    $message = "Login successful!";
                }
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Authentication</title>
</head>
<body>
    <h2>User Authentication System</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>

        <button type="submit" name="action" value="register">
            Register
        </button>

        <button type="submit" name="action" value="login">
            Login
        </button>
    </form>

    <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>
