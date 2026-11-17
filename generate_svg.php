<?php
$items = [
    ["kalung_emas.svg", "📿"],
    ["gelang_mutiara.svg", "🧿"],
    ["cincin_berlian.svg", "💍"],
    ["anting_perak.svg", "💎"],
    ["kalung_hati.svg", "💖"],
    ["gelang_swarovski.svg", "✨"],
    ["cincin_titanium.svg", "💍"],
    ["anting_emas_putih.svg", "💎"],
    ["kalung_choker.svg", "🎀"],
    ["gelang_kaki.svg", "🦶"],
    ["cincin_safir.svg", "💍"],
    ["anting_mutiara.svg", "🐚"],
    ["kalung_nama.svg", "🔠"],
    ["gelang_rantai.svg", "⛓️"],
    ["jepit_rambut.svg", "🪮"],
    ["gantungan_kunci.svg", "🗝️"],
    ["nametag.svg", "📛"],
    ["kacamata_gaya.svg", "🕶️"],
    ["bando.svg", "🌸"],
    ["pot_hias.svg", "🪴"],
    ["skincare.svg", "🧴"],
    ["dompet.svg", "👛"],
    ["paperbag.svg", "🛍️"],
    ["alat_tulis.svg", "✏️"],
    ["kerudung.svg", "🧕"],
    ["stiker.svg", "🏷️"],
    ["masker.svg", "🎭"],
    ["kawat_bulu.svg", "🧶"],
    ["tas_makeup.svg", "👝"],
    ["mainan.svg", "🎲"],
    ["hiasan_ultah.svg", "🎈"],
    ["tumbler.svg", "🥤"]
];

foreach ($items as $item) {
    $filename = $item[0];
    $emoji = $item[1];
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400">
  <rect width="100%" height="100%" fill="#f8fafc" />
  <text x="50%" y="50%" font-size="200" text-anchor="middle" dominant-baseline="central">' . $emoji . '</text>
</svg>';
    file_put_contents('C:/xampp/htdocs/nnaco.sa/public/img/products/' . $filename, $svg);
}
