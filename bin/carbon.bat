@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/carbon-cli/carbon-cli/bin/carbon
php "%BIN_TARGET%" %*
