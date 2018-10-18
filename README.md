# ParsedownExtreme 
![Release](	https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtreme.svg?style=flat-square) ![GitHub (pre-)release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownExtreme/all.svg?style=flat-square&label=pre-release) ![Github All Releases](https://img.shields.io/github/downloads/BenjaminHoegh/ParsedownExtreme/total.svg?style=flat-square)

ParsedownExtreme is a extension to ParsedownExtra to add even more functions to the library.

### Installation

* Download the "Source code" from the [latest release](https://github.com/BenjaminHoegh/ParsedownExtreme/releases/latest)
* Include `ParsedownExtreme.php`
* You must include `parsedown.php` and `parsedownExtra.php` too.


### Example

    $ParsedownExtreme = new ParsedownExtreme();

    echo $ParsedownExtreme->text('Hello _Parsedown_!'); # prints: <p>Hello <em>Parsedown</em>!</p>
    // you can also parse inline markdown only
    echo $ParsedownExtreme->line('Hello _Parsedown_!'); # prints: Hello <em>Parsedown</em>!


## New Features

See all new features below

#### Task list

Default `enabled`


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

#### Superscript & Subscript

To enable Superscript & Subscript you most call `$ParsedownExtreme->enableSuperscript()`

Default `disabled`

```markdown
Superscript: 19^th^

Subscript: H~2~O
```  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/supandsub.png' height='100px'>


#### Insert and mark

To enable Superscript & Subscript you most call `$ParsedownExtreme->enableSuperscript()`

Default `enabled`


```markdown
++Inserted text++

==Marked text==
```  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/insertandmark.png' height='100px'>




#### Typograpic shurtcodes

To enable Superscript & Subscript you most call `$ParsedownExtreme->enableTypography()`

Default `disabled`

`(c) (C) (r) (R) (tm) (TM)`  
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/typography.png' height='50px'>

#### KaTeX

Default `disabled`


To enable KaTeX [download](https://katex.org) and then set `$ParsedownExtreme->enableKaTeX()` to enable it.

```Latex
$$
    x = {-b \pm \sqrt{b^2-4ac} \over 2a}.
$$
```
<img src='https://github.com/BenjaminHoegh/ParsedownExtreme/blob/master/docs/img/katex.png' height='100px'>


#### Mermaid

To enable Mermaid [download Mermaid](https://mermaidjs.github.io) and then set `$ParsedownExtreme->enableMermaid()` to enable it.

Default `disabled`


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
