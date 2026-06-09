<!DOCTYPE html>
<html>
<head>
    <title>Test Active Checkbox</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin: 5px 0; }
        input[type="checkbox"] { margin-right: 10px; }
        button { padding: 10px 20px; margin: 10px 0; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Test Active Checkbox Form</h1>
    
    <?php if ($_POST): ?>
        <div class="debug">
            <h3>POST Data Received:</h3>
            <pre><?php print_r($_POST); ?></pre>
            
            <h3>Active Value Analysis:</h3>
            <p>Raw active value: <?php echo isset($_POST['active']) ? var_export($_POST['active'], true) : 'NOT SET'; ?></p>
            <p>Active == '1': <?php echo isset($_POST['active']) && $_POST['active'] == '1' ? 'TRUE' : 'FALSE'; ?></p>
            <p>Active as boolean: <?php echo isset($_POST['active']) && $_POST['active'] == '1' ? 'true' : 'false'; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <h3>Test 1: With Hidden Input (Laravel Style)</h3>
            <input type="hidden" name="active" value="0">
            <label>
                <input type="checkbox" name="active" value="1" <?php echo (isset($_POST['active']) && $_POST['active'] == '1') ? 'checked' : ''; ?>>
                Active (with hidden input)
            </label>
        </div>
        
        <div class="form-group">
            <h3>Test 2: Without Hidden Input</h3>
            <label>
                <input type="checkbox" name="active_no_hidden" value="1" <?php echo (isset($_POST['active_no_hidden']) && $_POST['active_no_hidden'] == '1') ? 'checked' : ''; ?>>
                Active (without hidden input)
            </label>
        </div>
        
        <div class="form-group">
            <input type="text" name="test_field" placeholder="Test field" value="<?php echo isset($_POST['test_field']) ? htmlspecialchars($_POST['test_field']) : ''; ?>">
        </div>
        
        <button type="submit">Submit Test</button>
    </form>
    
    <div class="debug">
        <h3>Instructions:</h3>
        <ol>
            <li>Try checking/unchecking the checkboxes and submitting</li>
            <li>Notice how the hidden input affects the POST data</li>
            <li>Compare with Laravel's behavior</li>
        </ol>
    </div>
</body>
</html>