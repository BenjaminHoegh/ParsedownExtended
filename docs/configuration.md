# Configuration Reference

All options are read and updated through the parser instance:

```php
$parsedown = new BenjaminHoegh\ParsedownExtended\ParsedownExtended();
$parsedown->config()->set('math', true);
$parsedown->config()->set('links.external_links.open_in_new_window', false);
```

Feature aliases such as `math`, `diagrams`, `links`, `toc`, or `headings.auto_anchors` map to their explicitly defined `*.enabled` option. For example, `config()->set('math', true)` is equivalent to `config()->set('math.enabled', true)`.

This reference is generated from `Configuration::definitions()`. Add or change an option there rather than editing this table manually.

## Options

| Path | Type | Default | Description |
| --- | --- | --- | --- |
| `abbreviations.enabled` | boolean | `true` | Enables abbreviation processing. Alias: `abbreviations`. |
| `abbreviations.allow_custom` | boolean | `true` | Allows custom Markdown abbreviation definitions. |
| `abbreviations.predefined` | array | `[]` | Abbreviations to load before every parse, keyed by abbreviation. |
| `alerts.enabled` | boolean | `true` | Enables GitHub-style alert blocks. Alias: `alerts`. |
| `alerts.class` | string | `'markdown-alert'` | Base CSS class used for generated alert wrappers. |
| `alerts.types` | array | `['note', 'tip', 'important', 'warning', 'caution']` | Alert labels accepted after `> [!...]`. |
| `allow_raw_html` | boolean | `true` | Allows raw inline and block HTML when safe mode is not escaping it. |
| `code.enabled` | boolean | `true` | Enables code parsing. Alias: `code`. |
| `code.blocks` | boolean | `true` | Enables indented and fenced code blocks. |
| `code.inline` | boolean | `true` | Enables inline backtick code. |
| `comments` | boolean | `true` | Enables raw HTML comments when raw HTML is allowed. |
| `definition_lists` | boolean | `true` | Enables Parsedown Extra definition lists. |
| `diagrams.enabled` | boolean | `false` | Enables diagram-aware fenced code handling. Alias: `diagrams`. |
| `diagrams.chartjs` | boolean | `true` | Converts `chart` and `chartjs` fences to Chart.js canvas elements. |
| `diagrams.mermaid` | boolean | `true` | Converts `mermaid` fences to Mermaid containers. |
| `emojis` | boolean | `true` | Enables emoji shortcode replacement. |
| `emphasis.enabled` | boolean | `true` | Enables emphasis extensions. Alias: `emphasis`. |
| `emphasis.bold` | boolean | `true` | Enables bold text. |
| `emphasis.insertions` | boolean | `true` | Enables insertion syntax using `++text++`. |
| `emphasis.italic` | boolean | `true` | Enables italic text. |
| `emphasis.keystrokes` | boolean | `true` | Enables keystroke syntax using `[[Ctrl]]`. |
| `emphasis.mark` | boolean | `true` | Enables mark syntax using `==text==`. |
| `emphasis.strikethroughs` | boolean | `true` | Enables strikethrough syntax. |
| `emphasis.subscript` | boolean | `false` | Enables subscript syntax using `~text~`. |
| `emphasis.superscript` | boolean | `false` | Enables superscript syntax using `^text^`. |
| `footnotes` | boolean | `true` | Enables Parsedown Extra footnotes. |
| `headings.enabled` | boolean | `true` | Enables heading parsing. Alias: `headings`. |
| `headings.allowed_levels` | array | `['h1', 'h2', 'h3', 'h4', 'h5', 'h6']` | Heading levels that may render. |
| `headings.auto_anchors.enabled` | boolean | `true` | Generates heading IDs. Alias: `headings.auto_anchors`. |
| `headings.auto_anchors.blacklist` | array | `[]` | Heading IDs to skip when generating unique IDs. |
| `headings.auto_anchors.delimiter` | string | `'-'` | Replacement delimiter used when building heading IDs. |
| `headings.auto_anchors.lowercase` | boolean | `true` | Lowercases generated heading IDs. |
| `headings.auto_anchors.replacements` | array | `[]` | Regular-expression replacements applied before heading ID sanitization. |
| `headings.auto_anchors.transliterate` | boolean | `false` | Transliterates heading IDs toward ASCII. |
| `headings.special_attributes` | boolean | `true` | Enables Parsedown Extra heading attributes such as `{#custom-id}`. |
| `images` | boolean | `true` | Enables image parsing. |
| `links.enabled` | boolean | `true` | Enables link parsing. Alias: `links`. |
| `links.current_host` | string | `''` | Host name used when determining whether absolute links are external. |
| `links.email_links` | boolean | `true` | Enables autolinked email addresses. |
| `links.external_links.enabled` | boolean | `true` | Enables external links. Alias: `links.external_links`. |
| `links.external_links.internal_hosts` | array | `[]` | Hostnames treated as internal even when absolute URLs are used. |
| `links.external_links.nofollow` | boolean | `false` | Adds `nofollow` to external link `rel` attributes. |
| `links.external_links.noopener` | boolean | `false` | Adds `noopener` to external link `rel` attributes. |
| `links.external_links.noreferrer` | boolean | `false` | Adds `noreferrer` to external link `rel` attributes. |
| `links.external_links.open_in_new_window` | boolean | `false` | Adds `target="_blank"` to external links. |
| `lists.enabled` | boolean | `true` | Enables ordered and unordered list parsing. Alias: `lists`. |
| `lists.tasks` | boolean | `true` | Enables task-list checkboxes. |
| `math.enabled` | boolean | `false` | Enables math parsing. Alias: `math`. |
| `math.block.enabled` | boolean | `true` | Enables block math when math is enabled. Alias: `math.block`. |
| `math.block.delimiters` | array | `[['left' => '$$', 'right' => '$$']]` | Block math delimiter pairs. |
| `math.inline.enabled` | boolean | `true` | Enables inline math when math is enabled. Alias: `math.inline`. |
| `math.inline.delimiters` | array | `[['left' => '$', 'right' => '$']]` | Inline math delimiter pairs. |
| `quotes` | boolean | `true` | Enables block quotes. |
| `references` | boolean | `true` | Enables reference-style links. |
| `smartypants.enabled` | boolean | `false` | Enables Smartypants substitutions. Alias: `smartypants`. |
| `smartypants.smart_angled_quotes` | boolean | `true` | Converts `<<quotes>>` when Smartypants is enabled. |
| `smartypants.smart_backticks` | boolean | `true` | Converts double-backtick quotes when Smartypants is enabled. |
| `smartypants.smart_dashes` | boolean | `true` | Converts double and triple dashes when Smartypants is enabled. |
| `smartypants.smart_ellipses` | boolean | `true` | Converts three-dot ellipses when Smartypants is enabled. |
| `smartypants.smart_quotes` | boolean | `true` | Converts straight quotes when Smartypants is enabled. |
| `smartypants.substitutions.ellipses` | string | `'&hellip;'` | Replacement for ellipses. |
| `smartypants.substitutions.left_angle_quote` | string | `'&laquo;'` | Replacement for left angle quotes. |
| `smartypants.substitutions.left_double_quote` | string | `'&ldquo;'` | Replacement for left double quotes. |
| `smartypants.substitutions.left_single_quote` | string | `'&lsquo;'` | Replacement for left single quotes. |
| `smartypants.substitutions.mdash` | string | `'&mdash;'` | Replacement for em dashes. |
| `smartypants.substitutions.ndash` | string | `'&ndash;'` | Replacement for en dashes. |
| `smartypants.substitutions.right_angle_quote` | string | `'&raquo;'` | Replacement for right angle quotes. |
| `smartypants.substitutions.right_double_quote` | string | `'&rdquo;'` | Replacement for right double quotes. |
| `smartypants.substitutions.right_single_quote` | string | `'&rsquo;'` | Replacement for right single quotes. |
| `tables.enabled` | boolean | `true` | Enables table parsing. Alias: `tables`. |
| `tables.tablespan` | boolean | `true` | Enables colspan and rowspan table span handling. |
| `thematic_breaks` | boolean | `true` | Enables thematic breaks such as `---`, `***`, and `___`. |
| `toc.enabled` | boolean | `true` | Enables table-of-contents generation. Alias: `toc`. |
| `toc.id` | string | `'toc'` | ID used on the generated ToC wrapper. |
| `toc.levels` | array | `['h1', 'h2', 'h3', 'h4', 'h5', 'h6']` | Heading levels included in generated ToCs. |
| `toc.tag` | string | `'[TOC]'` | Marker replaced by the generated ToC. |
| `typographer` | boolean | `true` | Enables typographer substitutions such as `(c)`, `(r)`, `(tm)`, and ellipses. |
