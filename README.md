# Boilerplate for build a command line tool.

**Working in progress.**

Powered by [c9s/CLIFramework](https://github.com/c9s/CLIFramework)

## Installation

```bash
composer create-project jaceju/clitool-boilerplate myapp -s dev
```

## Change application name

`src/App/Application.php`

```diff
-    const NAME = 'App';
+    const NAME = '<MyApp>';
     const VERSION = '0.0.1';
```

## Build executable phar

```bash
php src/bootstrap.php self-build
chmod +x bin/app.phar
mv bin/app.phar /usr/local/bin/app
```

## License

MIT
