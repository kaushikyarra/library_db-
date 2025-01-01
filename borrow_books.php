<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];

    // For admin, allow selecting a user to borrow
    if ($user_role == 'admin' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id']; // Admin selects user for borrowing
    }

    // Check if the book is available (additional check to ensure no negative copies)
    $stmt = $conn->prepare("SELECT copies_available FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    // Check if the book has available copies
    if ($book && $book['copies_available'] > 0) {
        // Insert borrow record into borrow_records table
        $stmt = $conn->prepare("INSERT INTO borrow_records (book_id, user_id, borrow_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $book_id, $user_id);
        
        if ($stmt->execute()) {
            // Update available copies in books table
            $stmt = $conn->prepare("UPDATE books SET copies_available = copies_available - 1 WHERE book_id = ?");
            $stmt->bind_param("i", $book_id);
            if ($stmt->execute()) {
                $message = "Book borrowed successfully!";
            } else {
                // Error in updating book availability
                $message = "Error updating book availability.";
            }
        } else {
            // Error in inserting borrow record
            $message = "Error borrowing the book.";
        }
    } else {
        $message = "No copies available.";
    }
}

// Fetch all users for admin selection
if ($user_role == 'admin') {
    $stmt = $conn->prepare("SELECT user_id, username FROM users");
    $stmt->execute();
    $users_result = $stmt->get_result();
}

// Fetch available books and their quantities
$stmt = $conn->prepare("SELECT book_id, title, copies_available FROM books WHERE copies_available > 0");
$stmt->execute();
$books_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Books</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
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
        .message {
            text-align: center;
            font-weight: bold;
            color: #007BFF;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        select, input {
            padding: 10px;
            margin: 15px 0;
            width: 80%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
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
        .back-btn {
            margin-top: 20px;
            background-color: #6c757d;
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Borrow a Book</h2>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($user_role == 'admin'): ?>
            <!-- Admin selects user to borrow a book -->
            <form method="POST" action="borrow_books.php">
                <select name="user_id" required>
                    <option value="">Select User</option>
                    <?php while ($row = $users_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['user_id']; ?>"><?php echo $row['username']; ?></option>
                    <?php endwhile; ?>
                </select><br>
        <?php endif; ?>

        <select name="book_id" required>
            <option value="">Select Book</option>
            <?php while ($book = $books_result->fetch_assoc()): ?>
                <option value="<?php echo $book['book_id']; ?>">
                    <?php echo $book['title']; ?> (<?php echo $book['copies_available']; ?> available)
                </option>
            <?php endwhile; ?>
        </select><br>
        <button type="submit">Borrow</button>
        </form>

        <h3>Available Books</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Available Copies</th>
            </tr>
            <?php
            // Re-fetch available books for display
            $stmt->execute();
            $books_result = $stmt->get_result();
            while ($book = $books_result->fetch_assoc()) {
                echo "<tr><td>" . $book['title'] . "</td><td>" . $book['copies_available'] . "</td></tr>";
            }
            ?>
        </table>

        <!-- Back to Dashboard Button -->
        <a href="dashboard.php">
            <button class="back-btn">Back to Dashboard</button>
        </a>
    </div>

</body>
</html>
