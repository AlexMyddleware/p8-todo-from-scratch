# Move to the project directory
Set-Location -Path "C:\laragon\www\p8-todo-from-scratch"

# Load fixtures without interaction
& php bin/console doctrine:fixtures:load --no-interaction

# Load fixtures without interaction in test environment
& php bin/console doctrine:fixtures:load --no-interaction --env=test

# Validation message and prompt to exit
Write-Host "Fixtures loaded successfully in both local and test environments. Press any key to exit."
$host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
    