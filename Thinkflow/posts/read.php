<?php
include 'config.php';

$result = $conn->query("SELECT * FROM posts");

while ($row = $result->fetch_assoc()) {
    echo "<h3>" . $row['title'] . "</h3>";
    echo "<p>" . $row['content'] . "</p>";
}
?>