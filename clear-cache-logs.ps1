# Check if folder exists and remove subfolder
if (Test-Path "C:\laragon\www\p8-todo-from-scratch\var\cache") {
    Set-Location "C:\laragon\www\p8-todo-from-scratch\var\cache"
    if (Test-Path "dev") { Remove-item -r dev } else { Write-Host "Folder dev is already deleted" }
    if (Test-Path "test") { Remove-item -r test } else { Write-Host "Folder test is already deleted" }
    if (Test-Path "prod") { Remove-item -r prod } else { Write-Host "Folder prod is already deleted" }
} else { Write-Host "Folder C:\laragon\www\p8-todo-from-scratch\var\cache does not exist" }

# Check if folder exists and remove log files
if (Test-Path "C:\laragon\www\p8-todo-from-scratch\var\log") {
    Set-Location "C:\laragon\www\p8-todo-from-scratch\var\log"
    if (Test-Path "dev.log") { Remove-item -r dev.log } else { Write-Host "File dev.log is already deleted" }
    if (Test-Path "test.log") { Remove-item -r test.log } else { Write-Host "File test.log is already deleted" }
} else { Write-Host "Folder C:\laragon\www\p8-todo-from-scratch\var\log does not exist" }

# Change to project directory
# Set-Location "C:\websites\p7-bilemo"

# Run cache clear command on local machine
# php bin/console cache:clear

# Run cache clear command in docker container
# docker exec p7-bilemo-php-1 php bin/console cache:clear

# Run cache clear command in docker container for test environment
# docker exec p7-bilemo-php-1 php bin/console --env=test cache:clear


# Validation message and prompt to exit
Write-Host "Cache cleared successfully in both local and docker environments. Press any key to exit."
$host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")