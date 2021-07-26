<?php

$lib = __DIR__.'/lab/lib';

require $lib . '/Parsedown.php';
require $lib . '/ParsedownExtra.php';
require 'ParsedownExtended.php';

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta content="width=device-width" name="viewport">
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" async href="/lab/lib/css/fontawesome.min.css">
    <link rel="stylesheet" async href="/lab/lib/css/all.min.css">
    <link rel="stylesheet" async href="/lab/lib/css/reset.css">
    <link rel="stylesheet" async href="/lab/lib/css/main.css">
    <link rel="stylesheet" async href="/lab/lib/css/markdown.css">
    <link rel="stylesheet" async href="/lab/lib/css/diff.css">
    <link rel="stylesheet" async href="/lab/lib/css/grid.css">

    <!-- Katex -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.12.0/dist/katex.min.css" integrity="sha384-AfEj0r4/OFrOo5t7NnNe46zW/tFgW6x/bCJG8FqQCEo3+Aro6EYUG4+cU+KJWu/X" crossorigin="anonymous">

    <!-- ChartJS -->
    <link rel="stylesheet" href="/lab/lib/css/Chart.min.css">

</head>
<body>
    <nav>
        <a href="/"><span class="fa fa-home"></span></a>
        <a href="/lab/benchmarks.php">Benchmarks</a>
        <a href="/lab/test.php">Tests</a>
        <a href="/lab/execute.php">Execute</a>
    </nav>
</body>
</html>
