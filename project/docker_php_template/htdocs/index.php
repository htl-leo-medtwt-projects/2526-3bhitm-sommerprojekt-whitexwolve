<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Docker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 1vw;
            background-color: #000;
            color: #fff;
            font-size: 120%;
        }
        li {
            padding: 0.2vw;
        }
        a {
            color: aqua;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php
    function listDirectory($dir) {
        $directories = [];
        $files = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..') {
                    $path = $dir . '/' . $entry;
                    if (is_dir($path)) {
                        $directories[] = $entry;
                    } else {
                        $files[] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        sort($directories);
        sort($files);

        echo '<ul>';
        foreach ($directories as $directory) {
            $path = $dir . '/' . $directory;
            echo "<li><strong>[DIR]</strong> <a href='?dir=$path'>$directory</a></li>";
        }
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            echo "<li><a href='$path'>$file</a></li>";
        }
        echo '</ul>';
    }

    $currentDir = isset($_GET['dir']) ? $_GET['dir'] : '.';
    echo "<h2>Index of <span style='font-size: 90%;'>$currentDir</span></h2>";
    listDirectory($currentDir);
    ?>

</body>
</html>