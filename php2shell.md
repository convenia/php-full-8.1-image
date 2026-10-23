# php2shell backport (CVE-2026-109618 / CVE-2026-109619)

PHP 7.4 and 8.0 are end of life, so no PHP release fixes these CVEs for them. The PHP security team published
minimal source backports instead
([advisory on php.net](https://www.php.net/)), and the `7.4` and `8.0` images rebuild PHP from source with them:

| Image tag | Base image                   | Patch                                              | php-src commit (backport branch)                    |
|-----------|------------------------------|----------------------------------------------------|-----------------------------------------------------|
| `7.4`     | `php:7.4.33-fpm-alpine3.16`  | `7.4/php-7.4-CVE-2026-109618-109619.patch`         | `712e60f9edfd` (`PHP-7.4-CVE-2026-109618-109619`)   |
| `8.0`     | `php:8.0.30-fpm-alpine3.16`  | `8.0/php-8.0-CVE-2026-109618-109619.patch`         | `82f8f31b16a0` (`PHP-8.0-CVE-2026-109618-109619`)   |

PHP 5.6 has no official backport, so there is no patched 5.6 image.

The patch files are the official commits, unchanged. To check one against php-src:

```sh
curl -sSfL https://github.com/php/php-src/commit/712e60f9edfd03f0960fc4fc3c2c375f42b90aa2.patch | diff - 7.4/php-7.4-CVE-2026-109618-109619.patch
curl -sSfL https://github.com/php/php-src/commit/82f8f31b16a035c1b67cc253e79f7cc288f4cc20.patch | diff - 8.0/php-8.0-CVE-2026-109618-109619.patch
```

No output means the files are identical.

## Build and publish

Each version has its own workflow, which only runs when started by hand (Actions > `docker-image-7.4` or
`docker-image-8.0` > Run workflow). It builds the image, tests it, and pushes the multi-arch tag
(amd64 and arm64) to `convenia/php-full`.

To build locally:

```sh
docker build -f 7.4/Dockerfile -t convenia/php-full:7.4 .
docker build -f 8.0/Dockerfile -t convenia/php-full:8.0 .
```

## How to prove the patch is applied

Run each check against the image under test and, for comparison, against the unpatched base image from the table above.

### 1. Behaviour test (strongest evidence)

The patch changes what PHP does when a script runs out of memory (the CVE-2026-109618 path):

- **Unpatched:** PHP raises a normal fatal error, still runs shutdown functions, and exits with code `255`.
- **Patched:** PHP prints the error to stderr and exits straight away with code `1`, without running any more PHP code.

```sh
IMAGE=convenia/php-full:7.4   # or convenia/php-full:8.0, or the base image to compare
docker run --rm -i "$IMAGE" php -d display_errors=stderr - <<'EOF'; echo "exit code: $?"
<?php
register_shutdown_function(function () { echo "shutdown function ran\n"; });
ini_set('memory_limit', '8M');
$a = str_repeat('x', 16 * 1024 * 1024);
EOF
```

Expected output:

```
=== php:7.4.33-fpm-alpine3.16 (unpatched)
shutdown function ran
Fatal error: Allowed memory size of 8388608 bytes exhausted ... on line 4
exit code: 255

=== convenia/php-full:7.4 (patched)
Fatal error: Allowed memory size of 8388608 bytes exhausted ... on line 4
exit code: 1
```

The patched image must **not** print `shutdown function ran`, and its exit code must be `1`.

### 2. Binary check

The patch adds a new engine function, `zend_new_array_singleton`, which exists in `php` and `php-fpm` only when
the patch was compiled in:

```sh
docker run --rm "$IMAGE" grep -c zend_new_array_singleton /usr/local/sbin/php-fpm /usr/local/bin/php
```

| Image     | Result                           |
|-----------|----------------------------------|
| Unpatched | `php-fpm:0` and `php:0`          |
| Patched   | `php-fpm:1` and `php:1`          |

The Dockerfiles fail the build if this check fails, and so does the Test step of each workflow.

### 3. Build log

In the workflow's Build step (or a local `docker build`), the rebuild prints one `patching file ...` line for each
file the patch touches, 17 in total, from `Zend/zend_API.h` to `main/rfc1867.c`. A `Hunk ... FAILED` line or a
missing file means the patch did not apply.

### Not evidence

The `php.security-backport` label on the image is plain text set in the Dockerfile. It shows what the image is meant
to contain, not what was compiled.
