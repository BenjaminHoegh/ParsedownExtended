<!-- ![ParsedownExtended](docs/img/parsedownExtended.png) -->
<p align="center"><img alt="ParsedownExtended" src="docs/img/parsedownExtended.png" height="330" /></p>

# Parsedown Extended
![Release](	https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtended.svg?style=flat-square) ![License](https://img.shields.io/github/license/BenjaminHoegh/ParsedownExtended?style=flat-square)

Parsedown Extended is a extension to [Parsedown](https://github.com/erusev/parsedown) to add even more functions to the library. It also work with [ParsedownExtra](https://github.com/erusev/parsedown-extra)

### Extentions included in ParsedownExtended

- [ParsedownMath](https://github.com/BenjaminHoegh/ParsedownMath)
- [ParsedownToc](https://github.com/BenjaminHoegh/parsedownToc)


---

### Installation

1. Download the "Source code" from the [latest release](https://github.com/BenjaminHoegh/ParsedownExtended/releases/latest)
2. You must include `parsedown.php` or `parsedownExtra.php` too.
3. Include `ParsedownExtended.php`

> **Important:** Parsedown and ParsedownExtra don't work with PHP 7.4+ at the moment and it will throw some errors on ParsedownExtended. Use PHP 7.3 until Parsedown have been updated


##### Example

```php
$ParsedownExtended = new ParsedownExtended();

echo $ParsedownExtended->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtended->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```

---


### Added features

#### Table of contents

- **Inline Example:**

  **PHP**
  ```php
  $contents = file_get_contents('example.md');

  $Parsedown = new ParsedownExtended([
    "toc" => [
      "enable" => true,
      "inline" => true,
    ]
  ]);

  echo $Parsedown->text($contents);
  ```

  **Markdown**
  ```markdown
  [toc]
  ```

- **Outside Markdown Example:**

  **PHP**
  ```php
  $contents = file_get_contents('example.md');

  $Parsedown = new ParsedownExtended([
    "toc" => [
      "enable" => true
    ]
  ]);

  echo $Parsedown->text($contents);
  ```

  Then use where you want to execute the TOC
  ```
  echo $Parsedown->toc($contents);
  ```


#### Typography

- **Insert and Mark**

  ```php
  $Parsedown = new ParsedownExtended([
      "mark" => true,
      "insert" => true,
  ]);
  ```

  | Type                | To get            |
  | ------------------- | ----------------- |
  | \==Mark\==          | \<mark>Mark\</mark>          |
  | \++Insert\++        | \<ins>Insert\</ins>        |

- **Auto replace**

  ```php
  $Parsedown = new ParsedownExtended([
      "smartTypography" => true,
  ]);
  ```

  | Type        | Or    | Get      |
  | ----------- | ----- | -------- |
  | \(c)        | \(C)  | &copy;   |
  | \(r)        | \(R)  | &reg;    |
  | \(tm)       | \(TM) | &trade;  |
  | \...        |       | &hellip; |
  | \--         |       | &ndash;  |
  | -\--        |       | &mdash;  |
  | \>>         |       | &raquo;  |
  | \<<         |       | &laquo;  |

- **Subscript and superscript**

  ```php
  $Parsedown = new ParsedownExtended([
      "scripts" => true,
  ]);
  ```

  Superscript
  ```
  19^th^
  ```

  Subscript:
  ```
  H~2~O
  ```

- **KBD**

  ```php
  $Parsedown = new ParsedownExtended([
      "kbd" => true,
  ]);
  ```

  ```
  Press [[Shift]] + [[Alt]] + [[G]] to open
  ```

  Press <kbd>Shift</kbd> + <kbd>Alt</kbd> + <kbd>G</kbd> to open

#### Task

```php
$Parsedown = new ParsedownExtended([
    "task" => true,
]);
```


```markdown
- [ ] ToDos
- [x] Buy some salad
- [ ] Brush teeth
- [x] Drink some water
```

- [ ] ToDos
  - [x] Buy some salad
  - [ ] Brush teeth
  - [x] Drink some water


#### LaTeX
LaTeX syntax support for both [MathJax](https://www.mathjax.org) and [KaTeX](https://katex.org) by using [MathJax standard](https://docs.mathjax.org/en/latest/basic/mathematics.html) delimiters `$$...$$`, `\\[...\\]` and `\\(...\\)`


```php
$Parsedown = new ParsedownExtended([
    "math" => true,
]);
```


- **Inline Example**
  ```
  This is some \(ax^2 + bx + c = 0\) inline LaTeX
  ```


- **Block Example**
  ```
  \[
      x = {-b \pm \sqrt{b^2-4ac} \over 2a}.
  \]
  ```

  ```
  $$
      x = {-b \pm \sqrt{b^2-4ac} \over 2a}.
  $$
  ```

#### Diagrams
Support for [Mermaid](https://mermaid-js.github.io/mermaid/#/) and [ChartJS](https://www.chartjs.org).

```php
$Parsedown = new ParsedownExtended([
    "diagrams" => true,
]);
```

- **Mermaid Example**

      ```mermaid
      sequenceDiagram
          participant Alice
          participant Bob
          Alice->>John: Hello John, how are you?
          loop Healthcheck
              John->>John: Fight against hypochondria
          end
          Note right of John: Rational thoughts<br/>prevail...
          John-->>Alice: Great!
          John->>Bob: How about you?
          Bob-->>John: Jolly good!
      ```

- **ChartJS Example**

      ```chart
      {
        "type": "line",
        "data": {
          "labels": [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July"
          ],
          "datasets": [
            {
              "label": "# of bugs",
              "fill": false,
              "lineTension": 0.1,
              "backgroundColor": "rgba(75,192,192,0.4)",
              "borderColor": "rgba(75,192,192,1)",
              "borderCapStyle": "butt",
              "borderDash": [],
              "borderDashOffset": 0,
              "borderJoinStyle": "miter",
              "pointBorderColor": "rgba(75,192,192,1)",
              "pointBackgroundColor": "#fff",
              "pointBorderWidth": 1,
              "pointHoverRadius": 5,
              "pointHoverBackgroundColor": "rgba(75,192,192,1)",
              "pointHoverBorderColor": "rgba(220,220,220,1)",
              "pointHoverBorderWidth": 2,
              "pointRadius": 1,
              "pointHitRadius": 10,
              "data": [
                65,
                59,
                80,
                81,
                56,
                55,
                40
              ],
              "spanGaps": false
            }
          ]
        },
        "options": {}
      }
      ```
