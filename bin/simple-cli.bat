@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/simple-cli/simple-cli/bin/simple-cli
php "%BIN_TARGET%" %*
