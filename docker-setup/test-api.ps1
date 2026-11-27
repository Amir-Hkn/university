param(
    [string[]]$PestArguments = @()
)

$ErrorActionPreference = "Stop"

Push-Location $PSScriptRoot

try {
    if (-not (Test-Path "../api/.env.testing")) {
        throw "The api/.env.testing file is missing."
    }

    docker compose cp ../api/.env.testing api:/app/.env.testing

    if ($LASTEXITCODE -ne 0) {
        throw "Failed to synchronize the testing environment."
    }

    docker compose exec -T api sh -c `
        'test -f /app/.env.testing && exec env -i PATH="$PATH" HOME="$HOME" APP_ENV=testing php vendor/bin/pest "$@"' `
        sh @PestArguments

    $testExitCode = $LASTEXITCODE
}
finally {
    Pop-Location
}

exit $testExitCode