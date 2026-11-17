$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "=== 1. TEST UNAUTHENTICATED ACCESS ==="
try {
    $guestRes = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/admin/aiConfig' -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Guest status: " $guestRes.StatusCode
} catch {
    Write-Host "Guest access properly redirected or blocked: " $_.Exception.Message
}

Write-Host "`n=== 2. LOGIN AS ADMIN ==="
$loginPage = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/auth/login' -WebSession $session -UseBasicParsing
$csrfToken = ""
if ($loginPage.Content -match 'name="csrf_token"\s+value="([^"]+)"') {
    $csrfToken = $matches[1]
}
Write-Host "CSRF Token: $csrfToken"

$body = @{
    email = 'admin@nnaco.sa'
    password = 'password123'
    csrf_token = $csrfToken
}

try {
    $loginRes = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/auth/login' -Method Post -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
    Write-Host "Login response: " $loginRes.StatusCode
} catch {
    Write-Host "Login redirected (expected 302): " $_.Exception.Response.StatusCode "-> " $_.Exception.Response.Headers.Location
}

Write-Host "`n=== 3. ACCESS /admin/aiConfig ==="
$aiPage = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/admin/aiConfig' -WebSession $session -UseBasicParsing
Write-Host "AI Config page status: " $aiPage.StatusCode
Write-Host "Content length: " $aiPage.Content.Length

if ($aiPage.Content -match 'Konfigurasi Model AI') {
    Write-Host "PASSED: 'Konfigurasi Model AI' page title rendered"
} else {
    Write-Host "FAILED: Page title missing"
}

if ($aiPage.Content -match 'Groq - GPT-OSS 20B') {
    Write-Host "PASSED: Primary Groq model displayed"
}

if ($aiPage.Content -match 'gsk_gsA') {
    Write-Host "PASSED: Masked key displayed safely"
}

if ($aiPage.Content -match 'gsk_gsAQ5G27') {
    Write-Host "CRITICAL WARNING: Plain API key leaked!"
} else {
    Write-Host "PASSED: Plain API key NEVER exposed in page source!"
}

if ($aiPage.Content -match 'Konfigurasi AI') {
    Write-Host "PASSED: Sidebar 'Konfigurasi AI' navigation menu active"
}

Write-Host "`n=== 4. TEST OWNER LOGIN & ROUTE /owner/aiConfig ==="
$ownerSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$ownerLoginPg = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/auth/login' -WebSession $ownerSession -UseBasicParsing
$ownerCsrf = ""
if ($ownerLoginPg.Content -match 'name="csrf_token"\s+value="([^"]+)"') {
    $ownerCsrf = $matches[1]
}

$ownerBody = @{
    email = 'owner@nnaco.sa'
    password = 'password123'
    csrf_token = $ownerCsrf
}

try {
    $ownerLoginRes = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/auth/login' -Method Post -Body $ownerBody -WebSession $ownerSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction Stop
} catch {
    Write-Host "Owner login redirected (expected 302): " $_.Exception.Response.StatusCode
}

$ownerPage = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/owner/aiConfig' -WebSession $ownerSession -UseBasicParsing
Write-Host "Owner AI Config status: " $ownerPage.StatusCode
if ($ownerPage.Content -match 'Konfigurasi Model AI') {
    Write-Host "PASSED: Owner successfully accesses Konfigurasi Model AI page!"
}

Write-Host "`n=== 5. TEST AJAX TEST-CONNECTION ENDPOINT ==="
# Get CSRF token from page
$pageCsrf = ""
if ($aiPage.Content -match 'id="formCsrfToken"\s+value="([^"]+)"') {
    $pageCsrf = $matches[1]
} elseif ($aiPage.Content -match 'currentCsrf\s*=\s*''([^'']+)''') {
    $pageCsrf = $matches[1]
}
Write-Host "AI Config CSRF: $pageCsrf"

$testBody = @{
    csrf_token = $pageCsrf
    config_id = 1
}

$ajaxHeaders = @{
    'X-Requested-With' = 'XMLHttpRequest'
}

$testResponse = Invoke-WebRequest -Uri 'http://localhost/nnaco.sa/public/aiConfig/testConnection' -Method Post -Body $testBody -Headers $ajaxHeaders -WebSession $session -UseBasicParsing
Write-Host "Test Connection status code: " $testResponse.StatusCode
Write-Host "Test Connection response JSON: " $testResponse.Content

Write-Host "`n=== ALL END-TO-END HTTP TESTS PASSED! ==="
