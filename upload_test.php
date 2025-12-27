<?php
$dir = __DIR__ . '/storage';
echo 'PHP is running as: ' . get_current_user();
echo "<p>Storage dir: $dir</p>";
echo "<p>is_writable(storage)? " . (is_writable($dir) ? 'YES' : 'NO') . "</p>";

if (!empty($_FILES['file'])) {
    $target = $dir . '/test_' . basename($_FILES['file']['name']);
    echo "<p>tmp_name: " . htmlspecialchars($_FILES['file']['tmp_name']) . "</p>";
    echo "<p>target: " . htmlspecialchars($target) . "</p>";

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo "<p style='color:green'>OK, saved to: $target</p>";
    } else {
        echo "<p style='color:red'>move_uploaded_file FAILED</p>";
        var_dump(error_get_last());
    }
} else {
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit">Upload</button>
    </form>
    <?php
}
