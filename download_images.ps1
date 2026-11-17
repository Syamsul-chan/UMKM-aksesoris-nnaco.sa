$products = @(
    @("kalung_emas.jpg", "Kalung Emas Murni", "fdf2f8", "831843"),
    @("gelang_mutiara.jpg", "Gelang Mutiara", "eff6ff", "1e3a8a"),
    @("cincin_berlian.jpg", "Cincin Berlian", "f0fdf4", "14532d"),
    @("anting_perak.jpg", "Anting Perak", "fffbeb", "78350f"),
    @("kalung_hati.jpg", "Kalung Hati", "fdf2f8", "831843"),
    @("gelang_swarovski.jpg", "Swarovski", "eff6ff", "1e3a8a"),
    @("cincin_titanium.jpg", "Cincin Titanium", "f0fdf4", "14532d"),
    @("anting_emas_putih.jpg", "Anting Emas", "fffbeb", "78350f"),
    @("kalung_choker.jpg", "Kalung Choker", "fdf2f8", "831843"),
    @("gelang_kaki.jpg", "Gelang Kaki", "eff6ff", "1e3a8a"),
    @("cincin_safir.jpg", "Cincin Safir", "f0fdf4", "14532d"),
    @("anting_mutiara.jpg", "Anting Mutiara", "fffbeb", "78350f"),
    @("kalung_nama.jpg", "Kalung Nama", "fdf2f8", "831843"),
    @("gelang_rantai.jpg", "Gelang Rantai", "eff6ff", "1e3a8a"),
    @("jepit_rambut.jpg", "Jepit Rambut", "fdf2f8", "831843"),
    @("gantungan_kunci.jpg", "Gantungan Kunci", "eff6ff", "1e3a8a"),
    @("nametag.jpg", "Nametag Karyawan", "f0fdf4", "14532d"),
    @("kacamata_gaya.jpg", "Kacamata Gaya", "fffbeb", "78350f"),
    @("bando.jpg", "Bando Kain", "fdf2f8", "831843"),
    @("pot_hias.jpg", "Pot Hias Mini", "eff6ff", "1e3a8a"),
    @("skincare.jpg", "Paket Skincare", "f0fdf4", "14532d"),
    @("dompet.jpg", "Dompet Wanita", "fffbeb", "78350f"),
    @("paperbag.jpg", "Paperbag", "fdf2f8", "831843"),
    @("alat_tulis.jpg", "Set Alat Tulis", "eff6ff", "1e3a8a"),
    @("kerudung.jpg", "Kerudung Pashmina", "f0fdf4", "14532d"),
    @("stiker.jpg", "Stiker Jurnal", "fffbeb", "78350f"),
    @("masker.jpg", "Masker Wajah", "fdf2f8", "831843"),
    @("kawat_bulu.jpg", "DIY Kawat Bulu", "eff6ff", "1e3a8a"),
    @("tas_makeup.jpg", "Tas Makeup", "f0fdf4", "14532d"),
    @("mainan.jpg", "Mainan Rubik", "fffbeb", "78350f"),
    @("hiasan_ultah.jpg", "Hiasan Ultah", "fdf2f8", "831843"),
    @("tumbler.jpg", "Tumbler", "eff6ff", "1e3a8a")
)

foreach ($p in $products) {
    $filename = $p[0]
    $text = $p[1] -replace ' ', '+'
    $bg = $p[2]
    $color = $p[3]
    
    $url = "https://placehold.co/400x400/$bg/$color.png?text=$text"
    $filepath = "c:\xampp\htdocs\nnaco.sa\public\img\products\$filename"
    
    Write-Host "Downloading $filename..."
    Invoke-WebRequest -Uri $url -OutFile $filepath
}
