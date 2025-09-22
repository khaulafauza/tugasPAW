<?php
// File untuk nyimpen data
$jadwalFile = "data/jadwal.json";
$tugasFile  = "data/tugas.json";

// Baca data dari file JSON
$jadwal = file_exists($jadwalFile) ? json_decode(file_get_contents($jadwalFile), true) : [];
$tugas  = file_exists($tugasFile) ? json_decode(file_get_contents($tugasFile), true) : [];

// ---------- Tambah Jadwal ----------
if(isset($_POST['tambahJadwal'])){
    $baru = [
        "matkul" => $_POST['matkul'],
        "hari"   => $_POST['hari'],
        "jam"    => $_POST['jam'],
        "ruang"  => $_POST['ruang'],
        "dosen"  => $_POST['dosen'],
        "sks"    => (int)$_POST['sks']
    ];
    $jadwal[] = $baru;
    file_put_contents($jadwalFile, json_encode($jadwal, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// ---------- Tambah Tugas ----------
if(isset($_POST['tambahTugas'])){
    $baru = [
        "judul"    => $_POST['judul'],
        "deadline" => $_POST['deadline'],
        "status"   => "belum"
    ];
    $tugas[] = $baru;
    file_put_contents($tugasFile, json_encode($tugas, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// ---------- Ubah Status Tugas ----------
if(isset($_GET['selesai'])){
    $id = $_GET['selesai'];
    if(isset($tugas[$id])){
        $tugas[$id]['status'] = "selesai";
        file_put_contents($tugasFile, json_encode($tugas, JSON_PRETTY_PRINT));
    }
    header("Location: index.php");
    exit;
}

// ---------- Hitung SKS ----------
function totalSKS($jadwal){
    $sks = 0;
    foreach($jadwal as $j){
        $sks += $j['sks'];
    }
    return $sks;
}

// ---------- Cek deadline dekat ----------
function cekDeadlineDekat($tugas){
    $alert = [];
    $hariIni = strtotime(date("Y-m-d"));
    foreach($tugas as $t){
        $sisa = (strtotime($t['deadline']) - $hariIni) / 86400;
        if($t['status']=="belum" && $sisa <= 2 && $sisa >= 0){
            $alert[] = "Tugas ".$t['judul']." deadline sebentar lagi!";
        }
    }
    return $alert;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ToDoList Mahasiswa</title>
    <style>
        body { font-family: Arial; margin:20px; background:#f5f5f5; }
        h2 { margin-top:30px; }
        form { margin-bottom:15px; }
        input, select { margin:5px; padding:5px; }
        .box { background:white; padding:15px; margin-bottom:20px; border-radius:8px; }
        .alert { color:red; font-weight:bold; }
    </style>
</head>
<body>
    <h1>📌 ToDoList Mahasiswa</h1>

    <!-- ALERT -->
    <div class="box">
        <h2>🔔 Peringatan Deadline</h2>
        <?php
        $alerts = cekDeadlineDekat($tugas);
        if(count($alerts)==0){
            echo "<i>Tidak ada deadline dekat.</i>";
        } else {
            foreach($alerts as $a) echo "<div class='alert'>$a</div>";
        }
        ?>
    </div>

    <!-- DASHBOARD -->
    <div class="box">
        <h2>📅 Jadwal Kuliah Hari Ini (<?php echo date("l"); ?>)</h2>
        <ul>
        <?php
        $hari = date("l"); // English (Monday, Tuesday,...)
        $ada = false;
        foreach($jadwal as $j){
            if(strtolower($j['hari']) == strtolower($hari)){
                echo "<li>{$j['matkul']} ({$j['jam']}) - {$j['ruang']} - {$j['dosen']}</li>";
                $ada = true;
            }
        }
        if(!$ada) echo "<i>Tidak ada jadwal.</i>";
        ?>
        </ul>
        <p><b>Total SKS:</b> <?php echo totalSKS($jadwal); ?></p>
    </div>

    <!-- FORM JADWAL -->
    <div class="box">
        <h2>Tambah Jadwal</h2>
        <form method="post">
            <input type="text" name="matkul" placeholder="Mata Kuliah" required>
            <select name="hari">
                <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                <option>Thursday</option><option>Friday</option><option>Saturday</option>
            </select>
            <input type="text" name="jam" placeholder="Jam">
            <input type="text" name="ruang" placeholder="Ruangan">
            <input type="text" name="dosen" placeholder="Dosen">
            <input type="number" name="sks" placeholder="SKS">
            <button type="submit" name="tambahJadwal">Simpan</button>
        </form>
    </div>

    <!-- FORM TUGAS -->
    <div class="box">
        <h2>Tambah Tugas</h2>
        <form method="post">
            <input type="text" name="judul" placeholder="Judul Tugas" required>
            <input type="date" name="deadline" required>
            <button type="submit" name="tambahTugas">Simpan</button>
        </form>
    </div>

    <!-- DAFTAR TUGAS -->
    <div class="box">
        <h2>📖 Daftar Tugas</h2>
        <ul>
        <?php
        if(count($tugas)==0){
            echo "<i>Belum ada tugas.</i>";
        } else {
            foreach($tugas as $i=>$t){
                echo "<li>{$t['judul']} (Deadline: {$t['deadline']}) - {$t['status']}";
                if($t['status']=="belum"){
                    echo " <a href='?selesai=$i'>[Tandai Selesai]</a>";
                }
                echo "</li>";
            }
        }
        ?>
        </ul>
    </div>
</body>
</html>
