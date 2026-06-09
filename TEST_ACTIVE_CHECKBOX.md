# Active Checkbox Test Instructions

## Test Steps to Verify the Fix

### 1. Test School Edit Page
1. Navigate to: http://127.0.0.1:8000/admin/schools/2/edit
2. **Test Unchecking Active:**
   - Uncheck the "Active" checkbox
   - Click "Update School"
   - Verify you're redirected to the schools list
   - Navigate back to the edit page: http://127.0.0.1:8000/admin/schools/2/edit
   - **Expected Result:** The "Active" checkbox should remain unchecked

3. **Test Checking Active:**
   - Check the "Active" checkbox
   - Click "Update School"
   - Navigate back to the edit page
   - **Expected Result:** The "Active" checkbox should remain checked

### 2. Test Schools List Page
1. Navigate to: http://127.0.0.1:8000/admin/schools
2. **Expected Result:** The school's status should reflect the checkbox state from the edit page

### 3. Test Calculator Integration
1. Navigate to: http://127.0.0.1:8000/calculator
2. **When school is Active:**
   - **Expected Result:** School should appear in the school dropdown
3. **When school is Inactive:**
   - **Expected Result:** School should NOT appear in the school dropdown

### 4. Test Multiple Schools
1. Repeat the above tests with different schools (e.g., school ID 1, 3, etc.)
2. **Expected Result:** All schools should behave consistently

## Technical Fix Applied

### Problem Identified
The `SchoolController` was using `$request->has('active')` which always returned `true` because of the hidden input field in the form.

### Solution Implemented
Changed the logic in both `store()` and `update()` methods:
```php
// Before (incorrect)
$validated['active'] = $request->has('active');

// After (correct)
$validated['active'] = $request->input('active') == '1';
```

### How It Works
- When checkbox is checked: form sends `active=1`, result is `true`
- When checkbox is unchecked: hidden field sends `active=0`, result is `false`
- Database correctly stores the boolean value
- Calculator properly filters schools using `School::where('active', true)`

## Files Modified
- `app/Http/Controllers/Admin/SchoolController.php` (lines 67 and 151)

## Validation Checklist
- [ ] Active checkbox persists when checked
- [ ] Active checkbox persists when unchecked
- [ ] Schools list reflects correct status
- [ ] Calculator shows only active schools
- [ ] Calculator hides inactive schools
- [ ] Multiple schools work consistently
- [ ] No other school data is affected