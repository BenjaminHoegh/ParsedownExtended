# Docs Branch Fixes

This directory contains a patch that fixes several issues in the `docs` branch documentation site.

## How to apply

```bash
git checkout docs
git apply docs-branch-fixes.patch
git commit -m "Fix docs: wrong versions, config keys, missing Alerts page, and add external_links docs"
git push origin docs
```

Then delete this `docs-patches/` directory from the main branch (it is only included here because
the PR automation can only push to the PR branch, not the `docs` branch directly).

## What this patch fixes

| File | Issue | Fix |
|------|-------|-----|
| `docs/Installation.md` | Wrong minimum versions listed (Parsedown 1.7, ParsedownExtra 0.8) | Updated to Parsedown **1.8** and ParsedownExtra **0.9** |
| `docs/Installation.md` | Missing `use BenjaminHoegh\ParsedownExtended\ParsedownExtended;` in code examples | Added namespace import to both Composer and Manual examples |
| `docs/Introduction.md` | GFM Alerts missing from the features list | Added "GFM Alerts" feature bullet |
| `docs/Configuration/Smartypants.md` | All examples use wrong config key `'smarty'` | Fixed to `'smartypants'`; also added missing `smart_backticks` option |
| `docs/Configuration/Headings.md` | "Custom Anchor IDs" example uses non-existent `config()->set('headings.custom_anchor_id_callback', ...)` | Replaced with correct `setCreateAnchorIDCallback()` method usage |
| `docs/Configuration/Links.md` | Entire `external_links` sub-feature undocumented | Added full docs for `nofollow`, `noopener`, `noreferrer`, `open_in_new_window`, `internal_hosts` |
| `docs/Configuration/Table of Contents.md` | Wrong parameter name `headings` | Fixed to `levels`; also corrected tag example from `[toc]` to `[TOC]` |
| `docs/Configuration/Alerts.md` | File did not exist | Created new page documenting the GFM Alerts feature |
