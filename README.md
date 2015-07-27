# CLITool Boilerplate

Simple boilerplate for build your command line tool.

## Requirement

* PHP 5.4+

## Installation

```bash
composer create-project jaceju/clitool-boilerplate myapp -s dev
```

## Change application information

Change the constants below in `src/App/Application.php`:

```php
    const NAME = 'App';
    const BIN_NAME = 'app';
    const REPOSITORY = 'vendor-name/app-repository';
```

Rename output phar in `box.json`:

```json
    "output": "bin/app.phar",
```

**Finally, change the `name`, `description`, `authors` and `scripts` in `composer.json`.**

---

Here is a example of `README.md` below for authors of the package. I suppose package name is `app` in this example.

**You can remove all description above and this line.**

# App

## Requirement

* PHP 5.4+

## Installation

Add `~/.composer/vendor/bin/` to `PATH` environment variable first. Then can install the package by:

```bash
composer global require vendor-name/app-repository
```

And `app` command should be executable.

## Build executable phar

You can build the phar file by:

```bash
composer build
```

## Self Updating

Update `app` to latest version:

```bash
app self-update
```

## Zsh auto-completion

You can create an auto-completion of `app` for zsh by:

```bash
app zsh --bind app > ~/.zsh/app
```

Then add the line below to your `.zshrc` file:

```
source ~/.zsh/app
```

> Same steps as above in bash auto-completion.

## Powered by

* [c9s/CLIFramework](https://github.com/c9s/CLIFramework)
* [box-project/box2](https://github.com/box-project/box2)

## License

MIT
