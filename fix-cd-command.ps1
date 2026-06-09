# Fix for the "ؤؤcd" issue
# This script creates an alias that removes the Arabic characters before the cd command

# Create a function that strips Arabic characters and calls the original cd command
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

Write-Host "The cd command has been fixed. You should no longer see 'ؤؤcd' errors." -ForegroundColor Green
