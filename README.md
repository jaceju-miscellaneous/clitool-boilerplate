# CLITool Boilerplate

Simple boilerplate for build your command line tool.

## Requirement

* PHP 5.4+

## Installation

```bash
composer create-project jaceju/clitool-boilerplate myapp -s dev
```

## Change application information

Rename the application in `src/App/Application.php`:

```diff
-    const NAME = 'App';
+    const NAME = 'MyApp';
-    const REPOSITORY = 'jaceju/clitool-boilerplate';
+    const REPOSITORY = 'vendor/repository';
```

Then change the info in `composer.json`.

## Build executable phar

It will build phar and publish it to remote site automatically.

```bash
php build.php [version]
```

User can download it by this command:

```bash
curl -L -O https://vendor.github.io/repository/downloads/myapp.phar
chmod +x myapp.phar
sudo mv myapp.phar /usr/local/bin/myapp
```

`self update` :

```bash
myapp self-update
```

## Zsh auto-completion

```bash
myapp zsh --bind myapp > ~/.zsh/myapp
```

Then add these lines to your `.zshrc` file:

```
source ~/.zsh/myapp
```

## Powered by

* [c9s/CLIFramework](https://github.com/c9s/CLIFramework)
* [box-project/box2](https://github.com/box-project/box2)
* [cpliakas/manifest-publisher](https://github.com/cpliakas/manifest-publisher)

## License

MIT
