# Agent Logo Fix & Deployment Guide

## Issue Identified
The agent logo for `mohdedu@intholidays.com` was not displaying because `public/storage` was a physical directory instead of a symbolic link to `storage/app/public`.

## Root Cause
- Files uploaded via the agent settings are stored in `storage/app/public/agents/`
- Laravel's `Storage::url()` generates URLs like `/storage/agents/filename.jpg`
- These URLs require `public/storage` to be a **symbolic link** to `storage/app/public`
- The symlink was broken/missing, causing a 404 error for logo images

## Fix Applied (Local Environment)

### Step 1: Remove Incorrect Directory
```powershell
Remove-Item -Path "public\storage" -Recurse -Force
```

### Step 2: Create Proper Symlink
```bash
php artisan storage:link
```

This command creates a symbolic link from `public/storage` → `storage/app/public`

## Verification
✅ Logo now displays correctly for `mohdedu@intholidays.com`
✅ File accessible at: `http://127.0.0.1:8000/storage/agents/cyNElsx2yW7vzGr26Ht4DIG5AmHIoyImxFW597oz.jpg`
✅ Logo appears in:
   - Dashboard header
   - Agent Settings page
   - Quotation PDFs

## Cloud Deployment Instructions

### For ANY Cloud Hosting (AWS, DigitalOcean, Heroku, etc.)

**CRITICAL: Run this command AFTER deploying to production:**

```bash
php artisan storage:link
```

### Deployment Checklist

1. **Push code to repository** (Git will ignore `public/storage` as per `.gitignore`)

2. **SSH into your server**

3. **Navigate to project directory**
   ```bash
   cd /path/to/your/project
   ```

4. **Pull latest code**
   ```bash
   git pull origin main
   ```

5. **Install dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

6. **Run migrations** (if any)
   ```bash
   php artisan migrate --force
   ```

7. **Create storage symlink** ⚠️ **REQUIRED**
   ```bash
   php artisan storage:link
   ```

8. **Set proper permissions**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

9. **Clear caches**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Verification on Cloud

After deployment, verify the symlink exists:

```bash
ls -la public/storage
```

Expected output should show:
```
lrwxrwxrwx ... public/storage -> /full/path/to/storage/app/public
```

### Common Cloud Platform Notes

#### **AWS EC2 / DigitalOcean Droplet**
- SSH access available
- Run `php artisan storage:link` directly
- Ensure Apache/Nginx user has write permissions to `storage/`

#### **Shared Hosting (cPanel)**
- Use Terminal in cPanel
- Navigate to public_html or project root
- Run `php artisan storage:link`
- If symlinks are restricted, contact hosting support

#### **Heroku**
- Add to `Procfile` or deployment script:
  ```
  web: php artisan storage:link && vendor/bin/heroku-php-apache2 public/
  ```

#### **Laravel Forge**
- Add to deployment script:
  ```bash
  php artisan storage:link
  ```

## Future Agent Logo Uploads

With the symlink properly configured:

1. ✅ New agents can upload logos via Agent Settings
2. ✅ Logos are stored in `storage/app/public/agents/`
3. ✅ Logos are accessible via `/storage/agents/` URL
4. ✅ Logos display correctly in all views
5. ✅ Logos are included in PDF quotations

## Troubleshooting

### Logo still not showing after deployment?

**Check 1: Verify symlink exists**
```bash
ls -la public/storage
```

**Check 2: Recreate symlink**
```bash
rm -rf public/storage
php artisan storage:link
```

**Check 3: Check file permissions**
```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

**Check 4: Verify file exists**
```bash
ls -la storage/app/public/agents/
```

**Check 5: Check web server configuration**
- Ensure `FollowSymLinks` is enabled in Apache
- Ensure Nginx is configured to serve static files from `public/`

## Technical Details

### File Upload Flow
1. User uploads logo via `/agent/settings`
2. `AgentSettingsController::update()` stores file using:
   ```php
   $path = $request->file('brand_logo')->store('agents', 'public');
   ```
3. This saves to: `storage/app/public/agents/{hashed-filename}.jpg`
4. Path stored in DB: `agents/{hashed-filename}.jpg`

### File Display Flow
1. Blade template uses:
   ```blade
   <img src="{{ Storage::url($agent->brand_logo_path) }}" />
   ```
2. `Storage::url()` generates: `/storage/agents/{hashed-filename}.jpg`
3. Web server resolves via symlink:
   - `/storage/` → `public/storage/` (URL path)
   - `public/storage/` → `storage/app/public/` (symlink)
   - Final file: `storage/app/public/agents/{hashed-filename}.jpg`

## Summary

✅ **Local Fix**: Symlink recreated successfully
✅ **Cloud Deployment**: Must run `php artisan storage:link` after each deployment
✅ **Future Uploads**: Will work automatically with proper symlink
✅ **All Environments**: Solution works identically on local and cloud

---

**Last Updated**: 2026-01-17
**Tested On**: Windows (XAMPP), Ready for Linux/Cloud deployment
