# ChangeLog

## v1.0.1
- Accept custom render engine and use that engine's `render` method if exists
- Fix section render logic `code process`
- Trim any whitespace from `$view` parameter of `render` method. `eg. src > view.php ==> src>view.php`
- Automatically create `path` the user set in constructor if `path` is not exists
- Change method names, `displaySection` to `yield`, `endSection` to `end`
- remove `clean` method