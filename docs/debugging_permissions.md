# Debugging Storage Permissions

If you encounter `Permission denied` errors for `storage/logs/laravel.log` or `storage/framework/views/*`, it usually means the web server user (e.g., `http` or `www-data`) and your CLI user (e.g., `craigpar`) have conflicting ownership of the files.

Here are the tools and strategies to fix this fast.

## 1. The Permissions Debug Script (`debug_perms.php`)

Create this file in `public/debug_perms.php` and visit it in your browser to see *exactly* who the web server is running as and what permissions it sees.

```php
<?php
echo "<pre>";
echo "User: " . exec('whoami') . "\n";
$logDir = '../storage/logs';
echo "Log Dir: $logDir\n";
echo "Log Dir Perms: " . substr(sprintf('%o', fileperms($logDir)), -4) . "\n";
echo "Log Dir Owner: " . posix_getpwuid(fileowner($logDir))['name'] . "\n";

$logFile = $logDir . '/laravel.log';
if (file_exists($logFile)) {
  echo "Log File Perms: " . substr(sprintf('%o', fileperms($logFile)), -4) . "\n";
  echo "Log File Owner: " . posix_getpwuid(fileowner($logFile))['name'] . "\n";
} else { 
    echo "Log file does not exist\n"; 
}

$viewsDir = '../storage/framework/views';
echo "\nViews Dir: $viewsDir\n";
echo "Views Dir Perms: " . substr(sprintf('%o', fileperms($viewsDir)), -4) . "\n";
echo "Views Dir Owner: " . posix_getpwuid(fileowner($viewsDir))['name'] . "\n";

echo "\nAttempting to write to log file...\n";
try {
    file_put_contents($logFile, "Test log entry\n", FILE_APPEND);
    echo "Success: Wrote to log file.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
```

## 2. The "Nuclear Option" (Storage Redirect)

If you cannot change permissions (e.g. `chmod` fails because you don't own the files and `sudo` isn't available or working), you can bypass the `storage` directory entirely.

1. Create a new directory that **you** own:

    ```bash
    mkdir storage_temp
    chmod -R 777 storage_temp
    ```

2. Tell Laravel to use this new directory in `bootstrap/app.php`:

    ```php
    // ... existing code ...
    
    $app = Application::configure(basePath: dirname(__DIR__))
        ->withRouting(...)
        ->withMiddleware(...)
        ->withExceptions(...)
        ->create();
    
    // ADD THIS LINE:
    $app->useStoragePath(base_path('storage_temp'));
    
    return $app;
    ```

This forces Laravel to write logs and views to `storage_temp` instead of `storage`. Since you created `storage_temp`, you have full control over it.

## 3. The "Web Shell" Fix (`fix.php`)

Sometimes you need to delete files but only the web server has permission. You can create a temporary PHP script to run commands *as the web server*.

Create `public/fix.php`:

```php
<?php
echo "<pre>";
// Delete stubborn log files
system('rm -rf ../storage/logs/laravel.log');
// Delete view cache
system('rm -rf ../storage/framework/views/*');
// Try to fix permissions
system('chmod -R 777 ../storage');
echo "Done.";
```

Visit `/fix.php` in your browser, then **delete the file immediately** after use security.
