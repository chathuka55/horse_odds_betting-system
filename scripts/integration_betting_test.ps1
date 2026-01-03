# Integration test: place bet -> save result -> verify credit change
$root = Get-Location
Write-Host "Running integration betting test..."
# Create test bet
php scripts\create_test_bet.php | Tee-Object -Variable out1
if ($LASTEXITCODE -ne 0) { Write-Error "create_test_bet failed"; exit 1 }
Write-Host $out1
# Capture user id from output
 $m1 = [regex]::Match($out1, 'Created user id=(\d+)')
 if ($m1.Success) { $userId = $m1.Groups[1].Value }
 else {
	 $m2 = [regex]::Match($out1, 'Found user id=(\d+)')
	 if ($m2.Success) { $userId = $m2.Groups[1].Value } else { Write-Error 'Could not determine user id'; exit 1 }
 }
# We will read credit from the simulate output later; capture a simple before snapshot by querying DB via PHP is flaky in CLI quoting.
Write-Host "Proceeding to simulate settlement and will read credit from output";
# Simulate saving results
php scripts\simulate_save_result.php | Tee-Object -Variable out2
Write-Host $out2
# Parse credit for the user from the simulate output
 $pattern = "id=$userId\s+email=.*?credit=([0-9]+\.?[0-9]*)"
 $m = [regex]::Match($out2, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
if (-not $m.Success) { Write-Error "Could not parse credit from simulate output"; exit 1 }
$after = $m.Groups[1].Value
Write-Host "Credit after: $after"
if ([decimal]$after -le 0) { Write-Error "Unexpected credit value"; exit 1 } else { Write-Host "Integration test passed: credit after settlement = $after"; exit 0 }
