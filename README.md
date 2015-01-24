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
+    const NAME = '<YourAppName>';
     const VERSION = '0.0.1';
```

`build.php`

```diff
-        $pharName = 'app.phar';
+        $pharName = '<your-app-name>.phar';
```

## Build phar file

```bash
php build.php
```

and test:

```
php bin/app.phar
```

## License

MIT