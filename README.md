# Parsedown Extreme 
![Release](	https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtreme.svg?style=flat-square) ![GitHub (pre-)release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtreme/all.svg?style=flat-square&label=pre-release) ![Github All Releases](https://img.shields.io/github/downloads/BenjaminHoegh/ParsedownExtreme/total.svg?style=flat-square)

Parsedown Extreme is a extension to [Parsedown Extra](https://github.com/erusev/parsedown-extra) to add even more functions to the library.

---

### Installation

* Download the "Source code" from the [latest release](https://github.com/BenjaminHoegh/ParsedownExtreme/releases/latest)
* Include `ParsedownExtreme.php`
* You must include `parsedown.php` and `parsedownExtra.php` too.


### Example

```php
$ParsedownExtreme = new ParsedownExtreme();

echo $ParsedownExtreme->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
// you can also parse inline markdown only
echo $ParsedownExtreme->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!
```

---

## New Features

See all new features below

### Task list

Default `enabled`

**Example**

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

### Superscript & Subscript

To toggle Superscript & Subscript you most call `$ParsedownExtreme->superscript('true'|'false')`

**Default:** `disabled`

**Example**

```markdown
Superscript: 19^th^

Subscript: H~2~O
```  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/supandsub.png' height='100px'>


### Insert and mark

To toggle insert you most call `$ParsedownExtreme->insert('true'|'false')`
and `$ParsedownExtreme->mark('true'|'false')` for mark

**Default:** `enabled`

**Example**

```markdown
++Inserted text++

==Marked text==
```  

<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/insertandmark.png' height='100px'>


### Video embeding

Video embeding support Youtube, Vimeo and Dailtmotion

To toggle Video embeding you most call `$ParsedownExtreme->embeding('true'|'false')`

**Default:** `true`

**Example**

```markdown
<!-- Also works with normal URL -->
[video src="https://www.youtube.com/watch?v=dWO9uP_VJV8"]

<!-- And with embed URL -->
[video src="https://www.youtube.com/embed/dWO9uP_VJV8"]

<!-- Vimeo -->
[video src="https://player.vimeo.com/video/262117047"]

<!-- Dailymotion -->
[video src="//www.dailymotion.com/embed/video/x6nbzp4"]
```

<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/videoembeding.png' height='300px'>


### Typograpic shurtcodes

To toggle Typograpic shurtcodes you most call `$ParsedownExtreme->typography('true'|'false')`

**Default:** `disabled`

**Example**

`(c) (C) (r) (R) (tm) (TM)`  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/typography.png' height='50px'>


### (La)KaTeX

To enable KaTeX you must [download katex](https://katex.org)

To toggle KaTeX you most call `$ParsedownExtreme->katex('true'|'false')`

**Default:** `disabled`

**Example**

```Latex
$$
    x = {-b \pm \sqrt{b^2-4ac} \over 2a}.
$$
```
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/katex.png' height='100px'>


### Mermaid

To enable Mermaid [download Mermaid](https://mermaidjs.github.io) and use `$ParsedownExtreme->mermaid('true'|'false')` to enable it


**Default:** `disabled`

**Example**

```Mermaid
%%
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
%%
```  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/mermaid.png' height='250px'>
