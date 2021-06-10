## Introduction

All of the configurations for **ParsedownExtended** are defined in `json` format. Each option is documented, so feel free to look through and get familiar with the options available to you.

These configurations allow you to configure things like your predefined abbreviation, heading permalink, as well as various other core configuration values.

By **default** any configuration where only a sub-configuration is determined will automatically turn on the function unless others are mentioned in the docs 

## Content
- [Abbreviation](#abbreviation)
    - [Predefined abbreviation](#predefined)
    - [Predefined only](#predefined-only)
- [Blockqoute](#blockqoutes)
- [Code](#code)
- [Definition list](#definition-list)
- [Email](#emails)
- [Emojis](#emojis)
- [Emphasis](#emphasis)
- [Footnotes](#footnotes)
- [Headings](#headings)
    - [Heading permalink](#heading-permalink)
    - [blacklist](#blacklist)
    - [Allowed](#allowed)
- [Thematic breaks](#horizontal-rule-thematic-breaks)
- [Images](#images)
- [Keystrokes](#keystrokes)
- [LaTeX](#la-te-x)
- [Links](#links)
- [Lists](#lists)
- [Marks](#mark)
- [Smartypants](#smartypants)
- [Strikethrough](#strikethrough)
- [Subscript](#subscript)
- [Superscript](#superscript)
- [Table of content](#table-of-content)
    - [Delimiter](#delimiter)
    - [Headings](#headings-1)
    - [Lowercase](#lowercase)
    - [Transliterate](#transliterate)
    - [Urlencode](#urlencode)
- [Tables](#tables)
- [Task](#task)
- [Diagrams](#diagrams)


### Abbreviation
To enable/disable abbreviations use the following:
```php
$Parsedown = new ParsedownExtended([
    'abbreviations' => true
]);
```

#### Predefined
Predefine abbreviations:
```php
$Parsedown = new ParsedownExtended([
    'abbreviations' => [
        'predefine' => [
            'CSS' => 'Cascading Style Sheet',
            'HTML' => 'Hyper Text Markup Language',
            'JS' => 'JavaScript'
        ]
    ]
]);
```

#### Predefined only
Disable user/custom abbreviations by using `allow_custom_abbr`

```php
$Parsedown = new ParsedownExtended([
    'abbreviations' => [
        'allow_custom_abbr': false
        'predefine' => [
            'CSS' => 'Cascading Style Sheet',
            'HTML' => 'Hyper Text Markup Language',
            'JS' => 'JavaScript'
        ]
    ]
]);
```


### Blockqoutes
To enable/disable blockqoutes use the following:
```php
$Parsedown = new ParsedownExtended([
    'blockqoutes' => true
]);
```

### Code
To enable/disable code use the following:
```php
$Parsedown = new ParsedownExtended([
    'code_blocks' => true,
    'inline_code' => true
]);
```

### Definition list
To enable/disable definition list use the following:
```php
$Parsedown = new ParsedownExtended([
    'definition_list' => true
]);
```

### Emails
To enable/disable automatic email link use the following:
```php
$Parsedown = new ParsedownExtended([
    'auto_mark_emails' => true
]);
```

### Emojis
To enable/disable emojis use the following:
```php
$Parsedown = new ParsedownExtended([
    'emojis' => true
]);
```

### Emphasis
To enable/disable emphasis use the following:
```php
$Parsedown = new ParsedownExtended([
    'blockqoutes' => true
]);
```

### Footnotes
To enable/disable footnotes use the following:
```php
$Parsedown = new ParsedownExtended([
    'footnotes' => true
]);
```

### Headings
To enable/disable headings use the following:
```php
$Parsedown = new ParsedownExtended([
    'headings' => true
]);
```

#### Heading permalink
To enable/disable automatic heading permalink use the following:
```php
$Parsedown = new ParsedownExtended([
    'headings' => [
        'auto_anchors' => true
    ]
]);
```

#### Allowed
Choose what headings level can be used in the markdown
```php
$Parsedown = new ParsedownExtended([
    'headings' => [
        'allowed' => ['h1','h2','h3']
    ]
]);
```


#### Blacklist
To block any ids from being included in the ToC simply use the following:
```php
$Parsedown = new ParsedownExtended([
    'headings' => [
        'blacklist' => ['my_blacklisted_header_id','another_blacklisted_id']
    ]
]);
```

### Horizontal rule / Thematic breaks
To enable/disable thematic breaks use the following:
```php
$Parsedown = new ParsedownExtended([
    'thematic_breaks' => true
]);
```

### Images
To enable/disable images use the following:
```php
$Parsedown = new ParsedownExtended([
    'images' => true
]);
```

### Keystrokes
To enable/disable keystrokes use the following:
```php
$Parsedown = new ParsedownExtended([
    'keystrokes' => true
]);
```

### LaTeX
ParsedownExtended adds the ability to use LaTeX in your markdown, by using regular expression to find and recognize LaTeX to avoid formatting it. This enables you to use a library like [KaTeX](https://katex.org) to make the on-device rendering of the code.

To enable LaTeX support:
```php
$Parsedown = new ParsedownExtended([
    'math' => true
]);
```
If you want to use single dollar to active LaTeX mode you can do the following:
```php
$Parsedown = new ParsedownExtended([
    'math' => [
        'single_dollar' => true
    ]
]);
```

### Links
To enable/disable links use the following:
```php
$Parsedown = new ParsedownExtended([
    'links' => true
]);
```

### Lists
To enable/disable lists use the following:
```php
$Parsedown = new ParsedownExtended([
    'lists' => true
]);
```

### Mark
To enable/disable highlight/mark use the following:
```php
$Parsedown = new ParsedownExtended([
    'marks' => true
]);
```

### Smartypants
To enable/disable smartypants use the following:
```php
$Parsedown = new ParsedownExtended([
    'smartypants' => true
]);
```

### Strikethrough
To enable/disable strikethrough use the following:
```php
$Parsedown = new ParsedownExtended([
    'strikethrough' => true
]);
```

### Subscript
To enable/disable subscript use the following:
```php
$Parsedown = new ParsedownExtended([
    'subscripts' => true
]);
```

### Superscript
To enable/disable superscript use the following:
```php
$Parsedown = new ParsedownExtended([
    'superscripts' => true
]);
```

### Table of content
To enable/disable TOC use the following:
```php
$Parsedown = new ParsedownExtended([
    'toc' => true
]);
```

`setTagToc(string $tag='[tag]')`:
- Sets user defined ToC markdown tag. Use this method before `text()` or `body()` method if you want to use the ToC tag rather than the "`[toc]`".
- Empty value sets the default ToC tag.

With the `contentsList()` method, you can get just the "ToC".
```php
$toc = $Parsedown->contentsList();
```

Returns the parsed content WITHOUT parsing `[toc]` tag.
```php
$toc = $Parsedown->body();
```

Returns the parsed content and `[toc]` tag(s) parsed as well.
```php
$toc = $Parsedown->text();
```

Example
```php
// Parse body and ToC separately
$content = file_get_contents('sample.md');
$Parsedown = new \ParsedownToC();

$body = $Parsedown->body($content);
$toc = $Parsedown->contentsList();

echo $toc;  // Table of Contents in <ul> list
echo $body; // Main body
```

#### Delimiter
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'delimiter' => true 
    ]
]);
```
#### Headings
Choose what headings level to include in the ToC list.
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'headings' => ['h1','h2','h3']  
    ]
]);
```
#### Lowercase
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'lowercase' => true 
    ]
]);
```
#### Transliterate
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'transliterate' => true 
    ]
]);
```
#### Urlencode
```php
$Parsedown = new ParsedownExtended([
    'toc' => [
        'urlencode' => true 
    ]
]);
```

### Tables
To enable/disable headings use the following:
```php
$Parsedown = new ParsedownExtended([
    'tables' => true
]);
```

### Task
To enable/disable task use the following:
```php
$Parsedown = new ParsedownExtended([
    'tasks' => true
]);
```

### Diagrams
ParsedownExtended add the ability to use diagrams in your markdown, by adding support for  [ChartJS](https://www.chartjs.org) and [Mermaid](https://mermaid-js.github.io/mermaid/)

To enable/disable diagrams use the following:
```php
$Parsedown = new ParsedownExtended([
    'diagrams' => true
]);
```
