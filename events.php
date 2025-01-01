<?php
session_start();
require_once 'config.php';

// Fetch events from the database
$stmt = $conn->prepare("SELECT * FROM events ORDER BY date ASC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            padding: 20px;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .event {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .event h2 {
            margin: 0;
            color: #007bff;
        }
        .event p {
            margin: 5px 0;
            color: #333;
        }
        .event .date {
            font-weight: bold;
            color: #ff4500;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Upcoming Library Events</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($event = $result->fetch_assoc()): ?>
                <div class="event">
                    <h2><?php echo $event['title']; ?></h2>
                    <p class="date"><?php echo date("F j, Y", strtotime($event['date'])); ?></p>
                    <p><?php echo nl2br($event['description']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events found.</p>
        <?php endif; ?>

    </div>

</body>
</html>
