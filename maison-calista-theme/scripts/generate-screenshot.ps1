Add-Type -AssemblyName System.Drawing

$outPath = Join-Path (Split-Path $PSScriptRoot -Parent) 'screenshot.png'

$w = 1200
$h = 900
$bmp = New-Object System.Drawing.Bitmap $w, $h
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.Clear([System.Drawing.Color]::FromArgb(247, 242, 234))

$g.FillRectangle(
    (New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(230, 255, 255, 255))),
    0, 0, $w, 72
)
$g.FillRectangle(
    (New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(232, 221, 208))),
    0, 72, $w, 380
)
$g.FillRectangle(
    (New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(166, 124, 82))),
    980, 24, 140, 28
)

$sf = New-Object System.Drawing.StringFormat
$sf.Alignment = [System.Drawing.StringAlignment]::Center

$brushBronze = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(107, 79, 58))
$brushMuted = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(138, 132, 122))

$g.DrawString('Maison Calista', (New-Object System.Drawing.Font 'Georgia', 22), $brushBronze, 88, 22)
$g.DrawString(
    'Exclusive Residence',
    (New-Object System.Drawing.Font 'Georgia', 42),
    $brushBronze,
    (New-Object System.Drawing.RectangleF 0, 250, $w, 60),
    $sf
)
$g.DrawString(
    'Maison Calista WordPress Theme',
    (New-Object System.Drawing.Font 'Arial', 14),
    $brushMuted,
    (New-Object System.Drawing.RectangleF 0, 835, $w, 30),
    $sf
)

$bmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)
$g.Dispose()
$bmp.Dispose()

Write-Host "Created $outPath ($((Get-Item $outPath).Length) bytes)"
