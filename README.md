# CLITool Boilerplate

Simple boilerplate for build your command line tool.

Powered by [c9s/CLIFramework](https://github.com/c9s/CLIFramework)

## Installation

```bash
composer create-project jaceju/clitool-boilerplate myapp -s dev
```

## Change application information

Rename the application in `src/App/Application.php`:

```diff
-    const NAME = 'App';
+    const NAME = '<MyApp>';
     const VERSION = '0.0.1';
```

Then change the info in `composer.json`.

## Build executable phar

```bash
php src/bootstrap.php self-build
chmod +x bin/app
mv bin/app /usr/local/bin/
```

## Zsh auto-completion

```bash
myapp zsh --bind myapp > ~/.zsh/myapp
```

Then add these lines to your `.zshrc` file:

```
source ~/.zsh/myapp 
```

## License

MIT
