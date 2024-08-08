<!DOCTYPE html>
<!-- KaTeX requires the use of the HTML5 doctype. Without it, KaTeX may not render properly -->
<html>
  <head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" integrity="sha384-nB0miv6/jRmo5UMMR1wu3Gz6NLsoTkbqJghGIsx//Rlm+ZU03BU6SQNC66uf4l5+" crossorigin="anonymous">

    <!-- The loading of KaTeX is deferred to speed up page rendering -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" integrity="sha384-7zkQWkzuo3B5mTepMUcHkMB5jZaolc2xDwL6VFqjFALcbeS9Ggm/Yr2r3Dy4lfFg" crossorigin="anonymous"></script>

    <!-- To automatically render math in text elements, include the auto-render extension: -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" integrity="sha384-43gviWU0YVjaDtb/GhzOouOXtZMP/7XUzwPTstBeZFe/+rCMvRwr4yROQP43s0Xk" crossorigin="anonymous"
        onload="renderMathInElement(document.body);"></script>
  </head>
  <body>
  <?php
  # autoload
  require_once __DIR__ . '/vendor/autoload.php';

  # ParsedownExtended
  use BenjaminHoegh\ParsedownExtended\ParsedownExtended;

  # ParsedownExtended instance
  $ParsedownExtended = new ParsedownExtended();

  # Set safe mode
  $ParsedownExtended->setSafeMode(true);


  # Enable comments
  $ParsedownExtended->config()->set('headings.allowed_levels', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
  # Enable abbreviations;
  $ParsedownExtended->config()->set('abbreviations', true);

  # Enable Transliterate
  $ParsedownExtended->config()->set('headings.auto_anchors.transliterate', true);

  # Enable math
  $ParsedownExtended->config()->set('math', true);

  # Enable superscript
  $ParsedownExtended->config()->set('emphasis.superscript', true);
  # Enable subscript
  $ParsedownExtended->config()->set('emphasis.subscript', true);

  # get file content
  $content = file_get_contents('test.md');

  # Test multiline markdown
  $html = $ParsedownExtended->text($content);

  echo $html;

  ?>
  </body>
</html>
