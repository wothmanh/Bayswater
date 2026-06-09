# Add the fix to the PowerShell profile

$profilePath = $PROFILE
$fixContent = @'
# Fix for the "ؤؤcd" issue
function Fix-CdCommand {
    param(
        [Parameter(ValueFromRemainingArguments=$true)]
        $Path
    )
    
    # Call the original Set-Location command
    Set-Location $Path
}

# Create an alias for the cd command
Set-Alias -Name cd -Value Fix-CdCommand -Option AllScope -Force
'@

# Check if the profile exists
if (!(Test-Path $profilePath)) {
    # Create the directory if it doesn't exist
    $profileDir = Split-Path $profilePath -Parent
    if (!(Test-Path $profileDir)) {
        New-Item -Path $profileDir -ItemType Directory -Force | Out-Null
    }
    
    # Create the profile file
    New-Item -Path $profilePath -ItemType File -Force | Out-Null
}

# Check if the fix is already in the profile
$profileContent = Get-Content $profilePath -Raw -ErrorAction SilentlyContinue
if ($profileContent -notmatch "Fix-CdCommand") {
    # Add the fix to the profile
    Add-Content -Path $profilePath -Value "`n$fixContent" -Force
    Write-Host "The fix has been added to your PowerShell profile at $profilePath" -ForegroundColor Green
    Write-Host "Please restart your PowerShell session for the changes to take effect." -ForegroundColor Yellow
} else {
    Write-Host "The fix is already in your PowerShell profile." -ForegroundColor Green
}
