# Contributing to ParsedownExtended

Looking to contribute something to ParsedownExtended? **Here's how you can help.**

Please take a moment to review this document in order to make the contribution
process easy and effective for everyone involved.

Following these guidelines helps to communicate that you respect the time of
the developers managing and developing this open source project. In return,
they should reciprocate that respect in addressing your issue or assessing
patches and features.


## Using the issue tracker

The [issue tracker](https://github.com/BenjaminHoegh/ParsedownExtended/issues) is
the preferred channel for [bug reports](#bug-reports), [features requests](#feature-requests)
and [submitting pull requests](#pull-requests), but please respect the following
restrictions:

* Please **do not** use the issue tracker for personal support requests.

* Please **do not** derail or troll issues. Keep the discussion on topic and
  respect the opinions of others.

* Please **do not** post comments consisting solely of "+1" or ":thumbsup:".
  Use [GitHub's "reactions" feature](https://github.com/blog/2119-add-reactions-to-pull-requests-issues-and-comments)
  instead. We reserve the right to delete comments which violate this rule.

## Issues and labels

Our bug tracker utilizes several labels to help organize and identify issues. Here's what they represent and how we use them:

- `Bug` - Issues that have been confirmed with a reduced test case and identify a bug in ParsedownExtended.
- `Duplicate` - Issue or pull request already exists
- `Enhancement` - Issues that will iterate on existing functionality.
- `Feature` - Issues asking for a new feature to be added, or an existing one to be extended or modified.
- `Fixed` - Issue related to a bug there has been fixed
- `Help wanted` - Issues we need or would love help from the community to resolve.
- `Hotfix` - Issue there has been resolved as a part of a hotfix
- `In progress` - Issue there are currently been working on
- `Invalid` - Issues where no actions are needed or possible. The issue is either fixed, addressed better by other
- `Investigation` - Issues that require further investigation
- `Meta` - Issues with the project itself or our GitHub repository.
- `Need more info` - Issues that require further conversation to figure out how to proceed or what action steps are needed.
- `On hold` - Issues where no actions are needed or possible. The issue is either fixed, addressed better by other
- `Question` - Issues that require further conversation to figure out how to proceed or what action steps are needed
- `Ready for release` -  Issue where a new functionality or a bug is finished and will be released in the next version.
- `Ready for review` - Issues that require further conversation to figure out how to proceed or what action steps are needed
- `Released` -  An issue where a new functionality or a bug is finished and has been released.
- `Under consideration` - Issues where action can be taken, but has not yet.
- `Docs` - Issues for improving or updating our documentation.
- `Won't fix` - Issues where no actions are needed or possible. The issue is either fixed, addressed better by other
- `Examples` - Issues involving the example templates included in our docs.

For a complete look at our labels, see the [project labels page](https://github.com/BenjaminHoegh/ParsedownExtended/labels).


## Bug reports

A bug is a _demonstrable problem_ that is caused by the code in the repository.
Good bug reports are extremely helpful, so thanks!

Guidelines for bug reports:

1. **Validate and lint your code** &mdash; [Validate and lint your PHP](https://phptools.online/php-checker) to ensure your problem isn't caused by a simple error in your own code.

2. **Use the GitHub issue search** &mdash; check if the issue has already been reported.

3. **Check if the issue has been fixed** &mdash; try to reproduce it using the
   latest `master` or development branch in the repository.

A good bug report shouldn't leave others needing to chase you up for more
information. Please try to be as detailed as possible in your report. What is
your environment? What steps will reproduce the issue? What would you expect to be the outcome? All these details will help people to fix
any potential bugs.

Example:

> Short and descriptive example bug report title
>
> A summary of the issue and the environment in which it occurs. If
> suitable, include the steps required to reproduce the bug.
>
> 1. This is the first step
> 2. This is the second step
> 3. Further steps, etc.
>
> `<url>` - a link to the reduced test case
>
> Any other information you want to share that is relevant to the issue being
> reported. This might include the lines of code that you have identified as
> causing the bug, and potential solutions (and your opinions on their
> merits).

## Feature requests

Before opening a feature request, please take a moment to find out whether your idea
fits with the scope and aims of the project. It's up to *you* to make a strong
case to convince the project's developers of the merits of this feature. Please
provide as much detail and context as possible.

## Pull requests

Good pull requests—patches, improvements, new features—are a fantastic
help. They should remain focused in scope and avoid containing unrelated
commits.

**Please ask first** before embarking on any significant pull request (e.g.
implementing features, refactoring code, porting to a different language),
otherwise you risk spending a lot of time working on something that the
project's developers might not want to merge into the project.

Please adhere to the [coding guidelines](#code-guidelines) used throughout the
project (indentation, accurate comments, etc.) and any other requirements
(such as test coverage).

Similarly, when contributing to ParsedownExtended's documentation, you should edit the
documentation in the [wiki](https://github.com/BenjaminHoegh/ParsedownExtended/wiki).

### Which Branch?

**All** bug fixes should be sent to the `master` branch. Bug fixes should **never** be sent to the `dev` branch unless they fix features that exist only in the upcoming release.

**Minor** features that are fully backward compatible with the current release may be sent to the `master` branch.

**Major** new features should always be sent to the `dev` branch, which contains the upcoming release.

If you are unsure if your feature qualifies as a major or minor, please ask BenjaminHoegh in the #contributors channel in the [GitHub Discussions section](https://github.com/BenjaminHoegh/ParsedownExtended/discussions/categories/contributors).

### Get started

Adhering to the following process is the best way to get your work
included in the project:

1. [Fork](https://help.github.com/fork-a-repo/) the project, clone your fork,
   and configure the remotes:

   ```bash
   # Clone your fork of the repo into the current directory
   git clone https://github.com/<your-username>/ParsedownExtended.git
   # Navigate to the newly cloned directory
   cd ParsedownExtended
   # Assign the original repo to a remote called "upstream"
   git remote add upstream https://github.com/BenjaminHoegh/ParsedownExtended.git
   ```

2. If you cloned a while ago, get the latest changes from upstream:

   ```bash
   git checkout developer
   git pull upstream developer
   ```

3. Create a new topic branch (off the main project development branch) to
   contain your feature, change, or fix:

   ```bash
   git checkout -b <topic-branch-name>
   ```

4. Commit your changes in logical chunks. Please adhere to these [git commit
   message guidelines](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html)
   or your code is unlikely to be merged into the main project. Use Git's
   [interactive rebase](https://help.github.com/articles/interactive-rebase)
   feature to tidy up your commits before making them public.

5. Locally merge (or rebase) the upstream development branch into your topic branch:

   ```bash
   git pull [--rebase] upstream developer
   ```

6. Push your topic branch up to your fork:

   ```bash
   git push origin <topic-branch-name>
   ```

7. [Open a Pull Request](https://help.github.com/articles/using-pull-requests/)
    with a clear title and description against the target branch.

**IMPORTANT**: By submitting a patch, you agree to allow the project owners to
license your work under the terms of the [MIT License](LICENSE) (if it
includes code changes) and under the terms of the
[Creative Commons Attribution 3.0 Unported License](docs/LICENSE)
(if it includes documentation changes).


## Code guidelines

### PHP
- Four (4) spaces indents, no tabs;
- Ideally, 80-characters wide lines;
- Comment your code, a little is better than nothing.
- All PHP reserved keywords and types must be in lower case.
- Blank lines MAY be added to improve readability and to indicate related blocks of code except where explicitly forbidden.
- All code should follow the syntax guidelines on [php-fig.org](https://www.php-fig.org/psr/psr-12/)

### Markdown
- Use 4 space indent.
- Follow [Google's styleguide](https://google.github.io/styleguide/docguide/style.html) based on CommonMark.

## License

By contributing your code, you agree to license your contribution under the [MIT License](LICENSE).
By contributing to the documentation, you agree to license your contribution under the [Creative Commons Attribution 3.0 Unported License](docs/LICENSE).
