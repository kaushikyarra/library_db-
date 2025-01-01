<?php
session_start();

// Ensure the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php'); // Redirect to login page if not logged in or not admin
    exit();
}

require_once 'config.php';

// Fetch all users and their borrowed books
$stmt = $conn->prepare("
    SELECT u.username, b.title, br.borrow_date, br.return_date
    FROM borrow_records br
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.book_id
    ORDER BY u.username, br.borrow_date DESC
");
$stmt->execute();
$users_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Users and Borrowed Books</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Users and Their Borrowed Books</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
            </tr>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['title']); ?></td>
                    <td><?php echo htmlspecialchars($user['borrow_date']); ?></td>
                    <td><?php echo $user['return_date'] ? htmlspecialchars($user['return_date']) : 'Not Returned Yet'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
